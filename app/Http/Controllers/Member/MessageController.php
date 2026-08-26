<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Profile;
use App\Models\PpvPurchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\StripeClient;

class MessageController extends Controller
{
    /** Max. neue Konversationen (unterschiedliche Empfänger), die ein Mitglied pro 24h starten darf. */
    private const MAX_NEW_CHATS_PER_DAY = 10;

    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->with(['from:id,name', 'to:id,name', 'profile:id,display_name,slug'])
            ->orderByDesc('created_at')
            ->get();

        $conversations = [];
        foreach ($messages as $msg) {
            $otherId   = $msg->from_user_id === $user->id ? $msg->to_user_id : $msg->from_user_id;
            $otherName = $msg->from_user_id === $user->id ? $msg->to->name : $msg->from->name;
            if (!isset($conversations[$otherId])) {
                $conversations[$otherId] = [
                    'user_id'      => $otherId,
                    'name'         => $otherName,
                    'profile'      => $msg->profile ? [
                        'display_name' => $msg->profile->display_name,
                        'slug'         => $msg->profile->slug,
                    ] : null,
                    'last_message' => $msg->previewText(),
                    'last_at'      => $msg->created_at->format('d.m.Y H:i'),
                    'unread'       => 0,
                ];
            }
            if ($msg->to_user_id === $user->id && !$msg->read_at) {
                $conversations[$otherId]['unread']++;
            }
        }

        // Active subscriptions – used to populate the sidebar even before any message exists
        $subscriptions = $user->platformSubscriptions()
            ->with(['profile:id,display_name,slug,user_id'])
            ->whereIn('status', ['active', 'trialing'])
            ->where('current_period_end', '>', now())
            ->get()
            ->map(fn($s) => [
                'creator_user_id' => $s->profile->user_id,
                'display_name'    => $s->profile->display_name,
                'slug'            => $s->profile->slug,
            ]);

        // Direkter Chat-Start von der Profilseite (?to=slug) – z. B. im Launch-Modus,
        // wo es keine Abos gibt, über die man sonst eine Konversation beginnt.
        $startWith = null;
        if ($slug = $request->query('to')) {
            $target = Profile::where('slug', $slug)->first();
            if ($target && $this->canChatWith($user, $target) && ! isset($conversations[$target->user_id])) {
                $startWith = [
                    'user_id' => $target->user_id,
                    'name'    => $target->display_name,
                    'profile' => ['display_name' => $target->display_name, 'slug' => $target->slug],
                ];
            }
        }

