<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\PrivateGalleryRequest;
use App\Notifications\GalleryRequestApprovedNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryRequestController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            return redirect()->route('inserat.profile.edit')->with('error', 'Kein Profil gefunden.');
        }

        $requests = PrivateGalleryRequest::where('profile_id', $profile->id)
            ->with('user:id,name')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'declined')")
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'name'       => $r->user?->name ?? 'Mitglied',
                'status'     => $r->status,
                'created_at' => $r->created_at->format('d.m.Y H:i'),
            ]);

        return Inertia::render('Inserent/GalleryRequests', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, PrivateGalleryRequest $galleryRequest)
    {
        $this->authorizeOwner($request, $galleryRequest);

        $galleryRequest->update(['status' => 'approved', 'responded_at' => now()]);

        $galleryRequest->user?->notify(new GalleryRequestApprovedNotification($galleryRequest->profile));

        return back()->with('success', 'Zugang freigegeben.');
    }

    public function decline(Request $request, PrivateGalleryRequest $galleryRequest)
    {
        $this->authorizeOwner($request, $galleryRequest);

        $galleryRequest->update(['status' => 'declined', 'responded_at' => now()]);

        return back()->with('success', 'Anfrage abgelehnt.');
    }

    /** Nur der Profil-Eigentümer darf über seine Anfragen entscheiden. */
    private function authorizeOwner(Request $request, PrivateGalleryRequest $galleryRequest): void
    {
        abort_unless($galleryRequest->profile->user_id === $request->user()->id, 403);
    }
}
