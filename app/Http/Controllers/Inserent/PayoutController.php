<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return redirect()->route('inserat.profile.edit');
        }

        $payouts = Payout::where('profile_id', $profile->id)
            ->orderByDesc('period_start')
            ->get()
            ->map(fn($p) => [
                'id'               => $p->id,
                'period'           => $p->period_start->format('M Y') . ' – ' . $p->period_end->format('M Y'),
                'gross_amount_chf' => $p->gross_amount_chf,
                'commission_chf'   => $p->commission_chf,
                'net_amount_chf'   => $p->net_amount_chf,
                'status'           => $p->status,
                'paid_at'          => $p->paid_at?->format('d.m.Y'),
                'iban'             => $p->iban,
            ]);

        $pendingTotal = Payout::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->sum('net_amount_chf');

        return Inertia::render('Inserent/Payouts', [
            'payouts'      => $payouts,
            'pendingTotal' => $pendingTotal,
            'hasIban'      => !empty($profile->iban ?? null),
        ]);
    }

    public function updateBankDetails(Request $request)
    {
        $request->validate([
            'iban'           => ['required', 'string', 'max:34'],
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:100'],
        ]);

        // Store bank details on pending payouts for this profile
        $profile = $request->user()->profile;
        if (!$profile) return back()->with('error', 'Kein Profil gefunden.');

        Payout::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->update([
                'iban'           => $request->iban,
                'bank_name'      => $request->bank_name,
                'account_holder' => $request->account_holder,
            ]);

        return back()->with('success', 'Bankdaten gespeichert.');
    }
}
