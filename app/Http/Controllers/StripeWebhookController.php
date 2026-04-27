<?php
namespace App\Http\Controllers;

use App\Mail\NewSubscriptionMail;
use App\Mail\ProfileApprovedMail;
use App\Models\ListingOrder;
use App\Models\PlatformSubscription;
use App\Models\Profile;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('cashier.secret'));

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature mismatch', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload');
            return response('Invalid payload', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type]);

        match ($event->type) {
            // ── Listing / Abo checkout ────────────────────────────────────
            'checkout.session.completed'    => $this->handleCheckoutCompleted($event->data->object),

            // ── Abo-Zahlungen ─────────────────────────────────────────────
            'invoice.paid'                  => $this->handleInvoicePaid($event->data->object),
            'invoice.payment_succeeded'     => $this->handleInvoicePaid($event->data->object),
            'invoice.payment_failed'        => $this->handleInvoicePaymentFailed($event->data->object),
            'customer.subscription.created' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),

            // ── Stripe Connect: Creator-Konto aktualisiert ────────────────
            // Requires "Events from connected accounts" enabled in Stripe Dashboard
            'account.updated'               => $this->handleConnectAccountUpdated($event->data->object),

            default => null,
        };

        return response('OK', 200);
    }

    // ── Checkout abgeschlossen (Listing ODER Abo) ────────────────────────

    private function handleCheckoutCompleted(object $session): void
    {
        $type = $session->metadata->type ?? 'listing';

        if ($type === 'subscription') {
            $this->activatePlatformSubscription($session);
        } elseif ($type === 'push') {
            $this->handlePushPayment($session);
        } else {
            $this->activateListingOrder($session);
        }
    }

    private function activateListingOrder(object $session): void
    {
        $orderId = $session->metadata->order_id ?? null;
        if (! $orderId) return;

        $order = ListingOrder::with('profile', 'listingPackage')->find($orderId);
        if (! $order || $order->status === 'paid') return;

        $order->update(['status' => 'paid', 'paid_at' => now()]);

        $profile   = $order->profile;
        $package   = $order->listingPackage;
        $expiresAt = now()->addDays($package->duration_days);

        $profile->update([
            'status'             => 'active',
            'listing_expires_at' => $expiresAt,
        ]);

        try {
            Mail::to($profile->user->email)->send(new ProfileApprovedMail($profile));
        } catch (\Exception $e) {
            Log::warning('ProfileApprovedMail failed: ' . $e->getMessage());
        }

        Log::info("Profile {$profile->id} activated until {$expiresAt}");
    }

    private function activatePlatformSubscription(object $session): void
    {
        $profileId   = $session->metadata->profile_id ?? null;
        $userId      = $session->metadata->user_id ?? null;
        $stripeSubId = $session->subscription ?? null;

        if (! $profileId || ! $userId || ! $stripeSubId) return;

        if (PlatformSubscription::where('stripe_subscription_id', $stripeSubId)->exists()) return;

        $profile = Profile::find($profileId);
        $user    = User::find($userId);
        if (! $profile || ! $user) return;

        PlatformSubscription::updateOrCreate(
            ['subscriber_user_id' => $userId, 'profile_id' => $profileId],
            [
                'stripe_subscription_id' => $stripeSubId,
                'amount_chf'             => $profile->subscription_price_chf,
                'status'                 => 'active',
                'current_period_start'   => now(),
                'current_period_end'     => now()->addMonth(),
            ]
        );

        $this->refreshSubscriberCount($profileId);

        try {
            Mail::to($profile->user->email)->send(
                new NewSubscriptionMail($profile, $user, $profile->subscription_price_chf)
            );
        } catch (\Exception $e) {
            Log::warning('NewSubscriptionMail failed: ' . $e->getMessage());
        }

        Log::info("Platform subscription created for user {$userId} → profile {$profileId}");
    }

    // ── Push-Zahlung ─────────────────────────────────────────────────────

    private function handlePushPayment(object $session): void
    {
        $profileId = $session->metadata->profile_id ?? null;
        if (!$profileId) return;

        Profile::where('id', $profileId)->update(['pushed_at' => now()]);
        Log::info("Profile {$profileId} pushed to top.");
    }

    // ── Abo: monatliche Erneuerung ────────────────────────────────────────

    private function handleInvoicePaid(object $invoice): void
    {
        $stripeSubId = $invoice->subscription ?? null;
        if (! $stripeSubId) return;

        $sub = PlatformSubscription::where('stripe_subscription_id', $stripeSubId)->first();
        if (! $sub) return;

        $sub->update([
            'status'               => 'active',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);

        $this->refreshSubscriberCount($sub->profile_id);

        // Record transaction (idempotent via stripe_invoice_id unique index)
        $amountPaid = $invoice->amount_paid ?? 0;
        if ($amountPaid > 0 && $invoice->id ?? null) {
            $gross    = round($amountPaid / 100, 2);
            $fee      = round($gross * 0.20, 2);
            $net      = round($gross - $fee, 2);
            $currency = strtoupper($invoice->currency ?? 'chf');

            Transaction::updateOrCreate(
                ['stripe_invoice_id' => $invoice->id],
                [
                    'fan_user_id'              => $sub->subscriber_user_id,
                    'profile_id'               => $sub->profile_id,
                    'stripe_subscription_id'   => $stripeSubId,
                    'stripe_payment_intent_id' => $invoice->payment_intent ?? null,
                    'type'                     => 'subscription',
                    'gross_amount_chf'         => $gross,
                    'platform_fee_chf'         => $fee,
                    'creator_net_chf'          => $net,
                    'currency'                 => $currency,
                    'status'                   => 'paid',
                ]
            );
        }

        Log::info("Subscription {$sub->id} renewed for profile {$sub->profile_id}");
    }

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        $stripeSubId = $invoice->subscription ?? null;
        if (! $stripeSubId) return;

        PlatformSubscription::where('stripe_subscription_id', $stripeSubId)
            ->update(['status' => 'past_due']);
    }

    private function handleSubscriptionDeleted(object $stripeSub): void
    {
        $sub = PlatformSubscription::where('stripe_subscription_id', $stripeSub->id)->first();
        if (! $sub) return;

        $sub->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->refreshSubscriberCount($sub->profile_id);
    }

    private function handleSubscriptionUpdated(object $stripeSub): void
    {
        $sub = PlatformSubscription::where('stripe_subscription_id', $stripeSub->id)->first();
        if (! $sub) return;

        $sub->update(['status' => $stripeSub->status]);
    }

    // ── Stripe Connect: Creator-Onboarding abgeschlossen ─────────────────

    private function handleConnectAccountUpdated(object $account): void
    {
        $payoutsEnabled = $account->payouts_enabled ?? false;
        $chargesEnabled = $account->charges_enabled ?? false;

        Profile::where('stripe_account_id', $account->id)->update([
            'payouts_enabled' => $payoutsEnabled && $chargesEnabled,
        ]);

        Log::info("Connect account {$account->id} updated: payouts={$payoutsEnabled} charges={$chargesEnabled}");
    }

    private function refreshSubscriberCount(int $profileId): void
    {
        $count = PlatformSubscription::where('profile_id', $profileId)
            ->where('status', 'active')
            ->count();

        Profile::where('id', $profileId)->update(['total_subscribers' => $count]);
    }
}
