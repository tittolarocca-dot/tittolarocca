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

        return Inertia::render('Member/Messages', [
            'conversations' => array_values($conversations),
            'subscriptions' => $subscriptions,
            'ppvSuccess'    => $request->query('ppv_success') === '1',
        ]);
    }

    public function send(Request $request, Profile $profile)
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $user = $request->user();

        if (!$user->isSubscribedTo($profile)) {
            return back()->with('error', 'Nur Abonnenten können Nachrichten senden.');
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

        if (!$user->isSubscribedTo($profile)) {
            return response()->json(['error' => 'Nur Abonnenten können Bilder senden.'], 403);
        }

        $file    = $request->file('media');
        $ext     = $file->getClientOriginalExtension();

        $message = Message::create([
            'from_user_id'   => $user->id,
            'to_user_id'     => $profile->user_id,
            'profile_id'     => $profile->id,
            'ppv_media_type' => 'image',
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
            $isPpv      = $m->isPpv();
            $hasMedia   = $m->ppv_media_type !== null;
            $purchased  = $isPpv && $paidIds->has($m->id);
            $fromMe     = $m->from_user_id === $user->id;

            // Regular (free) media is visible to both parties; PPV only if purchased
            $mediaUrl = match(true) {
                !$hasMedia        => null,
                !$isPpv           => route('media.ppv', $m->id), // regular chat image
                $purchased        => route('media.ppv', $m->id), // paid PPV
                default           => null,
            };

            return [
                'id'             => $m->id,
                'body'           => $m->body,
                'from_me'        => $fromMe,
                'sender'         => $m->from->name,
                'created_at'     => $m->created_at->format('d.m.Y H:i'),
                'ppv_media_type' => $m->ppv_media_type,
                'ppv_price_chf'  => $m->ppv_price_chf,
                'ppv_purchased'  => $purchased,
                'ppv_media_url'  => $mediaUrl,
                'is_ppv'         => $isPpv,
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

        // Launch-Modus: keine echten Zahlungen (Pay-per-View deaktiviert).
        if (config('features.launch_mode')) {
            return back()->with('error', 'Im Launch-Modus sind kostenpflichtige Inhalte deaktiviert.');
        }

        if (!$message->isPpv()) {
            return back()->with('error', 'Diese Nachricht enthält keinen bezahlten Inhalt.');
        }

        // Must be subscribed to the creator
        $profile = $message->profile;
        if (!$profile || !$user->isSubscribedTo($profile)) {
            return back()->with('error', 'Nur Abonnenten können Inhalte freischalten.');
        }

        // Already purchased?
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
                'stripe_session_id'=> $session->id,
            ]
        );

        return redirect($session->url);
    }
}
