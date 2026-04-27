<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\NewSubscriptionMail;
use App\Models\PlatformSubscription;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = $request->user()
            ->platformSubscriptions()
            ->with('profile.city')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'status'      => $s->status,
                'amount_chf'  => $s->amount_chf,
                'renews_at'   => $s->current_period_end?->format('d.m.Y'),
                'profile'     => [
                    'id'           => $s->profile->id,
                    'display_name' => $s->profile->display_name,
                    'slug'         => $s->profile->slug,
                    'city'         => $s->profile->city?->name,
                ],
            ]);

        return Inertia::render('Member/Subscriptions', compact('subscriptions'));
    }

    public function trial(Request $request, Profile $profile)
    {
        $user = $request->user();

        if (!$profile->isActive()) {
            return back()->with('error', 'Dieses Profil ist nicht mehr aktiv.');
        }

        // Already has any subscription record for this profile?
        $existing = $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'trialing' && $existing->current_period_end?->isFuture()) {
                return back()->with('error', 'Du hast bereits einen laufenden Gratis-Test.');
            }
            return back()->with('error', 'Du hast den Gratis-Test für dieses Profil bereits genutzt.');
        }

        PlatformSubscription::create([
            'subscriber_user_id'   => $user->id,
            'profile_id'           => $profile->id,
            'amount_chf'           => 0,
            'status'               => 'trialing',
            'current_period_start' => now(),
            'current_period_end'   => now()->addDays(3),
        ]);

        $profile->increment('total_subscribers');

        return redirect()->route('profile.show', $profile->slug)
            ->with('success', 'Gratis-Test aktiviert! Du hast 3 Tage Zugang zu allen privaten Inhalten.');
    }

    public function subscribe(Request $request, Profile $profile)
    {
        $user = $request->user();

        if (!$profile->isActive()) {
            return back()->with('error', 'Dieses Profil ist nicht mehr aktiv.');
        }

        if (!$profile->subscription_price_chf || $profile->subscription_price_chf <= 0) {
            return back()->with('error', 'Dieses Profil bietet keine privaten Inhalte an.');
        }

        // Already subscribed?
        $existing = $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->with('error', 'Du hast bereits ein aktives Abonnement.');
        }

        // Ensure profile has a Stripe Price
        $priceId = $this->ensureStripePrice($profile);

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Ensure user has a Stripe customer
        if (!$user->stripe_id) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name'  => $user->name,
                'metadata' => ['user_id' => $user->id],
            ]);
            $user->update(['stripe_id' => $customer->id]);
        }

        $subscriptionData = [
            'metadata' => [
                'profile_id' => $profile->id,
                'user_id'    => $user->id,
            ],
        ];

        // Automatic fee split via Stripe Connect when creator has completed onboarding
        if ($profile->payouts_enabled && $profile->stripe_account_id) {
            $subscriptionData['application_fee_percent'] = 20;
            $subscriptionData['transfer_data']           = [
                'destination' => $profile->stripe_account_id,
            ];
        }

        $session = $stripe->checkout->sessions->create([
            'customer'          => $user->stripe_id,
            'mode'              => 'subscription',
            'line_items'        => [[
                'price'    => $priceId,
                'quantity' => 1,
            ]],
            'success_url'       => route('profile.show', $profile->slug) . '?subscribed=1',
            'cancel_url'        => route('profile.show', $profile->slug),
            'metadata'          => [
                'type'       => 'subscription',
                'profile_id' => $profile->id,
                'user_id'    => $user->id,
            ],
            'subscription_data' => $subscriptionData,
        ]);

        return redirect($session->url);
    }

    public function cancel(Request $request, Profile $profile)
    {
        $user = $request->user();

        $subscription = $user->platformSubscriptions()
            ->where('profile_id', $profile->id)
            ->whereIn('status', ['active', 'trialing'])
            ->first();

        if (!$subscription) {
            return back()->with('error', 'Kein aktives Abonnement gefunden.');
        }

        // Trial: cancel immediately (no Stripe involved)
        if ($subscription->status === 'trialing') {
            $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            $profile->decrement('total_subscribers');
            return back()->with('success', 'Gratis-Test beendet.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Cancel at period end (user keeps access until then)
        $stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $subscription->update(['cancelled_at' => now()]);

        return back()->with('success', 'Abonnement wird zum Periodenende gekündigt. Du behältst bis dahin Zugang.');
    }

    private function ensureStripePrice(Profile $profile): string
    {
        if ($profile->stripe_price_id) {
            return $profile->stripe_price_id;
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $product = $stripe->products->create([
            'name'     => "Privater Zugang: {$profile->display_name}",
            'metadata' => ['profile_id' => $profile->id],
        ]);

        $price = $stripe->prices->create([
            'product'        => $product->id,
            'currency'       => 'chf',
            'unit_amount'    => (int) ($profile->subscription_price_chf * 100),
            'recurring'      => ['interval' => 'month'],
        ]);

        $profile->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id'   => $price->id,
        ]);

        return $price->id;
    }
}
