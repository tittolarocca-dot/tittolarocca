<?php
namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\ProfileVisit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $profile = $user->profile()->with(['city', 'category', 'publicMedia', 'privateMedia'])->first();

        $isFreeProfile = $profile
            ? $profile->listingOrders()->where('amount_chf', '>', 0)->doesntExist()
            : false;

        $visitorsCount = $profile
            ? ProfileVisit::where('profile_owner_user_id', $user->id)->count()
            : 0;

        $unreadMessages = Message::where('to_user_id', $user->id)->whereNull('read_at')->count();

        $galleryRequests = $profile
            ? \App\Models\PrivateGalleryRequest::where('profile_id', $profile->id)->where('status', 'pending')->count()
            : 0;

        // Profil-Vollständigkeit + „Nächste Schritte"-Tipps (gleiche Checkliste treibt
        // Ring und Tippliste). href '#verifizierung' scrollt zur Sektion auf der Seite.
        $tips = [];
        $completeness = 0;
        if ($profile) {
            $checks = [
                ['key' => 'photos',      'label' => 'Fotos hochladen',          'desc' => 'Mindestens ein öffentliches Foto', 'done' => $profile->media()->where('type', 'image')->where('visibility', 'public')->exists(), 'href' => route('inserat.media.index')],
                ['key' => 'description', 'label' => 'Beschreibung schreiben',    'desc' => 'Erzähl etwas über dich',           'done' => filled($profile->description),                                                       'href' => route('inserat.profile.edit')],
                ['key' => 'verified',    'label' => 'Foto verifizieren',         'desc' => 'Verifiziert-Badge schafft Vertrauen', 'done' => $profile->verification_status === 'approved',                                      'href' => '#verifizierung'],
                ['key' => 'post',        'label' => 'Ersten Feed-Beitrag posten', 'desc' => 'Erscheine im Community-Feed',       'done' => $profile->posts()->exists(),                                                         'href' => route('inserat.posts.index')],
                ['key' => 'video',       'label' => 'Video hinzufügen',          'desc' => 'Profile mit Video werden öfter besucht', 'done' => $profile->media()->where('type', 'video')->exists(),                              'href' => route('inserat.media.index')],
            ];
            $done = count(array_filter($checks, fn ($c) => $c['done']));
            $completeness = (int) round($done / count($checks) * 100);
            $tips = $checks;
        }

        return inertia('Inserent/Dashboard', [
            'completeness'    => $completeness,
            'tips'            => $tips,
            'profile'         => $profile,
            'launchMode'      => (bool) config('features.launch_mode'),
            'credits'         => $user->creditsBalance(),
            'pushCost'        => (int) config('features.push_credit_cost', 1),
            'unreadMessages'  => $unreadMessages,
            'galleryRequests' => $galleryRequests,
            'countries'       => config('countries', []),
            'stats' => $profile ? [
                'views'         => $profile->total_views,
                'subscribers'   => $profile->total_subscribers,
                'mediaCount'    => $profile->media()->count(),
                'visitorsCount' => $visitorsCount,
                'isActive'      => $profile->isActive(),
                'expiresAt'     => $profile->listing_expires_at?->format('d.m.Y'),
                'createdAt'     => $profile->created_at->format('d.m.Y'),
                'pushedAt'      => $profile->pushed_at?->format('d.m.Y H:i'),
                'isFreeProfile' => $isFreeProfile,
            ] : null,
        ]);
    }
}
