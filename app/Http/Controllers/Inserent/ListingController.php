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
}
