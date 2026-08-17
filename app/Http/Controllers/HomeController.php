<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Category;
use App\Models\Profile;
use App\Models\Tag;
use Illuminate\Http\Request;

class HomeController extends Controller
{
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

    public function index(Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)->paginate(20)->withQueryString(),
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ]));
    }

    public function city(City $city, Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)->where('city_id', $city->id)->paginate(20)->withQueryString(),
            'activeCity'       => $city,
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ]));
    }

    public function category(Category $category, Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        // Pfad-Kategorie in die Mehrfachauswahl aufnehmen (Direktlinks /kategorie/{slug} bleiben gültig)
        $categories = array_values(array_unique(array_merge($categories, [$category->slug])));
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)->paginate(20)->withQueryString(),
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ]));
    }

    public function service(Tag $tag, Request $request)
    {
        [$search, $verified, $services, $ages, $categories] = $this->filters($request);
        // Pfad-Service in die Mehrfachauswahl aufnehmen (Direktlinks /service/{slug} bleiben gültig)
        $services = array_values(array_unique(array_merge($services, [$tag->slug])));
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'         => $this->baseQuery($search, $verified, $services, $ages, $categories)
                ->paginate(20)->withQueryString(),
            'activeSearch'     => $search,
            'activeAges'       => $ages,
            'activeVerified'   => $verified,
            'activeServices'   => $services,
            'activeCategories' => $categories,
        ]));
    }
}
