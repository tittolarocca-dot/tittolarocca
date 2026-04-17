<?php
namespace App\Http\Controllers;

use App\Models\ListingOrder;
use App\Models\PlatformSubscription;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            // ── Listing-Zahlung erfolgreich ───────────────────────────────
            'checkout.session.completed'    => $this->handleCheckoutCompleted($event->data->object),

            // ── Abo-Zahlungen ─────────────────────────────────────────────
            'invoice.paid'                  => $this->handleInvoicePaid($event->data->object),
            'invoice.payment_failed'        => $this->handleInvoicePaymentFailed($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),

            default => null,
        };

        return response('OK', 200);
    }

    // ── Listing-Paket: Einmalzahlung ─────────────────────────────────────

    private function handleCheckoutCompleted(object $session): void
    {
        $orderId = $session->metadata->order_id ?? null;
        if (! $orderId) return;

        $order = ListingOrder::with('profile', 'listingPackage')->find($orderId);
        if (! $order || $order->status === 'paid') return;

        $order->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        // Profil automatisch aktivieren
        $profile    = $order->profile;
        $package    = $order->listingPackage;
        $expiresAt  = now()->addDays($package->duration_days);

        $profile->update([
            'status'             => 'active',
            'listing_expires_at' => $expiresAt,
        ]);

        Log::info("Profile {$profile->id} activated until {$expiresAt}");
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

        // Abonnenten-Zähler im Profil aktuell halten
        $this->refreshSubscriberCount($sub->profile_id);

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

    private function refreshSubscriberCount(int $profileId): void
    {
        $count = PlatformSubscription::where('profile_id', $profileId)
            ->where('status', 'active')
            ->count();

        Profile::where('id', $profileId)->update(['total_subscribers' => $count]);
    }
}
