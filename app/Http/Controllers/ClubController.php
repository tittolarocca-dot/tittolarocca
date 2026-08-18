<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubReport;
use App\Support\SeoData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClubController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderList($request, null);
    }

    public function canton(Request $request, string $canton)
    {
        $code = collect(config('cantons'))->search(fn ($c) => $c['slug'] === $canton);
        if ($code === false) {
            abort(404);
        }
        return $this->renderList($request, $code);
    }

    private function renderList(Request $request, ?string $cantonCode)
    {
        $search   = trim((string) $request->query('q', ''));
        $category = $request->query('category');
        $opening  = $request->query('opening');
        $sort     = $request->query('sort', 'premium');
        $canton   = $cantonCode ?: $request->query('canton');

        $q = Club::active();

        if ($search !== '') {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%"));
        }
        if ($canton)   $q->where('canton', $canton);
        if ($category) $q->where('category', $category);

        // Premium immer zuerst; Distanz-Sortierung erfolgt clientseitig (Etappe 2).
        $q->orderByDesc('is_premium');
        match ($sort) {
            'rating' => $q->orderByDesc('rating_average')->orderBy('name'),
            'newest' => $q->orderByDesc('created_at'),
            'alpha'  => $q->orderBy('name'),
            default  => $q->orderByDesc('rating_average')->orderBy('name'),
        };

        $clubs = $q->get();

        // Öffnungszeiten-Filter (JSON-Logik → in PHP)
        if (in_array($opening, ['open_now', 'today', '24h', 'weekend'], true)) {
            $clubs = $clubs->filter(fn (Club $c) => match ($opening) {
                'open_now' => $c->isOpenNow(),
                'today'    => $c->isOpenToday(),
                '24h'      => $c->is24h(),
                'weekend'  => $c->isOpenWeekend(),
            })->values();
        }

        $cantonName = $cantonCode ? config("cantons.{$cantonCode}.name") : null;

        $meta = $this->listMeta($cantonName);
        $seo  = app(SeoData::class)->forPage($meta['title'], $meta['description']);
        $crumbs = [['Startseite', route('home')], ['Clubs', route('clubs.index')]];
        if ($cantonCode && $cantonName) {
            $crumbs[] = [$cantonName, route('clubs.canton', config("cantons.{$cantonCode}.slug"))];
        }
        $seo->addBreadcrumb($crumbs);

        return Inertia::render('Clubs/Index', [
            'clubs'       => $clubs->map(fn (Club $c) => $this->cardData($c))->values(),
            'cantons'     => collect(config('cantons'))->map(fn ($c, $code) => [
                'code' => $code, 'slug' => $c['slug'], 'name' => $c['name'],
            ])->values(),
            'categories'  => Club::CATEGORIES,
            'filters'     => [
                'q' => $search, 'canton' => $canton, 'category' => $category,
                'opening' => $opening, 'sort' => $sort,
            ],
            'activeCanton' => $cantonCode ? ['code' => $cantonCode, 'name' => $cantonName] : null,
            'meta'        => $meta,
        ]);
    }

    public function show(Club $club)
    {
        if ($club->status !== 'active') {
            abort(404);
        }

        $club->increment('profile_views');

        $meta = [
            'title'       => "{$club->name} – {$club->category} in {$club->city} | Clubs",
            'description' => "Erotikclub {$club->name} in {$club->city} ({$club->canton_name}) – Adresse, Öffnungszeiten und Website.",
        ];
        app(SeoData::class)
            ->forPage($meta['title'], $meta['description'])
            ->addBreadcrumb([
                ['Startseite', route('home')],
                ['Clubs', route('clubs.index')],
                [$club->name, url()->current()],
            ])
            ->addJsonLd(array_filter([
                '@context'  => 'https://schema.org',
                '@type'     => 'LocalBusiness',
                'name'      => $club->name,
                'url'       => url()->current(),
                'telephone' => $club->phone ?: null,
                'address'   => array_filter([
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => $club->address ?: null,
                    'addressLocality' => $club->city ?: null,
                    'postalCode'      => $club->postal_code ?: null,
                    'addressRegion'   => $club->canton_name ?: null,
                    'addressCountry'  => 'CH',
                ]),
            ]));

        return Inertia::render('Clubs/Show', [
            'club' => array_merge($this->cardData($club), [
                'description'  => $club->description,
                'postal_code'  => $club->postal_code,
                'phone'        => $club->phone,
                'email'        => $club->email,
                'opening_hours'=> $club->opening_hours ?? [],
                'is_24h'       => $club->is24h(),
            ]),
            'supportEmail' => config('mail.from.address'),
            'meta' => $meta,
        ]);
    }

    /** Betreiber/Besucher meldet eine Änderung oder beansprucht den Eintrag. */
    public function report(Request $request, Club $club)
    {
        $data = $request->validate([
            'type'    => ['required', 'in:report,claim'],
            'message' => ['required', 'string', 'max:2000'],
            'email'   => ['nullable', 'email', 'max:190'],
        ]);

        ClubReport::create([
            'club_id' => $club->id,
            'type'    => $data['type'],
            'message' => $data['message'],
            'email'   => $data['email'] ?? null,
            'status'  => 'open',
        ]);

        return back()->with('success', 'Danke! Deine Meldung wurde übermittelt.');
    }

    /** Zählt den Website-Klick und leitet weiter. */
    public function visit(Club $club)
    {
        $club->increment('website_clicks');

        if (! $club->website_url) {
            return redirect()->route('clubs.show', $club->slug);
        }
        return redirect()->away($club->website_url);
    }

    private function cardData(Club $c): array
    {
        return [
            'id'             => $c->id,
            'name'           => $c->name,
            'slug'           => $c->slug,
            'city'           => $c->city,
            'address'        => $c->address,
            'canton'         => $c->canton,
            'canton_name'    => $c->canton_name,
            'canton_slug'    => $c->canton_slug,
            'category'       => $c->category,
            'is_premium'     => $c->is_premium,
            'is_verified'    => $c->is_verified,
            'rating_average' => $c->rating_average,
            'rating_count'   => $c->rating_count,
            'website_url'    => $c->website_url,
            'today_label'    => $c->todayLabel(),
            'is_open_now'    => $c->isOpenNow(),
            'latitude'       => $c->latitude,
            'longitude'      => $c->longitude,
        ];
    }

    private function listMeta(?string $cantonName): array
    {
        if ($cantonName) {
            return [
                'title'       => "Erotikclubs in {$cantonName} – Clubs, Studios & Lokale finden",
                'description' => "Finde Erotikclubs, Studios und Lokale in {$cantonName} mit Adresse, Öffnungszeiten und Website.",
            ];
        }
        return [
            'title'       => 'Clubs & Lokale in der Schweiz – Erotikclubs, Studios & Saunaclubs',
            'description' => 'Erotikclubs, Studios, Saunaclubs und Lokale in der Schweiz nach Kanton, Kategorie und Entfernung finden.',
        ];
    }
}
