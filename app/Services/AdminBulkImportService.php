<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\ImportBatch;
use App\Models\ListingOrder;
use App\Models\ListingPackage;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Erstellt im Admin-Bulk-Import pro gültiger CSV-Zeile einen Inserenten-User
 * UND sein aktives Profil – unter Wiederverwendung der bestehenden Logik.
 *
 * SICHERHEIT: Dies ist die EINZIGE Stelle, die Accounts „administrativ sofort
 * aktiv + E-Mail bestätigt" erstellt. Sie wird ausschließlich aus der Filament-
 * Admin-Seite (canAccess = Admin) aufgerufen. Es gibt KEINEN Request-Parameter,
 * der dieses Verhalten auslöst → die öffentliche Registrierung bleibt unberührt.
 */
class AdminBulkImportService
{
    /**
     * @param array<int,array> $rows Normalisierte „ready"-Zeilen aus ImportRowValidator['data'].
     * @return ImportBatch  Mit Zählern; `details` als transientes Attribut für die Anzeige.
     */
    public function import(array $rows, User $admin, ?string $filename = null): ImportBatch
    {
        $batch = ImportBatch::create([
            'admin_id' => $admin->id,
            'filename' => $filename ? Str::limit(basename($filename), 180, '') : null,
            'total'    => count($rows),
        ]);

        $created = 0;
        $skipped = 0;
        $errors  = 0;
        $details = [];

        $days          = (int) config('features.launch_listing_days', 30);
        $freePackageId = ListingPackage::where('price_chf', 0)->value('id');

        foreach ($rows as $row) {
            $email = mb_strtolower(trim((string) ($row['email'] ?? '')));

            try {
                DB::transaction(function () use ($row, $email, $batch, $days, $freePackageId, &$created) {
                    // Finaler Dubletten-Schutz: bestehende User werden NIE überschrieben.
                    if (User::where('email', $email)->exists()) {
                        throw new \RuntimeException('__skip_duplicate__');
                    }

                    // 1) USER – administrativ SOFORT aktiv + E-Mail als bestätigt.
                    //    Bewusst KEIN event(Registered) → keine Aktivierungsmail.
                    $user = User::create([
                        'name'              => $row['display_name'],
                        'email'             => $email,
                        'password'          => Hash::make(Str::password(20)), // starkes Zufallspasswort
                        'role'              => 'inserent',
                        'status'            => 'active',
                        'email_verified_at' => now(),
                        'import_batch_id'   => $batch->id,
                    ]);
                    // UserObserver::saved vergibt idempotent den Launch-Credit-Bonus
                    // (bestehende Logik, kein Doppel-Bonus, nur im Launch-Modus).

                    // 2) PROFIL – sofort aktiv, korrekte Laufzeit (bestehende Logik).
                    $profile = new Profile();
                    $profile->user_id                = $user->id;
                    $profile->display_name           = $row['display_name'];
                    $profile->description            = ($row['description'] ?? '') !== '' ? $row['description'] : null;
                    $profile->city_id                = $row['city_id'];
                    $profile->category_id            = $row['category_id'];           // primäre Rubrik (für Karte)
                    $profile->age                    = $row['age'];                   // darf null sein
                    $profile->languages              = ! empty($row['languages']) ? $row['languages'] : null;
                    $profile->subscription_price_chf = 0;                             // Launch: kein Preis
                    $profile->verification_status    = 'unverified';                 // §14: NICHT auto-verifiziert
                    $profile->status                 = 'active';
                    $profile->listing_expires_at     = now()->addDays($days);
                    if (! empty($row['phone'])) {
                        $profile->whatsapp_number = $row['phone'];                   // Mutator verschlüsselt
                    }
                    $profile->save();                                               // Slug via Model-Hook

                    $profile->categories()->sync(array_values(array_filter([$row['category_id']])));

                    // 3) Freier Launch-ListingOrder (wie reguläre Aktivierung), falls Gratis-Paket existiert.
                    if ($freePackageId) {
                        ListingOrder::create([
                            'profile_id'         => $profile->id,
                            'listing_package_id' => $freePackageId,
                            'user_id'            => $user->id,
                            'amount_chf'         => 0,
                            'currency'           => 'CHF',
                            'status'             => 'paid',
                            'paid_at'            => now(),
                            'expires_at'         => now()->addDays($days),
                        ]);
                    }

                    // 4) Audit – nur unkritische Metadaten, KEINE Passwörter.
                    AdminLog::record(
                        'bulk_import.create',
                        'user',
                        $user->id,
                        "Import #{$batch->id}: administrativ erstellt · E-Mail bestätigt · Profil #{$profile->id} aktiviert"
                    );

                    $created++;
                });
            } catch (\Throwable $e) {
                if ($e->getMessage() === '__skip_duplicate__') {
                    $skipped++;
                    $details[] = ['email' => $email, 'status' => 'skipped', 'message' => 'E-Mail existiert bereits'];
                } else {
                    $errors++;
                    // Keine sensiblen Daten loggen.
                    Log::warning('Bulk-Import: Zeile fehlgeschlagen', ['email' => $email, 'error' => $e->getMessage()]);
                    $details[] = ['email' => $email, 'status' => 'error', 'message' => 'Erstellung fehlgeschlagen'];
                }
                continue;
            }

            $details[] = ['email' => $email, 'status' => 'created'];
        }

        $batch->update([
            'created_count' => $created,
            'skipped_count' => $skipped,
            'error_count'   => $errors,
        ]);

        $batch->setAttribute('details', $details); // transient, nur für die Ergebnis-Anzeige

        return $batch;
    }
}
