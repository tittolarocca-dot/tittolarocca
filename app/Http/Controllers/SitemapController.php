<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Club;
use App\Models\Profile;
use App\Models\Tag;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    /**
     * Dynamische XML-Sitemap. Listet alle öffentlichen, indexierbaren URLs:
     * Startseite, Städte, Kategorien, Services, Kanton-Club-Seiten, Clubs und
     * alle aktiven Inserate. Wird in der robots.txt referenziert.
     *
     * Robust gegen Schema-Abweichungen: <lastmod> wird nur ausgegeben, wenn
     * die jeweilige Tabelle tatsächlich eine updated_at-Spalte besitzt.
     */
    public function index(): Response
    {
        $urls = [];

        $add = function (?string $loc, string $changefreq, string $priority, $lastmod = null) use (&$urls) {
            if (! $loc) {
                return;
            }
            $urls[] = [
                'loc'        => $loc,
                'changefreq' => $changefreq,
                'priority'   => $priority,
                'lastmod'    => $lastmod?->toAtomString(),
            ];
        };

        // Statische Kern-Seiten
        $add(route('home'),        'daily',  '1.0');
        $add(route('neue-bilder'), 'daily',  '0.7');
        $add(route('clubs.index'), 'weekly', '0.6');

        // Städte (lokale Landingpages)
        $cityHasTs = Schema::hasColumn('cities', 'updated_at');
        foreach (City::where('is_active', true)->get() as $city) {
            $add($city->slug ? route('city', $city->slug) : null, 'daily', '0.8', $cityHasTs ? $city->updated_at : null);
        }

        // Kategorien
        $catHasTs = Schema::hasColumn('categories', 'updated_at');
        foreach (Category::where('is_active', true)->get() as $category) {
            $add($category->slug ? route('category', $category->slug) : null, 'weekly', '0.7', $catHasTs ? $category->updated_at : null);
        }

        // Services / Leistungen
        foreach (Tag::all() as $tag) {
            $add($tag->slug ? route('service', $tag->slug) : null, 'weekly', '0.5');
        }

        // Kanton-Club-Seiten
        foreach (array_column(config('cantons', []), 'slug') as $cantonSlug) {
            $add($cantonSlug ? route('clubs.canton', $cantonSlug) : null, 'weekly', '0.5');
        }

        // Clubs
        $clubHasTs = Schema::hasColumn('clubs', 'updated_at');
        foreach (Club::active()->get() as $club) {
            $add($club->slug ? route('clubs.show', $club->slug) : null, 'weekly', '0.6', $clubHasTs ? $club->updated_at : null);
        }

        // Aktive Inserate (in Blöcken, speicherschonend)
        $profileHasTs = Schema::hasColumn('profiles', 'updated_at');
        Profile::where('status', 'active')
            ->where('listing_expires_at', '>', now())
            ->orderBy('id')
            ->chunk(500, function ($profiles) use ($add, $profileHasTs) {
                foreach ($profiles as $profile) {
                    $add($profile->slug ? route('profile.show', $profile->slug) : null, 'daily', '0.9', $profileHasTs ? $profile->updated_at : null);
                }
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