        return Inertia::render('Member/Messages', [
            'conversations' => array_values($conversations),
            'subscriptions' => $subscriptions,
            'startWith'     => $startWith,
            'ppvSuccess'    => $request->query('ppv_success') === '1',
        ]);
    }

    /**
     * Darf $user dieser Inserentin schreiben?
     * - Launch-Modus: jedes registrierte Mitglied darf.
     * - Normalbetrieb: nur Abonnenten.
     * - In beiden Fällen NICHT, wenn die Inserentin den Nutzer blockiert hat.
     */
    private function canChatWith(\App\Models\User $user, Profile $profile): bool
    {
        $access = config('features.launch_mode') || $user->isSubscribedTo($profile);
        if (! $access) {
            return false;
        }

        $owner = \App\Models\User::find($profile->user_id);
        return ! ($owner && $owner->hasBlocked($user->id));
    }

    /**
     * Spam-Schutz beim Senden (Option A):
     * - Erst-Kontakt: nur EINE Nachricht, bis die Inserierende geantwortet hat.
     * - Neue Konversation: max. MAX_NEW_CHATS_PER_DAY pro 24h.
     * Gibt eine Fehlermeldung zurück oder null, wenn erlaubt.
     */
    private function sendBlockReason(\App\Models\User $user, Profile $profile): ?string
    {
        $ownerId = $profile->user_id;

        $memberSent = Message::where('from_user_id', $user->id)
            ->where('to_user_id', $ownerId)->exists();

        if ($memberSent) {
            // Bestehende Konversation: erst offen, wenn die Inserierende schon einmal geantwortet hat.
            $ownerReplied = Message::where('from_user_id', $ownerId)
                ->where('to_user_id', $user->id)->exists();

            return $ownerReplied
                ? null
                : 'Bitte warte, bis ' . $profile->display_name . ' auf deine Anfrage geantwortet hat.';
        }

        // Neue Konversation → Tageslimit für neue Chats prüfen.
        $recipientsToday = Message::where('from_user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->distinct()
            ->count('to_user_id');

        if ($recipientsToday >= self::MAX_NEW_CHATS_PER_DAY) {
            return 'Du hast heute bereits viele neue Chats gestartet. Bitte versuche es später wieder.';
        }

        return null;
    }

    public function send(Request $request, Profile $profile)
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $user = $request->user();

        if (! $this->canChatWith($user, $profile)) {
            return response()->json(['error' => 'Du kannst dieser Person aktuell nicht schreiben.'], 403);
        }

        if ($reason = $this->sendBlockReason($user, $profile)) {
            return response()->json(['error' => $reason], 429);
        }

        Message::create([
            'from_user_id' => $user->id,
            'to_user_id'   => $profile->user_id,
            'profile_id'   => $profile->id,
            'body'         => $request->input('body'),
        ]);

        return response()->json(['ok' => true]);
    }

    public function sendMedia(Request $request, Profile $profile)
    {
        $request->validate([
            'media' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:20480'],
        ]);

        $user = $request->user();

        if (! $this->canChatWith($user, $profile)) {
            return response()->json(['error' => 'Du kannst dieser Person aktuell nicht schreiben.'], 403);
        }

        if ($reason = $this->sendBlockReason($user, $profile)) {
            return response()->json(['error' => $reason], 429);
        }

        $file    = $request->file('media');
        $ext     = $file->getClientOriginalExtension();

        $message = Message::create([
            'from_user_id'   => $user->id,
            'to_user_id'     => $profile->user_id,
            'profile_id'     => $profile->id,
            'ppv_media_type' => 'image',
            'ppv_media_mode' => 'free',
        ]);

        $path = $file->storeAs("chat/{$message->id}", "img.{$ext}", 'local');
        $message->update(['ppv_media_path' => $path]);

        return response()->json(['ok' => true]);
    }

    public function conversation(Request $request, int $userId)
    {
        $user = $request->user();

        $messages = Message::where(function ($q) use ($user, $userId) {
            $q->where('from_user_id', $user->id)->where('to_user_id', $userId);
        })->orWhere(function ($q) use ($user, $userId) {
            $q->where('from_user_id', $userId)->where('to_user_id', $user->id);
        })
        ->with(['from:id,name'])
        ->orderBy('created_at')
        ->get();

        // Batch-load paid PPV purchases for this user
        $messageIds = $messages->pluck('id');
        $paidIds = PpvPurchase::whereIn('message_id', $messageIds)
            ->where('buyer_user_id', $user->id)
            ->where('status', 'paid')
            ->pluck('message_id')
            ->flip();

        $mapped = $messages->map(function ($m) use ($user, $paidIds) {
            $gated      = $m->requiresUnlock();
            $hasMedia   = $m->hasMedia();
            $purchased  = $gated && $paidIds->has($m->id);
            $fromMe     = $m->from_user_id === $user->id;

            // Freie Medien sehen beide Seiten; gesperrte nur nach Freischaltung.
            $mediaUrl = match(true) {
                !$hasMedia => null,
                !$gated    => route('media.ppv', $m->id), // freies Chat-Medium
                $purchased => route('media.ppv', $m->id), // freigeschaltet
                default    => null,
            };

            return [
                'id'             => $m->id,
                'body'           => $m->body,
                'from_me'        => $fromMe,
                'sender'         => $m->from->name,
                'created_at'     => $m->created_at->format('d.m.Y H:i'),
                'time'           => $m->created_at->format('H:i'),
                'date'           => $m->created_at->format('d.m.Y'),
                'read'           => $fromMe ? (bool) $m->read_at : null,
                'ppv_media_type' => $m->ppv_media_type,
                'ppv_media_mode' => $m->ppv_media_mode,
                'ppv_price_chf'  => $m->ppv_price_chf,
                'ppv_purchased'  => $purchased,
                'ppv_media_url'  => $mediaUrl,
                'requires_unlock'=> $gated,
            ];
        });

        Message::where('from_user_id', $userId)
            ->where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $mapped]);
    }

    public function ppvCheckout(Request $request, Message $message)
    {
        $user = $request->user();

        // Nur „Online-Zahlung"-Inhalte sind kaufbar; manuelle werden von der
        // Inserentin freigegeben, freie brauchen keinen Kauf.
        if (!$message->isPaidOnline()) {
            return back()->with('error', 'Dieser Inhalt kann nicht online gekauft werden.');
        }

        // Online-Zahlung braucht einen konfigurierten Zahlungsanbieter (Stripe).
        // Solange keiner hinterlegt ist (z. B. Launch-Phase), sauber abweisen
        // statt mit einer Ausnahme abzustürzen.
        if (!config('services.stripe.secret')) {
            return back()->with('error', 'Online-Zahlung ist noch nicht aktiv. Bitte frag die Anbieterin nach einer manuellen Freigabe.');
        }

        $profile = $message->profile;

        // Bereits gekauft?
        $existing = PpvPurchase::where('message_id', $message->id)
            ->where('buyer_user_id', $user->id)
            ->where('status', 'paid')
            ->first();

        if ($existing) {
            return back()->with('success', 'Du hast diesen Inhalt bereits freigeschaltet.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Ensure Stripe customer
        if (!$user->stripe_id) {
            $customer = $stripe->customers->create([
                'email'    => $user->email,
                'name'     => $user->name,
                'metadata' => ['user_id' => $user->id],
            ]);
            $user->update(['stripe_id' => $customer->id]);
        }

        $session = $stripe->checkout->sessions->create([
            'customer'   => $user->stripe_id,
            'mode'       => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'chf',
                    'product_data' => [
                        'name' => 'Privater Inhalt: ' . ($profile->display_name ?? 'Creator'),
                    ],
                    'unit_amount' => (int) ($message->ppv_price_chf * 100),
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('konto.messages') . '?ppv_success=1',
            'cancel_url'  => route('konto.messages'),
            'metadata'    => [
                'type'       => 'ppv',
                'message_id' => $message->id,
                'user_id'    => $user->id,
            ],
        ]);

        // Create pending purchase record
        PpvPurchase::updateOrCreate(
            ['message_id' => $message->id, 'buyer_user_id' => $user->id],
            [
                'amount_chf'       => $message->ppv_price_chf,
                'status'           => 'pending',
                'method'           => 'stripe',
                'stripe_session_id'=> $session->id,
            ]
        );

        return redirect($session->url);
    }
}
