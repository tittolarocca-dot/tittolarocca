<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    private function makeInserent(string $slug): User
    {
        $city = City::firstOrCreate(['slug' => 'zuerich'], ['name' => 'Zürich', 'canton' => 'ZH']);
        $cat  = Category::firstOrCreate(['slug' => 'escort'], ['name' => 'Escort']);
        $u = User::create([
            'name' => $slug, 'email' => $slug.'@x.ch', 'password' => bcrypt('x'),
            'role' => 'inserent', 'email_verified_at' => now(),
        ]);
        Profile::create([
            'user_id' => $u->id, 'display_name' => ucfirst($slug), 'slug' => $slug, 'status' => 'active',
            'city_id' => $city->id, 'category_id' => $cat->id, 'subscription_price_chf' => 0,
            'listing_expires_at' => now()->addDays(30),
        ]);
        return $u->fresh();
    }

    public function test_statistics_counts_events_in_selected_range(): void
    {
        $u   = $this->makeInserent('amira');
        $pid = $u->profile->id;

        ProfileEvent::insert([
            ['profile_id' => $pid, 'type' => 'view',     'created_at' => now()->startOfMonth()->addDay()],
            ['profile_id' => $pid, 'type' => 'view',     'created_at' => now()],
            ['profile_id' => $pid, 'type' => 'whatsapp', 'created_at' => now()],
            ['profile_id' => $pid, 'type' => 'call',     'created_at' => now()],
            // Vormonat – darf bei range=month NICHT zählen
            ['profile_id' => $pid, 'type' => 'view',     'created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay()],
        ]);

        $this->actingAs($u)->get('/inserat/statistik?range=month')
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Inserent/Statistics')
                ->where('metrics.0.key', 'views')->where('metrics.0.value', 2)
                ->where('metrics.1.key', 'whatsapp')->where('metrics.1.value', 1)
                ->where('metrics.2.key', 'calls')->where('metrics.2.value', 1)
                ->where('metrics.0.delta', null)   // kein Vergleich gewählt
            );
    }

    public function test_comparison_delta(): void
    {
        $u   = $this->makeInserent('mina');
        $pid = $u->profile->id;

        ProfileEvent::insert([
            ['profile_id' => $pid, 'type' => 'view', 'created_at' => now()->startOfDay()->addHours(2)],
            ['profile_id' => $pid, 'type' => 'view', 'created_at' => now()->startOfDay()->addHours(3)],
            ['profile_id' => $pid, 'type' => 'view', 'created_at' => now()->subDay()->startOfDay()->addHours(2)],
        ]);

        $this->actingAs($u)->get('/inserat/statistik?range=today&compare=yesterday')
            ->assertInertia(fn (Assert $p) => $p
                ->where('metrics.0.key', 'views')
                ->where('metrics.0.value', 2)
                ->where('metrics.0.compare', 1)
                ->where('metrics.0.delta', 1)
            );
    }

    public function test_click_endpoint_logs_event(): void
    {
        $owner   = $this->makeInserent('owner');
        $visitor = User::create(['name' => 'v', 'email' => 'v@x.ch', 'password' => bcrypt('x'), 'role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($visitor)
            ->post('/profil/'.$owner->profile->slug.'/event', ['type' => 'whatsapp'])
            ->assertNoContent();

        $this->assertDatabaseHas('profile_events', [
            'profile_id' => $owner->profile->id, 'type' => 'whatsapp', 'visitor_user_id' => $visitor->id,
        ]);
    }

    public function test_owner_click_not_counted(): void
    {
        $owner = $this->makeInserent('cleo');
        $this->actingAs($owner)
            ->post('/profil/'.$owner->profile->slug.'/event', ['type' => 'call'])
            ->assertNoContent();

        $this->assertDatabaseMissing('profile_events', [
            'profile_id' => $owner->profile->id, 'type' => 'call',
        ]);
    }

    public function test_unknown_event_type_ignored(): void
    {
        $owner   = $this->makeInserent('nora');
        $visitor = User::create(['name' => 'v2', 'email' => 'v2@x.ch', 'password' => bcrypt('x'), 'role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($visitor)
            ->post('/profil/'.$owner->profile->slug.'/event', ['type' => 'hack'])
            ->assertNoContent();

        $this->assertDatabaseMissing('profile_events', [
            'profile_id' => $owner->profile->id, 'type' => 'hack',
        ]);
    }
}
