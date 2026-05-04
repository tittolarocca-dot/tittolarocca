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

    private function baseQuery(?string $search = null)
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

        return $q->orderByDesc('pushed_at')->orderByDesc('created_at');
    }

    public function index(Request $request)
    {
        $search = trim($request->query('search', '')) ?: null;
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'     => $this->baseQuery($search)->paginate(20)->withQueryString(),
            'activeSearch' => $search,
        ]));
    }

    public function city(City $city, Request $request)
    {
        $search = trim($request->query('search', '')) ?: null;
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'     => $this->baseQuery($search)->where('city_id', $city->id)->paginate(20)->withQueryString(),
            'activeCity'   => $city,
            'activeSearch' => $search,
        ]));
    }

    public function category(Category $category, Request $request)
    {
        $search = trim($request->query('search', '')) ?: null;
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'       => $this->baseQuery($search)->where('category_id', $category->id)->paginate(20)->withQueryString(),
            'activeCategory' => $category,
            'activeSearch'   => $search,
        ]));
    }

    public function service(Tag $tag, Request $request)
    {
        $search = trim($request->query('search', '')) ?: null;
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'      => $this->baseQuery($search)
                ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
                ->paginate(20)->withQueryString(),
            'activeService' => $tag,
            'activeSearch'  => $search,
        ]));
    }
}
