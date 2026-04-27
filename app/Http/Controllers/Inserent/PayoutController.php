<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\StripeClient;

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

        $transactions = Transaction::where('profile_id', $profile->id)
            ->with('fan:id,name')
            ->orderByDesc('created_at')
            ->take(50)
            ->get()
            ->map(fn($t) => [
                'id'               => $t->id,
                'date'             => $t->created_at->format('d.m.Y'),
                'fan_name'         => $t->fan?->name ?? '–',
                'type'             => $t->type,
                'gross_amount_chf' => $t->gross_amount_chf,
                'platform_fee_chf' => $t->platform_fee_chf,
                'creator_net_chf'  => $t->creator_net_chf,
                'currency'         => $t->currency,
                'status'           => $t->status,
            ]);

        return Inertia::render('Inserent/Payouts', [
            'payouts'         => $payouts,
            'pendingTotal'    => $pendingTotal,
            'hasIban'         => !empty($profile->iban ?? null),
            'stripeAccountId' => $profile->stripe_account_id,
            'payoutsEnabled'  => $profile->payouts_enabled,
            'transactions'    => $transactions,
        ]);
    }

    public function updateBankDetails(Request $request)
    {
        $request->validate([
            'iban'           => ['required', 'string', 'max:34'],
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:100'],
        ]);

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

    // ── Stripe Connect Express Onboarding ───────────────────────────────────

    public function onboard(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->route('inserat.profile.edit');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        if (!$profile->stripe_account_id) {
            $account = $stripe->accounts->create([
                'type'     => 'express',
                'country'  => 'CH',
                'email'    => $user->email,
                'metadata' => ['profile_id' => $profile->id],
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
            ]);

            $profile->update(['stripe_account_id' => $account->id]);
            Log::info("Created Stripe Connect account {$account->id} for profile {$profile->id}");
        }

        $link = $stripe->accountLinks->create([
            'account'     => $profile->stripe_account_id,
            'refresh_url' => route('inserat.payouts.onboard.refresh'),
            'return_url'  => route('inserat.payouts') . '?onboarded=1',
            'type'        => 'account_onboarding',
        ]);

        return redirect($link->url);
    }

    public function refresh(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile || !$profile->stripe_account_id) {
            return redirect()->route('inserat.payouts');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $link = $stripe->accountLinks->create([
            'account'     => $profile->stripe_account_id,
            'refresh_url' => route('inserat.payouts.onboard.refresh'),
            'return_url'  => route('inserat.payouts') . '?onboarded=1',
            'type'        => 'account_onboarding',
        ]);

        return redirect($link->url);
    }
}
