<?php
namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\ListingPackage;
use App\Models\ListingOrder;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class ListingController extends Controller
{
    public function selectPackage(Request $request)
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            return redirect()->route('inserat.profile.edit')
                ->with('error', 'Bitte erstelle zuerst dein Profil.');
        }

        return inertia('Inserent/PackageSelect', [
            'packages' => ListingPackage::where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'profile'  => [
                'display_name'   => $profile->display_name,
                'status'         => $profile->status,
                'listing_expires_at' => $profile->listing_expires_at?->format('d.m.Y'),
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'exists:listing_packages,id'],
        ]);

        $user    = $request->user();
        $profile = $user->profile;

        if (! $profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        $package = ListingPackage::findOrFail($request->package_id);

        // Gratis-Paket: direkt aktivieren ohne Stripe
        if ($package->price_chf == 0) {
            $expiresAt = now()->addDays($package->duration_days);
            $profile->update([
                'status'             => 'active',
                'listing_expires_at' => $expiresAt,
            ]);

            ListingOrder::create([
                'profile_id'         => $profile->id,
                'listing_package_id' => $package->id,
                'user_id'            => $user->id,
                'amount_chf'         => 0,
                'currency'           => 'CHF',
                'status'             => 'paid',
                'paid_at'            => now(),
            ]);

            return redirect()->route('payment.success')
                ->with('success', 'Gratis-Inserat ist jetzt für ' . $package->duration_days . ' Tage aktiv!');
        }

        // Offene Order für dieses Profil + Paket (idempotent)
        $order = ListingOrder::firstOrCreate(
            [
                'profile_id'         => $profile->id,
                'listing_package_id' => $package->id,
                'status'             => 'pending',
            ],
            [
                'user_id'    => $user->id,
                'amount_chf' => $package->price_chf,
                'currency'   => 'CHF',
            ]
        );

        // Stripe Checkout Session erstellen
        Stripe::setApiKey(config('cashier.secret'));

        $stripeSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'chf',
                    'unit_amount'  => (int) ($package->price_chf * 100), // Rappen
                    'product_data' => [
                        'name'        => "Inserat-Paket: {$package->name}",
                        'description' => "{$package->duration_days} Tage Laufzeit für \"{$profile->display_name}\"",
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'order_id'   => $order->id,
                'profile_id' => $profile->id,
                'package_id' => $package->id,
                'user_id'    => $user->id,
            ],
            'customer_email' => $user->email,
            'success_url'    => route('payment.success')  . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => route('payment.cancelled') . '?type=listing',
        ]);

        // Session-ID in Order speichern
        $order->update(['stripe_payment_intent_id' => $stripeSession->id]);

        return redirect($stripeSession->url);
    }

    public function reactivate(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        // Only allow free reactivation if the profile has never had a paid order
        if ($profile->listingOrders()->where('amount_chf', '>', 0)->exists()) {
            return back()->with('error', 'Gratis-Reaktivierung nur für kostenlose Inserate möglich.');
        }

        $profile->update([
            'status'             => 'active',
            'listing_expires_at' => now()->addDays(7),
        ]);

        ListingOrder::create([
            'profile_id'         => $profile->id,
            'listing_package_id' => ListingPackage::where('price_chf', 0)->value('id'),
            'user_id'            => $user->id,
            'amount_chf'         => 0,
            'currency'           => 'CHF',
            'status'             => 'paid',
            'paid_at'            => now(),
            'expires_at'         => now()->addDays(7),
        ]);

        return back()->with('success', 'Dein Inserat ist jetzt wieder für 7 Tage aktiv!');
    }
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile || !$profile->isActive()) {
            return back()->with('error', 'Dein Inserat muss aktiv sein, um es zu pushen.');
        }

        Stripe::setApiKey(config('cashier.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'chf',
                    'unit_amount'  => 500,
                    'product_data' => [
                        'name'        => 'Inserat pushen – 24h Top-Platzierung',
                        'description' => "\"{ $profile->display_name}\" wird auf die erste Seite gepusht.",
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'type'       => 'push',
                'profile_id' => $profile->id,
                'user_id'    => $user->id,
            ],
            'customer_email' => $user->email,
            'success_url'    => route('inserat.dashboard') . '?pushed=1',
            'cancel_url'     => route('inserat.dashboard'),
        ]);

        return redirect($session->url);
    }
}
