<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Category;
use App\Models\Profile;

class HomeController extends Controller
{
    public function index()
    {
        $profiles = Profile::with(['city', 'category', 'publicMedia'])
            ->where('status', 'active')
            ->where('listing_expires_at', '>', now())
            ->latest()
            ->paginate(20);

        return inertia('Home/Index', [
            'profiles'   => $profiles,
            'cities'     => City::where('is_active', true)->orderBy('sort_order')->get(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function city(City $city)
    {
        $profiles = Profile::with(['city', 'category', 'publicMedia'])
            ->where('city_id', $city->id)
            ->where('status', 'active')
            ->where('listing_expires_at', '>', now())
            ->latest()
            ->paginate(20);

        return inertia('Home/Index', [
            'profiles'      => $profiles,
            'cities'        => City::where('is_active', true)->orderBy('sort_order')->get(),
            'categories'    => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'activeCity'    => $city,
        ]);
    }

    public function category(Category $category)
    {
        $profiles = Profile::with(['city', 'category', 'publicMedia'])
            ->where('category_id', $category->id)
            ->where('status', 'active')
            ->where('listing_expires_at', '>', now())
            ->latest()
            ->paginate(20);

        return inertia('Home/Index', [
            'profiles'        => $profiles,
            'cities'          => City::where('is_active', true)->orderBy('sort_order')->get(),
            'categories'      => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'activeCategory'  => $category,
        ]);
    }
}
