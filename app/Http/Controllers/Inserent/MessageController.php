<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\PpvPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->route('inserat.profile.edit')
                ->with('error', 'Kein Profil gefunden.');
        }

        $messages = Message::where('to_user_id', $user->id)
            ->orWhere('from_user_id', $user->id)
            ->with(['from:id,name', 'to:id,name'])
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
                    'last_message' => $msg->previewText(),
                    'last_at'      => $msg->created_at->format('d.m.Y H:i'),
                    'unread'       => 0,
                    'blocked'      => $user->hasBlocked($otherId),
                ];
            }
            if ($msg->to_user_id === $user->id && !$msg->read_at) {
                $conversations[$otherId]['unread']++;
            }
        }

        return Inertia::render('Inserent/Messages', [
            'conversations' => array_values($conversations),
            'unreadCount'   => array_sum(array_column(array_values($conversations), 'unread')),
        ]);
    }

    public function reply(Request $request, int $toUserId)
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $user = $request->user();

        Message::create([
            'from_user_id' => $user->id,
            'to_user_id'   => $toUserId,
            'profile_id'   => $user->profile?->id,
            'body'         => $request->input('body'),
        ]);

        return back()->with('success', 'Antwort gesendet.');
    }

    /** Inserentin blockiert einen Kunden – er kann ihr nicht mehr schreiben. */
    public function block(Request $request, int $userId)
    {
        \App\Models\ChatBlock::firstOrCreate([
            'user_id'         => $request->user()->id,
            'blocked_user_id' => $userId,
        ]);

        return back()->with('success', 'Nutzer blockiert.');
    }

    public function unblock(Request $request, int $userId)
    {
        \App\Models\ChatBlock::where('user_id', $request->user()->id)
            ->where('blocked_user_id', $userId)
            ->delete();

        return back()->with('success', 'Blockierung aufgehoben.');
    }

    /** Leichtgewichtiger Endpoint fürs Polling der ungelesenen Nachrichten (Dashboard-Badge). */
    public function unreadCount(Request $request)
    {
        $count = Message::where('to_user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Inserentin sendet Foto/Video im Chat mit einem Freigabe-Modus:
     *   free   – sofort sichtbar
     *   paid   – gesperrt, Freischaltung über Online-Zahlung (Preis nötig)
     *   manual – gesperrt, Freigabe später manuell (z. B. nach TWINT)
     */
    public function sendPpv(Request $request, int $toUserId)
    {
        $request->validate([
            'media' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:102400'],
            'mode'  => ['required', 'in:free,paid,manual'],
            'price' => ['required_if:mode,paid', 'nullable', 'numeric', 'min:1', 'max:999'],
            'body'  => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $mode = $request->input('mode');

        $file      = $request->file('media');
        $ext       = $file->getClientOriginalExtension();
        $mediaType = in_array(strtolower($ext), ['mp4', 'mov', 'webm']) ? 'video' : 'image';

        // Nachricht zuerst anlegen (für die ID), dann Datei ablegen.
        $message = Message::create([
            'from_user_id'   => $user->id,
            'to_user_id'     => $toUserId,
            'profile_id'     => $user->profile?->id,
            'body'           => $request->input('body') ?: null,
            'ppv_media_type' => $mediaType,
            'ppv_media_mode' => $mode,
            'ppv_price_chf'  => $mode === 'paid' ? $request->input('price') : null,
            'ppv_media_path' => 'placeholder', // unten aktualisiert
        ]);

        $path = $file->storeAs("ppv/{$message->id}", "media.{$ext}", 'local');
        $message->update(['ppv_media_path' => $path]);

        return back()->with('success', 'Inhalt gesendet.');
    }

    /**
     * Manuelle Freigabe eines gesperrten Mediums (Modus „manual") für den
     * Empfänger – z. B. nachdem der Kunde per TWINT bezahlt hat.
     */
    public function releaseMedia(Request $request, int $messageId)
    {
        $user = $request->user();

        $message = Message::where('id', $messageId)
            ->where('from_user_id', $user->id)   // nur eigene Nachrichten
            ->where('ppv_media_mode', 'manual')
            ->firstOrFail();

        PpvPurchase::updateOrCreate(
            ['message_id' => $message->id, 'buyer_user_id' => $message->to_user_id],
            [
                'amount_chf' => $message->ppv_price_chf ?? 0,
                'status'     => 'paid',
                'method'     => 'manual',
                'paid_at'    => now(),
            ],
        );

        return back()->with('success', 'Inhalt freigegeben.');
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

        // Für gesperrte Inhalte: bezahlte/freigegebene Käufe zählen.
        $gatedIds = $messages->filter(fn($m) => $m->requiresUnlock())->pluck('id');
        $purchaseCounts = PpvPurchase::whereIn('message_id', $gatedIds)
            ->where('status', 'paid')
            ->selectRaw('message_id, count(*) as cnt')
            ->groupBy('message_id')
            ->pluck('cnt', 'message_id');

        $mapped = $messages->map(function ($m) use ($user, $purchaseCounts) {
            $count = $purchaseCounts[$m->id] ?? 0;
            return [
                'id'                 => $m->id,
                'body'               => $m->body,
                'from_me'            => $m->from_user_id === $user->id,
                'sender'             => $m->from->name,
                'created_at'         => $m->created_at->format('d.m.Y H:i'),
                'time'               => $m->created_at->format('H:i'),
                'date'               => $m->created_at->format('d.m.Y'),
                'read'               => $m->from_user_id === $user->id ? (bool) $m->read_at : null,
                'ppv_media_type'     => $m->ppv_media_type,
                'ppv_media_mode'     => $m->ppv_media_mode,
                'ppv_price_chf'      => $m->ppv_price_chf,
                // Die Inserentin (Erstellerin) darf ihre eigenen Medien immer sehen.
                'ppv_media_url'      => $m->hasMedia() ? route('media.ppv', $m->id) : null,
                'ppv_purchase_count' => $m->isPaidOnline() ? $count : null,
                'ppv_released'       => $m->isManual() ? ($count > 0) : null,
            ];
        });

        Message::where('from_user_id', $userId)
            ->where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $mapped]);
    }
}
