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

    private function baseQuery(?string $search = null, ?string $age = null, ?string $verified = null)
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
            if (preg_match('/^(\d+)\+$/', $age, $m)) {
                $q->where('age', '>=', (int) $m[1]);
            } elseif (preg_match('/^(\d+)-(\d+)$/', $age, $m)) {
                $q->whereBetween('age', [(int) $m[1], (int) $m[2]]);
            }
        }

        if ($verified === 'ja') {
            $q->where('verification_status', 'approved');
        } elseif ($verified === 'nein') {
            $q->where('verification_status', '!=', 'approved');
        }

        return $q->orderByDesc('pushed_at')->orderByDesc('created_at');
    }

    private function filters(Request $request): array
    {
        $verified = $request->query('verified');

        return [
            trim($request->query('search', '')) ?: null,
            trim($request->query('age', '')) ?: null,
            in_array($verified, ['ja', 'nein'], true) ? $verified : null,
        ];
    }

    public function index(Request $request)
    {
        [$search, $age, $verified] = $this->filters($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'       => $this->baseQuery($search, $age, $verified)->paginate(20)->withQueryString(),
            'activeSearch'   => $search,
            'activeAge'      => $age,
            'activeVerified' => $verified,
        ]));
    }

    public function city(City $city, Request $request)
    {
        [$search, $age, $verified] = $this->filters($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'       => $this->baseQuery($search, $age, $verified)->where('city_id', $city->id)->paginate(20)->withQueryString(),
            'activeCity'     => $city,
            'activeSearch'   => $search,
            'activeAge'      => $age,
            'activeVerified' => $verified,
        ]));
    }

    public function category(Category $category, Request $request)
    {
        [$search, $age, $verified] = $this->filters($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'       => $this->baseQuery($search, $age, $verified)->where('category_id', $category->id)->paginate(20)->withQueryString(),
            'activeCategory' => $category,
            'activeSearch'   => $search,
            'activeAge'      => $age,
            'activeVerified' => $verified,
        ]));
    }

    public function service(Tag $tag, Request $request)
    {
        [$search, $age, $verified] = $this->filters($request);
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'       => $this->baseQuery($search, $age, $verified)
                ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
                ->paginate(20)->withQueryString(),
            'activeService'  => $tag,
            'activeSearch'   => $search,
            'activeAge'      => $age,
            'activeVerified' => $verified,
        ]));
    }
}
