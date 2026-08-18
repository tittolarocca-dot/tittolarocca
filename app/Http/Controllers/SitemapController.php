<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Club;
use App\Models\Profile;
use App\Models\Tag;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Dynamische XML-Sitemap. Listet alle öffentlichen, indexierbaren URLs:
     * Startseite, Städte, Kategorien, Services, Kanton-Club-Seiten, Clubs und
     * alle aktiven Inserate. Wird in der robots.txt referenziert.
     */
    public function index(): Response
    {
        $urls = [];

        $add = function (string $loc, string $changefreq, string $priority, $lastmod = null) use (&$urls) {
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
        foreach (City::where('is_active', true)->get(['slug', 'updated_at']) as $city) {
            $add(route('city', $city->slug), 'daily', '0.8', $city->updated_at);
        }

        // Kategorien
        foreach (Category::where('is_active', true)->get(['slug', 'updated_at']) as $category) {
            $add(route('category', $category->slug), 'weekly', '0.7', $category->updated_at);
        }

        // Services / Leistungen
        foreach (Tag::get(['slug']) as $tag) {
            $add(route('service', $tag->slug), 'weekly', '0.5');
        }

        // Kanton-Club-Seiten
        foreach (array_column(config('cantons', []), 'slug') as $cantonSlug) {
            $add(route('clubs.canton', $cantonSlug), 'weekly', '0.5');
        }

        // Clubs
        foreach (Club::active()->get(['slug', 'updated_at']) as $club) {
            $add(route('clubs.show', $club->slug), 'weekly', '0.6', $club->updated_at);
        }

        // Aktive Inserate (in Blöcken, speicherschonend)
        Profile::where('status', 'active')
            ->where('listing_expires_at', '>', now())
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($profiles) use ($add) {
                foreach ($profiles as $profile) {
                    if ($profile->slug) {
                        $add(route('profile.show', $profile->slug), 'daily', '0.9', $profile->updated_at);
                    }
                }
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
