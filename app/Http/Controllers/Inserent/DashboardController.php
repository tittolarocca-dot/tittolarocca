<?php
namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile()->with(['city', 'category', 'publicMedia', 'privateMedia'])->first();

        return inertia('Inserent/Dashboard', [
            'profile'      => $profile,
            'stats' => $profile ? [
                'views'        => $profile->total_views,
                'subscribers'  => $profile->total_subscribers,
                'mediaCount'   => $profile->media()->count(),
                'isActive'     => $profile->isActive(),
                'expiresAt'    => $profile->listing_expires_at?->format('d.m.Y'),
                'createdAt'    => $profile->created_at->format('d.m.Y'),
                'pushedAt'     => $profile->pushed_at?->format('d.m.Y H:i'),
            ] : null,
        ]);
    }
}
