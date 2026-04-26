<?php
namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile()->with(['city', 'category', 'tags'])->first();

        return inertia('Inserent/Profile', [
            'profile'    => $profile ? [
                'id'                     => $profile->id,
                'display_name'           => $profile->display_name,
                'description'            => $profile->description,
                'city_id'                => $profile->city_id,
                'category_id'            => $profile->category_id,
                'age'                    => $profile->age,
                'whatsapp_number'        => $profile->whatsapp_number,
                'subscription_price_chf' => $profile->subscription_price_chf,
                'tag_ids'                => $profile->tags->pluck('id'),
                'status'                 => $profile->status,
                'listing_expires_at'     => $profile->listing_expires_at?->format('d.m.Y'),
            ] : null,
            // cities and categories come from HandleInertiaRequests (include slug)
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->profile()->exists()) {
            return redirect()->route('inserat.profile.edit')
                ->with('error', 'Du hast bereits ein Profil.');
        }

        $data = $this->validateProfile($request);

        $profile = $user->profile()->create([
            'slug'                   => $this->uniqueSlug($data['display_name']),
            'display_name'           => $data['display_name'],
            'description'            => $data['description'] ?? null,
            'city_id'                => $data['city_id'],
            'category_id'            => $data['category_id'],
            'age'                    => $data['age'],
            'subscription_price_chf' => $data['subscription_price_chf'],
            'status'                 => 'draft',
        ]);

        if (isset($data['whatsapp_number'])) {
            $profile->whatsapp_number = $data['whatsapp_number'];
            $profile->save();
        }

        if (! empty($data['tag_ids'])) {
            $profile->tags()->sync($data['tag_ids']);
        }

        // Rolle auf inserent setzen falls noch nicht
        if ($user->role !== 'inserent') {
            $user->update(['role' => 'inserent']);
        }

        return redirect()->route('inserat.package.select')
            ->with('success', 'Profil gespeichert! Wähle jetzt ein Paket, um sichtbar zu werden.');
    }

    public function update(Request $request)
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            return redirect()->route('inserat.profile.edit');
        }

        $data = $this->validateProfile($request);

        $profile->update([
            'display_name'           => $data['display_name'],
            'description'            => $data['description'] ?? null,
            'city_id'                => $data['city_id'],
            'category_id'            => $data['category_id'],
            'age'                    => $data['age'],
            'subscription_price_chf' => $data['subscription_price_chf'],
        ]);

        if (isset($data['whatsapp_number'])) {
            $profile->whatsapp_number = $data['whatsapp_number'];
            $profile->save();
        }

        if (! empty($data['tag_ids'])) {
            $profile->tags()->sync($data['tag_ids']);
        }

        return redirect()->route('inserat.dashboard')
            ->with('success', 'Profil erfolgreich aktualisiert.');
    }

    public function destroy(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->route('inserat.dashboard');
        }

        // Cancel active Stripe subscriptions for all subscribers
        $activeSubs = $profile->subscriptions()
            ->whereNotNull('stripe_subscription_id')
            ->whereIn('status', ['active'])
            ->get();

        if ($activeSubs->isNotEmpty()) {
            $stripe = new StripeClient(config('cashier.secret'));
            foreach ($activeSubs as $sub) {
                try {
                    $stripe->subscriptions->cancel($sub->stripe_subscription_id);
                } catch (\Exception $e) {
                    Log::warning("Could not cancel Stripe sub {$sub->stripe_subscription_id}: " . $e->getMessage());
                }
            }
        }

        // Delete all media files from storage
        Storage::disk('local')->deleteDirectory("media/{$profile->id}");

        // Delete profile — DB cascades handle media, subscriptions, reviews, payouts
        $profile->delete();

        // Reset user role back to member
        $user->update(['role' => 'member']);

        return redirect()->route('home')
            ->with('success', 'Dein Profil wurde erfolgreich gelöscht.');
    }

    private function validateProfile(Request $request): array
    {
        return $request->validate([
            'display_name'           => ['required', 'string', 'min:2', 'max:60'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'city_id'                => ['required', 'exists:cities,id'],
            'category_id'            => ['required', 'exists:categories,id'],
            'age'                    => ['required', 'integer', 'min:18', 'max:99'],
            'whatsapp_number'        => ['nullable', 'string', 'max:20'],
            'subscription_price_chf' => ['required', 'numeric', 'min:9', 'max:999'],
            'tag_ids'                => ['nullable', 'array'],
            'tag_ids.*'              => ['exists:tags,id'],
        ], [
            'display_name.required'           => 'Bitte gib einen Namen an.',
            'display_name.min'                => 'Der Name muss mindestens 2 Zeichen lang sein.',
            'city_id.required'                => 'Bitte wähle eine Stadt.',
            'category_id.required'            => 'Bitte wähle eine Kategorie.',
            'age.required'                    => 'Bitte gib dein Alter an.',
            'age.min'                         => 'Du musst mindestens 18 Jahre alt sein.',
            'subscription_price_chf.required' => 'Bitte lege einen Abo-Preis fest.',
            'subscription_price_chf.min'      => 'Der Mindestpreis beträgt CHF 9.',
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;
        while (\App\Models\Profile::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
