<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Category;
use App\Models\Profile;
use App\Models\Tag;
use App\Support\SeoData;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Setzt die SEO-Meta-Daten für die aktuelle Seite und gibt sie zurück.
     * $canonical ist die normalisierte URL (ohne Filter-/Seiten-Parameter),
     * damit gefilterte/paginierte Varianten nicht als Duplikate gelten.
     */
    private function seo(string $title, string $description, string $canonical): SeoData
    {
        return app(SeoData::class)->forPage($title, $description)->setCanonical($canonical);
    }

    /**
     * Gemeinsame SEO-Props für die Inertia-Seite. $heading ist die sichtbare,
     * lokalisierte H1 (z. B. "Sex, Escort & Begleitung in Luzern und Umgebung");
     * fehlt sie, nutzt das Frontend den generischen Hero-Text. $crumbs sind
     * sichtbare Breadcrumbs, $cityIntro der einzigartige Stadttext und
     * $categoryLinks die crawlbaren Stadt×Kategorie-Links.
     */
    private function seoProps(
        SeoData $seo,
        ?string $heading = null,
        array $crumbs = [],
        ?string $cityIntro = null,
        array $categoryLinks = []
    ): array {
        return [
            'seoTitle'          => $seo->pageTitle,
            'seoDescription'    => $seo->description,
            'seoHeading'        => $heading,
            'crumbs'            => $crumbs,
            'cityIntro'         => $cityIntro,
            'cityCategoryLinks' => $categoryLinks,
        ];
    }

    private function sharedData(): array
    {
        return [
            'cities'     => City::where('is_active', true)->orderBy('sort_order')->get(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'services'   => Tag::orderBy('name')->get(['id', 'name', 'slug']),
        ];
    }

    private function baseQuery(?string $search, ?string $verified, array $services, array $ages, array $categories)
    {
        $q = Profile::with(['city', 'category',
            'publicMedia' => fn ($q) => $q->orderBy('sort_order'),
            'listingOrders' => fn ($q) => $q
                ->where('status', 'paid')
                ->where('expires_at', '>', now())
                ->orderByDesc('paid_at'),
        ])
            ->where('status', 'active')
            ->where('listing_expires_at', '>', now());

        if ($search) {
            $q->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('description',   'like', "%{$search}%");
            });
        }

        // Mehrfachauswahl Alter: Profile in MINDESTENS EINER der gewählten Altersspannen (ODER-Verknüpfung)
        if (! empty($ages)) {
            $q->where(function ($outer) use ($ages) {
                foreach ($ages as $age) {
                    if (preg_match('/^(\d+)\+$/', $age, $m)) {
                        $outer->orWhere('age', '>=', (int) $m[1]);
                    } elseif (preg_match('/^(\d+)-(\d+)$/', $age, $m)) {
                        $outer->orWhereBetween('age', [(int) $m[1], (int) $m[2]]);
                    }
                }
            });
        }

        if ($verified === 'ja') {
            $q->where('verification_status', 'approved');
        } elseif ($verified === 'nein') {
            $q->where('verification_status', '!=', 'approved');
        }

        // Mehrfachauswahl Service: Profile, die MINDESTENS EINEN der gewählten Services anbieten (ODER-Verknüpfung)
        if (! empty($services)) {
            $q->whereHas('tags', fn ($t) => $t->whereIn('tags.slug', $services));
        }

        // Mehrfachauswahl Rubrik: Profile in MINDESTENS EINER der gewählten Kategorien (ODER-Verknüpfung).
        // Nutzt die m:n-Zuordnung, damit ein Profil unter jeder seiner Kategorien erscheint.
        if (! empty($categories)) {
            $q->whereHas('categories', fn ($c) => $c->whereIn('categories.slug', $categories));
        }

        return $q->orderByDesc('pushed_at')->orderByDesc('created_at');
    }

    /**
     * Normalisiert einen Query-Parameter, der als kommagetrennter String
     * (?x=a,b) oder als Array (?x[]=a) kommen kann, zu einer sauberen Slug-Liste.
     */
    private function multiParam(Request $request, string $key): array
    {
        $raw = $request->query($key, []);
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        return array_values(array_unique(array_filter(array_map(
            fn ($s) => trim((string) $s),
            is_array($raw) ? $raw : []
        ))));
    }

    private function filters(Request $request): array
    {
        $verified = $request->query('verified');

        return [
            trim($request->query('search', '')) ?: null,
            in_array($verified, ['ja', 'nein'], true) ? $verified : null,
            $this->multiParam($request, 'services'),
            $this->multiParam($request, 'age'),
            $this->multiParam($request, 'categories'),
        ];
    }

    /**
     * Crawlbare Links zu den Stadt×Kategorie-Seiten einer Stadt
     * (z. B. "Escort Luzern", "Massage Luzern") – für interne Verlinkung.
     */
    private function cityCategoryLinks(City $city): array
    {
        return Category::where('is_active', true)->orderBy('sort_order')->get()
            ->map(fn ($cat) => [
                'name' => "{$cat->name} {$city->name}",
                'url'  => route('city.category', [$city->slug, $cat->slug]),
            ])->values()->all();
    }

    public function index(Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        $seo = $this->seo(
            'Sex, Erotik & Escort in der Schweiz',
            'Sextreffen, Escorts & Begleitung aus der ganzen Schweiz – aktuelle Inserate mit Fotos, nach Stadt und Kategorie. Jetzt entdecken auf booklola.ch.',
            route('home')
        );
        $seo->addJsonLd([
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => config('seo.site_name'),
            'url'      => url('/'),
        ])->addJsonLd([
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => config('seo.site_name'),
            'url'      => url('/'),
            'logo'     => asset('images/logo-booklola.png'),
        ]);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)->paginate(20)->withQueryString(),
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ], $this->seoProps($seo)));
    }

    public function city(City $city, Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        $seo = $this->seo(
            "Sex & Escort {$city->name} – Sextreffen & Begleitung",
            "Sextreffen, Sex-Dates, Escorts & Begleitung in {$city->name} und Region: aktuelle, verifizierte Inserate mit Fotos. Diskret & gratis Kontakt aufnehmen – auf booklola.ch.",
            route('city', $city->slug)
        );
        $seo->addBreadcrumb([
            ['Startseite', route('home')],
            [$city->name, route('city', $city->slug)],
        ]);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)->where('city_id', $city->id)->paginate(20)->withQueryString(),
            'activeCity'       => $city,
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ], $this->seoProps(
            $seo,
            "Sex, Escort & Begleitung in {$city->name} und Umgebung",
            [
                ['name' => 'Startseite', 'url' => route('home')],
                ['name' => $city->name,  'url' => null],
            ],
            $city->intro_text,
            $this->cityCategoryLinks($city),
        )));
    }

    public function cityCategory(City $city, Category $category, Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        // Pfad-Kategorie in die Mehrfachauswahl aufnehmen
        $categories = array_values(array_unique(array_merge($categories, [$category->slug])));
        $seo = $this->seo(
            "{$category->name} {$city->name} – Sex & Begleitung",
            "{$category->name} in {$city->name} und Region: aktuelle Inserate mit Fotos, verifiziert. Sextreffen & Begleitung in {$city->name} – diskret & gratis auf booklola.ch.",
            route('city.category', [$city->slug, $category->slug])
        );
        $seo->addBreadcrumb([
            ['Startseite', route('home')],
            [$city->name, route('city', $city->slug)],
            [$category->name, route('city.category', [$city->slug, $category->slug])],
        ]);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)->where('city_id', $city->id)->paginate(20)->withQueryString(),
            'activeCity'       => $city,
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ], $this->seoProps(
            $seo,
            "{$category->name} in {$city->name}",
            [
                ['name' => 'Startseite',     'url' => route('home')],
                ['name' => $city->name,      'url' => route('city', $city->slug)],
                ['name' => $category->name,  'url' => null],
            ],
            null,
            $this->cityCategoryLinks($city),
        )));
    }

    public function category(Category $category, Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        // Pfad-Kategorie in die Mehrfachauswahl aufnehmen (Direktlinks /kategorie/{slug} bleiben gültig)
        $categories = array_values(array_unique(array_merge($categories, [$category->slug])));
        $seo = $this->seo(
            "{$category->name} – Inserate, Sex & Begleitung Schweiz",
            "{$category->name}: aktuelle Inserate mit Fotos aus der ganzen Schweiz. Sextreffen & Begleitung – diskret auf booklola.ch.",
            route('category', $category->slug)
        );
        $seo->addBreadcrumb([
            ['Startseite', route('home')],
            [$category->name, route('category', $category->slug)],
        ]);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)->paginate(20)->withQueryString(),
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ], $this->seoProps(
            $seo,
            "{$category->name} – Inserate in der Schweiz",
            [
                ['name' => 'Startseite',    'url' => route('home')],
                ['name' => $category->name, 'url' => null],
            ],
        )));
    }

    public function service(Tag $tag, Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        // Pfad-Service in die Mehrfachauswahl aufnehmen (Direktlinks /service/{slug} bleiben gültig)
        $services = array_values(array_unique(array_merge($services, [$tag->slug])));
        $seo = $this->seo(
            "{$tag->name} – Inserate & Begleitung",
            "Inserate mit {$tag->name} – Sex, Begleitung & Erotik in der Schweiz auf booklola.ch.",
            route('service', $tag->slug)
        );
        $seo->addBreadcrumb([
            ['Startseite', route('home')],
            [$tag->name, route('service', $tag->slug)],
        ]);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)
                ->paginate(20)->withQueryString(),
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ], $this->seoProps(
            $seo,
            "{$tag->name} – Inserate in der Schweiz",
            [
                ['name' => 'Startseite', 'url' => route('home')],
                ['name' => $tag->name,   'url' => null],
            ],
        )));
    }
}
