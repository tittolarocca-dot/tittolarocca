<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Category;
use App\Models\Profile;
use App\Models\Tag;

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

    private function baseQuery()
    {
        return Profile::with(['city', 'category', 'publicMedia',
            'listingOrders' => fn ($q) => $q
                ->where('status', 'paid')
                ->where('expires_at', '>', now())
                ->orderByDesc('paid_at'),
        ])
            ->where('status', 'active')
            ->where('listing_expires_at', '>', now())
            ->orderByDesc('pushed_at')
            ->orderByDesc('created_at');
    }

    public function index()
    {
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles' => $this->baseQuery()->paginate(20),
        ]));
    }

    public function city(City $city)
    {
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'   => $this->baseQuery()->where('city_id', $city->id)->paginate(20),
            'activeCity' => $city,
        ]));
    }

    public function category(Category $category)
    {
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'       => $this->baseQuery()->where('category_id', $category->id)->paginate(20),
            'activeCategory' => $category,
        ]));
    }

    public function service(Tag $tag)
    {
        return inertia('Home/Index', array_merge($this->sharedData(), [
            'profiles'      => $this->baseQuery()
                ->whereHas('tags', fn($q) => $q->where('tags.id', $tag->id))
                ->paginate(20),
            'activeService' => $tag,
        ]));
    }
}
