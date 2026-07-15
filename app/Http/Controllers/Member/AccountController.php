<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Stripe\StripeClient;

class AccountController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return inertia('Member/EditProfile', [
            'member' => [
                'name'        => $user->name,
                'gender'      => $user->gender,
                'age'         => $user->age,
                'height_cm'   => $user->height_cm,
                'weight_kg'   => $user->weight_kg,
                'city_id'     => $user->city_id,
                'languages'   => $user->languages ?? [],
                'smoking'     => $user->smoking,
                'bio'         => $user->bio,
                'preferences' => $user->preferences,
            ],
            // slug muss enthalten sein, da die geteilten cities (Header-Navigation)
            // damit überschrieben werden – sonst bricht route('city', city.slug).
            'cities' => City::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'canton', 'slug']),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'gender'      => ['nullable', Rule::in(['frau', 'mann', 'paar', 'trans', 'divers'])],
            'age'         => ['nullable', 'integer', 'min:18', 'max:99'],
            'height_cm'   => ['nullable', 'integer', 'min:120', 'max:230'],
            'weight_kg'   => ['nullable', 'integer', 'min:30', 'max:200'],
            'city_id'     => ['nullable', 'exists:cities,id'],
            'languages'   => ['nullable', 'array', 'max:15'],
            'languages.*' => ['string', 'max:40'],
            'smoking'     => ['nullable', 'boolean'],
            'bio'         => ['nullable', 'string', 'max:2500'],
            'preferences' => ['nullable', 'string', 'max:2500'],
        ]);

        $user->update([
            'gender'      => $data['gender'] ?? null,
            'age'         => $data['age'] ?? null,
            'height_cm'   => $data['height_cm'] ?? null,
            'weight_kg'   => $data['weight_kg'] ?? null,
            'city_id'     => $data['city_id'] ?? null,
            'languages'   => ! empty($data['languages']) ? array_values($data['languages']) : null,
            'smoking'     => $data['smoking'] ?? null,
            'bio'         => $data['bio'] ?? null,
            'preferences' => $data['preferences'] ?? null,
        ]);

        return back()->with('success', 'Profil gespeichert.');
    }

    /** Konto deaktivieren – reversibel: das nächste Login reaktiviert es. */
    public function deactivate(Request $request)
    {
        $user = $request->user();
        $user->update(['deactivated_at' => now()]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Konto deaktiviert. Melde dich einfach wieder an, um es zu reaktivieren.');
    }

    /** Konto endgültig löschen. */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Aktive Stripe-Abos kündigen
        $subs = $user->platformSubscriptions()
            ->whereNotNull('stripe_subscription_id')
            ->whereIn('status', ['active', 'trialing'])
            ->get();

        if ($subs->isNotEmpty()) {
            $stripe = new StripeClient(config('cashier.secret'));
            foreach ($subs as $sub) {
                try {
                    $stripe->subscriptions->cancel($sub->stripe_subscription_id);
                } catch (\Throwable $e) {
                    Log::warning("Konto-Löschung: Stripe-Abo {$sub->stripe_subscription_id} nicht gekündigt: " . $e->getMessage());
                }
            }
        }

        $user->favorites()->detach();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete(); // Bewertungen etc. via FK-Cascade

        return redirect()->route('home')->with('success', 'Dein Konto wurde gelöscht.');
    }
}
