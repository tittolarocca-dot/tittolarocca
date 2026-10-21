<?php

namespace App\Http\Controllers\Inserent;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\AgeVerification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Verbindliche 18+ Alters- & Identitätsprüfung (Ausweis + Identifikationsvideo).
 *
 * Sicherheit (zusammengefasst):
 *  - Dateien liegen auf der PRIVATEN Disk 'local' (storage/app/private), nie public.
 *  - Ausgeliefert werden sie NUR über document()/video() mit Autorisierung
 *    (Eigentümer:in ODER Admin/Moderator) und Cache-Control: no-store.
 *  - MIME wird serverseitig aus dem Dateiinhalt geprüft (nicht nur Endung).
 *  - Dateinamen werden serverseitig neu generiert (UUID); Originalpfade bleiben verborgen.
 *  - EXIF/Metadaten werden bei JPEG/PNG/WebP durch Re-Encoding entfernt.
 *  - Keine sensiblen Inhalte in Logs.
 */
class AgeVerificationController extends Controller
{
    /** Erlaubte Ausweis-Bildendungen (PDF zusätzlich, da privat gespeichert & nie inline gerendert). */
    private const DOC_EXTS   = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff', 'pdf'];
    /** Erlaubte Videoendungen. */
    private const VIDEO_EXTS = ['mp4', 'mov', 'avi', 'mkv', 'wmv', '3gp', 'flv'];

