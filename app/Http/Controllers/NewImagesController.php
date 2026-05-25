<?php
namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class NewImagesController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with(['profile:id,slug,display_name'])
            ->where('type', 'image')
            ->where('status', 'approved')
            ->whereHas('profile', fn ($q) => $q
                ->where('status', 'active')
                ->where('listing_expires_at', '>', now())
            );

        if (! auth()->check()) {
            $query->where('visibility', 'public');
        }

        $images = $query->orderByDesc('created_at')->paginate(48)->withQueryString();

        return inertia('NewImages/Index', [
            'images' => $images,
        ]);
    }
}
