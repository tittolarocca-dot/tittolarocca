<?php
namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class NewImagesController extends Controller
{
    public function index(Request $request)
    {
        // Öffentliche UND private Bilder anzeigen – private jedoch nur als
        // gesperrte, serverseitig weichgezeichnete Vorschau (nie das Original).
        $query = Media::with(['profile:id,slug,display_name'])
            ->where('type', 'image')
            ->where('status', 'approved')
            ->whereHas('profile', fn ($q) => $q
                ->where('status', 'active')
                ->where('listing_expires_at', '>', now())
            );

        $images = $query->orderByDesc('created_at')->paginate(48)->withQueryString()
            ->through(function ($m) {
                $private = $m->visibility === 'private';
                return [
                    'id'          => $m->id,
                    'private'     => $private,
                    'src'         => $private ? null : $m->src, // Bild-Varianten
                    'preview_url' => $private ? route('media.preview', $m->id) : null,
                    'profile'     => [
                        'slug'         => $m->profile->slug,
                        'display_name' => $m->profile->display_name,
                    ],
                ];
            });

        return inertia('NewImages/Index', [
            'images' => $images,
        ]);
    }
}