    // ─────────────────────────────────────────────────────────────────────────
    //  Inserenten-Seite
    // ─────────────────────────────────────────────────────────────────────────
    public function page(Request $request)
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return redirect()->route('inserat.profile.edit')->with('error', 'Bitte erstelle zuerst dein Profil.');
        }

        $av = $profile->ageVerification()->first();

        return inertia('Inserent/AgeVerification', [
            'av' => $av ? [
                'status'           => $av->status,
                'rejection_reason' => $av->rejection_reason,
                'first_name'       => $av->first_name,   // nur für Eigentümer:in (eigene Daten)
                'last_name'        => $av->last_name,
                'birth_date'       => optional($av->birth_date)->format('Y-m-d'),
                'has_document'     => $av->hasDocument(),
                'has_video'        => $av->hasVideo(),
                'document_url'     => $av->hasDocument() ? route('age-verification.document', $av) : null,
                'video_url'        => $av->hasVideo()    ? route('age-verification.video', $av)    : null,
                'submitted_at'     => optional($av->submitted_at)->format('d.m.Y H:i'),
            ] : [
                'status'       => AgeVerification::STATUS_NOT_SUBMITTED,
                'has_document' => false,
                'has_video'    => false,
            ],
            'limits' => [
                'document_mb' => (int) config('features.age_verification_max_document_mb', 15),
                'video_mb'    => (int) config('features.age_verification_max_video_mb', 80),
                'doc_exts'    => self::DOC_EXTS,
                'video_exts'  => self::VIDEO_EXTS,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Uploads (einzeln, vor der endgültigen Einreichung ersetzbar)
    // ─────────────────────────────────────────────────────────────────────────
    public function uploadDocument(Request $request)
    {
        $av = $this->draftOrFail($request);
        if ($av instanceof \Illuminate\Http\RedirectResponse) {
            return $av;
        }

        $maxKb = (int) config('features.age_verification_max_document_mb', 15) * 1024;
        $file  = $this->validatedFile($request, 'document', self::DOC_EXTS, 'document', $maxKb);

        // Alte Datei entfernen (Ersetzen vor Einreichung).
        $this->deleteFile($av->document_path);

        [$path, $mime] = $this->storeSecure($file, $av->profile_id, 'doc');
        $av->update(['document_path' => $path, 'document_mime' => $mime]);

        return back()->with('success', 'Ausweisdokument hochgeladen.');
    }

    public function uploadVideo(Request $request)
    {
        $av = $this->draftOrFail($request);
        if ($av instanceof \Illuminate\Http\RedirectResponse) {
            return $av;
        }

        $maxKb = (int) config('features.age_verification_max_video_mb', 80) * 1024;
        $file  = $this->validatedFile($request, 'video', self::VIDEO_EXTS, 'video', $maxKb);

        $this->deleteFile($av->video_path);

        [$path, $mime] = $this->storeSecure($file, $av->profile_id, 'vid');
        $av->update(['video_path' => $path, 'video_mime' => $mime]);

        return back()->with('success', 'Identifikationsvideo hochgeladen.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Endgültige Einreichung
    // ─────────────────────────────────────────────────────────────────────────
    public function submit(Request $request)
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        $av = $profile->ageVerification()->first();
        if (! $av) {
            return back()->with('error', 'Bitte lade zuerst deine Dokumente hoch.');
        }

        // Keine erneute Einreichung während laufender Prüfung.
        if ($av->isPending()) {
            return back()->with('error', 'Deine Prüfung läuft bereits. Bitte warte, bis sie abgeschlossen ist.');
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
        ], [], [
            'first_name' => 'Vorname',
            'last_name'  => 'Nachname',
            'birth_date' => 'Geburtsdatum',
        ]);

        // 18+ serverseitig erzwingen – niemals umgehbar.
        $age = Carbon::parse($data['birth_date'])->age;
        if ($age < 18) {
            throw ValidationException::withMessages([
                'birth_date' => 'Du musst mindestens 18 Jahre alt sein, um ein Inserat zu verifizieren.',
            ]);
        }

        // Beide Nachweise müssen vorhanden sein.
        if (! $av->hasDocument()) {
            throw ValidationException::withMessages(['document' => 'Bitte lade zuerst dein Identitätsdokument hoch.']);
        }
        if (! $av->hasVideo()) {
            throw ValidationException::withMessages(['video' => 'Bitte lade zuerst dein Identifikationsvideo hoch.']);
        }

        $resubmit = $av->isRejected();

        $av->update([
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'birth_date'       => $data['birth_date'],
            'status'           => AgeVerification::STATUS_PENDING,
            'rejection_reason' => null,
            'submitted_at'     => now(),
            'reviewed_at'      => null,
            'reviewed_by'      => null,
        ]);

        // Audit: nur unkritische Metadaten, KEINE Namen/Dateien.
        AdminLog::record(
            $resubmit ? 'age_verification.resubmitted' : 'age_verification.submitted',
            'age_verification',
            $av->id
        );

        return back()->with('success', 'Vielen Dank. Deine Angaben wurden übermittelt und werden geprüft.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Sichere Datei-Auslieferung (Eigentümer:in ODER Admin/Moderator)
    // ─────────────────────────────────────────────────────────────────────────
    public function document(Request $request, AgeVerification $ageVerification)
    {
        $this->authorizeFileAccess($request, $ageVerification);
        return $this->streamPrivate($ageVerification->document_path, $ageVerification->document_mime);
    }

    public function video(Request $request, AgeVerification $ageVerification)
    {
        $this->authorizeFileAccess($request, $ageVerification);
        return $this->streamPrivate($ageVerification->video_path, $ageVerification->video_mime);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Intern
    // ─────────────────────────────────────────────────────────────────────────

    /** Draft-Row holen/erstellen – blockt, falls Prüfung gerade läuft. */
    private function draftOrFail(Request $request)
    {
        $profile = $request->user()->profile;
        if (! $profile) {
            return back()->with('error', 'Kein Profil gefunden.');
        }

        $av = $profile->ageVerification()->first(); // frische Query (keine gecachte Relation)
        if ($av && $av->isPending()) {
            return back()->with('error', 'Deine Prüfung läuft bereits. Uploads sind währenddessen gesperrt.');
        }
        if (! $av) {
            $av = $profile->ageVerification()->create(['status' => AgeVerification::STATUS_NOT_SUBMITTED]);
        }
        return $av;
    }

    /**
     * Validiert eine hochgeladene Datei: Endung aus Allowlist UND echter
     * Inhalts-MIME passend zur Familie (image/* bzw. application/pdf / video/*).
     * Wehrt getarnte Dateien (z. B. shell.php.mp4) ab.
     */
    private function validatedFile(Request $request, string $field, array $exts, string $family, int $maxKb)
    {
        $request->validate([
            $field => ['required', 'file', 'max:' . $maxKb, 'extensions:' . implode(',', $exts)],
        ], [
            $field . '.required'   => 'Bitte wähle eine Datei aus.',
            $field . '.max'        => 'Die Datei ist zu gross.',
            $field . '.extensions' => 'Dieses Dateiformat ist nicht erlaubt.',
        ]);

        $file = $request->file($field);
        $mime = (string) $file->getMimeType(); // liest den Inhalt, nicht die Endung

        $ok = match ($family) {
            'document' => str_starts_with($mime, 'image/') || $mime === 'application/pdf',
            'video'    => str_starts_with($mime, 'video/'),
            default    => false,
        };

        if (! $ok) {
            throw ValidationException::withMessages([
                $field => 'Der Dateiinhalt entspricht nicht dem erwarteten Format.',
            ]);
        }

        return $file;
    }

    /**
     * Speichert die Datei sicher auf der privaten Disk mit neuem UUID-Namen.
     * Bei JPEG/PNG/WebP werden EXIF/Metadaten durch Re-Encoding entfernt.
     *
     * @return array{0:string,1:string} [relativer Pfad, MIME]
     */
    private function storeSecure(\Illuminate\Http\UploadedFile $file, int $profileId, string $prefix): array
    {
        $mime = (string) $file->getMimeType();
        $ext  = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $name = $prefix . '_' . Str::uuid()->toString() . '.' . $ext;
        $dir  = 'age-verifications/' . $profileId;
        $rel  = $dir . '/' . $name;

        // EXIF-Strip nur für GD-fähige Rasterbilder.
        $stripped = in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)
            ? $this->reencodeWithoutExif($file->getRealPath(), $mime)
            : null;

        if ($stripped !== null) {
            Storage::disk('local')->put($rel, $stripped);
        } else {
            // Unverändert ablegen (Video/PDF/GIF/BMP/TIFF).
            $stream = fopen($file->getRealPath(), 'rb');
            Storage::disk('local')->put($rel, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return [$rel, $mime];
    }

    /** Lädt ein Bild via GD und gibt es ohne Metadaten als Binärstring zurück (oder null bei Fehler). */
    private function reencodeWithoutExif(string $absPath, string $mime): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }
        try {
            $img = @imagecreatefromstring((string) file_get_contents($absPath));
            if ($img === false) {
                return null;
            }
            // Transparenz für PNG/WebP erhalten.
            imagealphablending($img, false);
            imagesavealpha($img, true);

            ob_start();
            match ($mime) {
                'image/jpeg' => imagejpeg($img, null, 92),
                'image/png'  => imagepng($img, null, 6),
                'image/webp' => imagewebp($img, null, 90),
                default      => null,
            };
            $data = ob_get_clean();
            imagedestroy($img);

            return $data !== false && $data !== '' ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Autorisierung: Eigentümer:in des Profils ODER Admin/Moderator. Sonst 403. */
    private function authorizeFileAccess(Request $request, AgeVerification $av): void
    {
        $user = $request->user();
        $isOwner = $user && $av->profile && (int) $av->profile->user_id === (int) $user->id;
        $isStaff = $user && in_array($user->role, ['admin', 'moderator'], true);

        abort_unless($isOwner || $isStaff, 403);
    }

    /** Streamt eine private Datei mit no-store-Header; 404 falls weg. */
    private function streamPrivate(?string $path, ?string $mime)
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type'  => $mime ?: 'application/octet-stream',
            'Cache-Control' => 'no-store, private, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Datei still und leise von der privaten Disk entfernen. */
    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }
}
