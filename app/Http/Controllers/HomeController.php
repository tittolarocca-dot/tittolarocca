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

    private function baseQuery(?string $search = null, ?string $age = null)
    {
        $q = Profile::with(['city', 'category', 'publicMedia',
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

        if ($age) {
            if ($age === '60+') {
                $q->where('age', '>=', 60);
            } elseif (preg_match('/^(\d+)-(\d+)$/', $age, $m)) {
                $q->whereBetween('age', [(int) $m[1], (int) $m[2]]);
            }
        }

        return $q->orderByDesc('pushed_at')->orderByDesc('created_at');
    }

    private function ageAndSearch(Request $request): array
    {
        return [
            trim($request->query('search', '')) ?: null,
            trim($request->query('age', '')) ?: null,
        ];
    }

    public function index(Request $request)
    {
        [$search, $age] = $this->ageAndSearch($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'     => $this->baseQuery($search, $age)->paginate(20)->withQueryString(),
            'activeSearch' => $search,
            'activeAge'    => $age,
        ]));
    }

    public function city(City $city, Request $request)
    {
        [$search, $age] = $this->ageAndSearch($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'     => $this->baseQuery($search, $age)->where('city_id', $city->id)->paginate(20)->withQueryString(),
            'activeCity'   => $city,
            'activeSearch' => $search,
            'activeAge'    => $age,
        ]));
    }

    public function category(Category $category, Request $request)
    {
        [$search, $age] = $this->ageAndSearch($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'       => $this->baseQuery($search, $age)->where('category_id', $category->id)->paginate(20)->withQueryString(),
            'activeCategory' => $category,
            'activeSearch'   => $search,
            'activeAge'      => $age,
        ]));
    }

    public function service(Tag $tag, Request $request)
    {
        [$search, $age] = $this->ageAndSearch($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'      => $this->baseQuery($search, $age)
                ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
                ->paginate(20)->withQueryString(),
            'activeService' => $tag,
            'activeSearch'  => $search,
            'activeAge'     => $age,
        ]));
    }
}
