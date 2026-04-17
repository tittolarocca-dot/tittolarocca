<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
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

        // All messages to/from this inserent, grouped by conversation partner
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
                    'last_message' => $msg->body,
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
            'id'         => $m->id,
            'body'       => $m->body,
            'from_me'    => $m->from_user_id === $user->id,
            'sender'     => $m->from->name,
            'created_at' => $m->created_at->format('d.m.Y H:i'),
        ]);

        Message::where('from_user_id', $userId)
            ->where('to_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }
}
