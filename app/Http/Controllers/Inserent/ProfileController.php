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
                'nationality'            => $profile->nationality,
                'height_cm'              => $profile->height_cm,
                'eye_color'              => $profile->eye_color,
                'smoking'                => $profile->smoking,
                'tattoo'                 => $profile->tattoo,
                'intimate_area'          => $profile->intimate_area,
                'body_type'              => $profile->body_type,
                'gender'                 => $profile->gender,
                'origin'                 => $profile->origin,
                'weight_kg'              => $profile->weight_kg,
                'cup_size'               => $profile->cup_size,
                'breast_type'            => $profile->breast_type,
                'has_video'              => $profile->has_video,
                'whatsapp_number'        => $profile->whatsapp_number,
                'telegram_username'      => $profile->telegram_username,
                'address'                => $profile->address,
                'website'                => $profile->website,
                'subscription_price_chf' => $profile->subscription_price_chf,
                'tag_ids'                => $profile->tags->pluck('id'),
                'status'                 => $profile->status,
                'listing_expires_at'     => $profile->listing_expires_at?->format('d.m.Y'),
            ] : null,
            // cities and categories come from HandleInertiaRequests (include slug)
            'tags' => Tag::orderBy('name')->get(['id', 'name', 'group']),
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
            'nationality'            => $data['nationality'] ?? null,
            'height_cm'              => $data['height_cm'] ?? null,
            'eye_color'              => $data['eye_color'] ?? null,
            'smoking'                => $data['smoking'] ?? null,
            'tattoo'                 => $data['tattoo'] ?? null,
            'intimate_area'          => $data['intimate_area'] ?? null,
            'body_type'              => $data['body_type'] ?? null,
            'gender'                 => $data['gender'] ?? null,
            'origin'                 => $data['origin'] ?? null,
            'weight_kg'              => $data['weight_kg'] ?? null,
            'cup_size'               => $data['cup_size'] ?? null,
            'breast_type'            => $data['breast_type'] ?? null,
            'has_video'              => $data['has_video'] ?? false,
            'subscription_price_chf' => $data['subscription_price_chf'],
            'telegram_username'      => $data['telegram_username'] ?? null,
            'address'                => $data['address'] ?? null,
            'website'                => $data['website'] ?? null,
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
            'nationality'            => $data['nationality'] ?? null,
            'height_cm'              => $data['height_cm'] ?? null,
            'eye_color'              => $data['eye_color'] ?? null,
            'smoking'                => $data['smoking'] ?? null,
            'tattoo'                 => $data['tattoo'] ?? null,
            'intimate_area'          => $data['intimate_area'] ?? null,
            'body_type'              => $data['body_type'] ?? null,
            'gender'                 => $data['gender'] ?? null,
            'origin'                 => $data['origin'] ?? null,
            'weight_kg'              => $data['weight_kg'] ?? null,
            'cup_size'               => $data['cup_size'] ?? null,
            'breast_type'            => $data['breast_type'] ?? null,
            'has_video'              => $data['has_video'] ?? false,
            'subscription_price_chf' => $data['subscription_price_chf'],
            'telegram_username'      => $data['telegram_username'] ?? null,
            'address'                => $data['address'] ?? null,
            'website'                => $data['website'] ?? null,
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

    public function deactivate(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            return redirect()->route('inserat.dashboard');
        }

        $profile->update(['status' => 'draft']);

        return redirect()->route('inserat.dashboard')
            ->with('success', 'Profil deaktiviert. Es ist nicht mehr öffentlich sichtbar.');
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
            'nationality'            => ['nullable', 'string', 'max:60'],
            'height_cm'              => ['nullable', 'integer', 'min:120', 'max:230'],
            'eye_color'              => ['nullable', 'string', 'max:30'],
            'smoking'                => ['nullable', 'boolean'],
            'tattoo'                 => ['nullable', 'boolean'],
            'intimate_area'          => ['nullable', 'string', 'in:Glatt,Teilrasiert,Natürlich'],
            'body_type'              => ['nullable', 'string', 'in:Schlank,Sportlich,Rundlich'],
            'gender'                 => ['nullable', 'string', 'in:frau,trans,gigolo'],
            'origin'                 => ['nullable', 'string', 'in:europaeisch,asiatisch,schwarz,indisch,latina,gemischt'],
            'weight_kg'              => ['nullable', 'integer', 'min:30', 'max:200'],
            'cup_size'               => ['nullable', 'string', 'in:A,B,C,D,E,F,G'],
            'breast_type'            => ['nullable', 'string', 'in:natur,implantate'],
            'has_video'              => ['nullable', 'boolean'],
            'whatsapp_number'        => ['nullable', 'string', 'max:20'],
            'telegram_username'      => ['nullable', 'string', 'max:100'],
            'address'                => ['nullable', 'string', 'max:255'],
            'website'                => ['nullable', 'url', 'max:255'],
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
