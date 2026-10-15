<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfilePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    private function makeProfile(string $slug, bool $active = true): Profile
    {
        $city = City::firstOrCreate(['slug' => 'zuerich'], ['name' => 'Zürich', 'canton' => 'ZH']);
        $cat  = Category::firstOrCreate(['slug' => 'escort'], ['name' => 'Escort']);
        $u = User::create([
            'name' => $slug, 'email' => $slug.'@x.ch', 'password' => bcrypt('x'),
            'role' => 'inserent', 'email_verified_at' => now(),
        ]);
        return Profile::create([
            'user_id' => $u->id, 'display_name' => ucfirst($slug), 'status' => 'active',
            'city_id' => $city->id, 'category_id' => $cat->id, 'subscription_price_chf' => 0,
            'verification_status' => 'approved',
            'listing_expires_at' => $active ? now()->addDays(30) : now()->subDay(),
        ]);
    }

    private function makePost(Profile $p, string $visibility, ?string $publishedAt, string $text): ProfilePost
    {
        return ProfilePost::create([
            'profile_id' => $p->id, 'author_user_id' => $p->user_id,
            'text' => $text, 'post_type' => 'text',
            'visibility' => $visibility, 'published_at' => $publishedAt,
        ]);
    }

    public function test_feed_shows_only_public_published_posts_of_active_profiles(): void
    {
        $a = $this->makeProfile('cleo');            // aktiv
        $b = $this->makeProfile('anna', active: false); // abgelaufen/inaktiv

        $this->makePost($a, 'public',    now()->subMinutes(5), 'Öffentlich sichtbar');
        $this->makePost($a, 'followers', now()->subMinutes(4), 'Nur Follower');
        $this->makePost($a, 'private',   now()->subMinutes(3), 'Privat');
        $this->makePost($a, 'public',    null,                 'Entwurf');        // nicht veröffentlicht
        $this->makePost($a, 'public',    now()->addHour(),     'Geplant');        // Zukunft
        $this->makePost($b, 'public',    now()->subMinutes(2), 'Von inaktivem Profil');

        $this->get('/feed')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Feed/Index', false)
            ->has('posts', 1)
            ->where('posts.0.text', 'Öffentlich sichtbar')
            ->where('posts.0.author.name', 'Cleo')
        );
    }

    public function test_empty_state_when_no_posts(): void
    {
        $this->get('/feed')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Feed/Index', false)->has('posts', 0));
    }

    public function test_json_pagination_endpoint(): void
    {
        $a = $this->makeProfile('lucy');
        for ($i = 0; $i < 15; $i++) {
            $this->makePost($a, 'public', now()->subMinutes(30 - $i), "Beitrag $i");
        }
        // Seite 1 (Inertia) hat 12, nextPageUrl gesetzt
        $this->get('/feed')->assertInertia(fn (Assert $page) => $page
            ->has('posts', 12)->where('nextPageUrl', fn ($u) => ! empty($u)));
        // Seite 2 als JSON
        $res = $this->getJson('/feed?page=2&json=1')->assertOk();
        $this->assertCount(3, $res->json('posts'));
        $this->assertNull($res->json('nextPageUrl'));
    }
}
