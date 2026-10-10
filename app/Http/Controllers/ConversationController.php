<?php

namespace App\Http\Controllers;

use App\Models\ChatBlock;
use App\Models\ConversationState;
use App\Models\Message;
use App\Models\Report;
use Illuminate\Http\Request;

/**
 * Konversations-Aktionen aus dem 3-Punkte-Menü – gemeinsam für Mitglieder und
 * Inserierende (beide sind eingeloggte Nutzer). Alle Aktionen beziehen sich auf
 * die Sicht des eingeloggten Nutzers auf die Konversation mit $userId.
 */
class ConversationController extends Controller
{
    /** Verhindert, dass man Aktionen „gegen sich selbst" auslöst. */
    private function guard(Request $request, int $userId): int
    {
        abort_if($userId === $request->user()->id, 403);
        return $userId;
    }

    /** Als ungelesen markieren (nur für mich – ändert keine Lesebestätigung der Gegenseite). */
    public function markUnread(Request $request, int $userId)
    {
        $this->guard($request, $userId);
        $state = ConversationState::for($request->user()->id, $userId);
        $state->update(['marked_unread_at' => now()]);

        return back();
    }

    /** Verstecken: aus der Liste wegpacken (Verlauf bleibt; kommt bei neuer Nachricht zurück). */
    public function hide(Request $request, int $userId)
    {
        $this->guard($request, $userId);
        ConversationState::for($request->user()->id, $userId)
            ->update(['hidden_at' => now(), 'marked_unread_at' => null]);

        return back()->with('success', 'Konversation versteckt.');
    }

    /** Löschen: Verlauf für mich leeren + ausblenden (bei neuer Nachricht beginnt es neu). */
    public function clear(Request $request, int $userId)
    {
        $this->guard($request, $userId);
        ConversationState::for($request->user()->id, $userId)
            ->update(['cleared_at' => now(), 'hidden_at' => null, 'marked_unread_at' => null]);

        return back()->with('success', 'Konversation gelöscht.');
    }

    /** Ignorieren = blockieren: die Person kann mir nicht mehr schreiben; aus der Liste nehmen. */
    public function block(Request $request, int $userId)
    {
        $this->guard($request, $userId);
        ChatBlock::firstOrCreate([
            'user_id'         => $request->user()->id,
            'blocked_user_id' => $userId,
        ]);
        ConversationState::for($request->user()->id, $userId)->update(['hidden_at' => now()]);

        return back()->with('success', 'Nutzer blockiert.');
    }

    /** Blockierung aufheben – Konversation wieder einblenden. */
    public function unblock(Request $request, int $userId)
    {
        $this->guard($request, $userId);
        ChatBlock::where('user_id', $request->user()->id)
            ->where('blocked_user_id', $userId)
            ->delete();
        ConversationState::for($request->user()->id, $userId)->update(['hidden_at' => null]);

        return back()->with('success', 'Blockierung aufgehoben.');
    }

    /** Melden: erstellt eine Meldung fürs Admin-Panel. */
    public function report(Request $request, int $userId)
    {
        $this->guard($request, $userId);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        // Nur melden, wenn es die Konversation tatsächlich gibt (Missbrauch vermeiden).
        $exists = Message::where(function ($q) use ($request, $userId) {
            $q->where('from_user_id', $request->user()->id)->where('to_user_id', $userId);
        })->orWhere(function ($q) use ($request, $userId) {
            $q->where('from_user_id', $userId)->where('to_user_id', $request->user()->id);
        })->exists();
        abort_unless($exists, 404);

        Report::create([
            'reporter_user_id' => $request->user()->id,
            'target_type'      => 'user',
            'target_id'        => $userId,
            'reason'           => ($data['reason'] ?? null) ?: 'Konversation/Nutzer aus dem Chat gemeldet.',
            'status'           => 'open',
        ]);

        return back()->with('success', 'Meldung an das Team gesendet. Danke!');
    }
}
