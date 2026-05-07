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

    public function sendPpv(Request $request, int $toUserId)
    {
        $request->validate([
            'media'     => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:102400'],
            'price'     => ['required', 'numeric', 'min:1', 'max:999'],
            'body'      => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $file      = $request->file('media');
        $ext       = $file->getClientOriginalExtension();
        $mediaType = in_array(strtolower($ext), ['mp4', 'mov', 'webm']) ? 'video' : 'image';

        // Create message first to get ID, then store file
        $message = Message::create([
            'from_user_id'  => $user->id,
            'to_user_id'    => $toUserId,
            'profile_id'    => $user->profile?->id,
            'body'          => $request->input('body') ?: null,
            'ppv_media_type'=> $mediaType,
            'ppv_price_chf' => $request->input('price'),
            'ppv_media_path'=> 'placeholder', // updated below
        ]);

        $path = $file->storeAs("ppv/{$message->id}", "media.{$ext}", 'local');
        $message->update(['ppv_media_path' => $path]);

        return back()->with('success', 'PPV-Inhalt gesendet.');
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

        // For PPV messages sent by inserent, count paid purchases
        $ppvMessageIds = $messages->filter(fn($m) => $m->isPpv())->pluck('id');
        $purchaseCounts = PpvPurchase::whereIn('message_id', $ppvMessageIds)
            ->where('status', 'paid')
            ->selectRaw('message_id, count(*) as cnt')
            ->groupBy('message_id')
            ->pluck('cnt', 'message_id');

        $mapped = $messages->map(function ($m) use ($user, $purchaseCounts) {
            return [
                'id'                 => $m->id,
                'body'               => $m->body,
                'from_me'            => $m->from_user_id === $user->id,
                'sender'             => $m->from?->name ?? '',
                'created_at'         => $m->created_at->format('d.m.Y H:i'),
                'ppv_media_type'     => $m->ppv_media_type,
                'ppv_price_chf'      => $m->ppv_price_chf,
                'ppv_media_url'      => $m->isPpv() ? route('media.ppv', $m->id) : null,
                'ppv_purchase_count' => $m->isPpv() ? ($purchaseCounts[$m->id] ?? 0) : null,
            ];
        });

        Message::where('from_user_id', $userId)
            ->where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $mapped]);
    }
}
