<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Profile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Group conversations by the other user
        $messages = Message::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->with(['from:id,name', 'to:id,name', 'profile:id,display_name,slug'])
            ->orderByDesc('created_at')
            ->get();

        // Build conversation list: keyed by other user
        $conversations = [];
        foreach ($messages as $msg) {
            $otherId    = $msg->from_user_id === $user->id ? $msg->to_user_id : $msg->from_user_id;
            $otherName  = $msg->from_user_id === $user->id ? $msg->to->name : $msg->from->name;
            if (!isset($conversations[$otherId])) {
                $conversations[$otherId] = [
                    'user_id'    => $otherId,
                    'name'       => $otherName,
                    'profile'    => $msg->profile ? [
                        'display_name' => $msg->profile->display_name,
                        'slug'         => $msg->profile->slug,
                    ] : null,
                    'last_message' => $msg->body,
                    'last_at'      => $msg->created_at->format('d.m.Y H:i'),
                    'unread'       => 0,
                ];
            }
            if ($msg->to_user_id === $user->id && !$msg->read_at) {
                $conversations[$otherId]['unread']++;
            }
        }

        return Inertia::render('Member/Messages', [
            'conversations' => array_values($conversations),
        ]);
    }

    public function send(Request $request, Profile $profile)
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $user = $request->user();

        // Only active subscribers can message
        if (!$user->isSubscribedTo($profile)) {
            return back()->with('error', 'Nur Abonnenten können Nachrichten senden.');
        }

        Message::create([
            'from_user_id' => $user->id,
            'to_user_id'   => $profile->user_id,
            'profile_id'   => $profile->id,
            'body'         => $request->input('body'),
        ]);

        return back()->with('success', 'Nachricht gesendet.');
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
        ->get()
        ->map(fn($m) => [
            'id'        => $m->id,
            'body'      => $m->body,
            'from_me'   => $m->from_user_id === $user->id,
            'sender'    => $m->from->name,
            'created_at'=> $m->created_at->format('d.m.Y H:i'),
        ]);

        // Mark as read
        Message::where('from_user_id', $userId)
            ->where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }
}
