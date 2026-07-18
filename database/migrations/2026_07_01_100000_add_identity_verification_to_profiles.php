<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Getrennte Verifizierungen:
 *  - verification_* (bestehend) = manuelle FOTO-Verifizierung (Selfie mit Schild)
 *  - identity_*     (neu)       = Identitäts- & Altersprüfung über Veriff
 *
 * Von Veriff werden NUR technische Metadaten/Status gespeichert –
 * keine Ausweisbilder, Selfies oder biometrischen Daten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $cols = [
                'identity_verification_status' => fn () => $table->string('identity_verification_status')->nullable(),
                'identity_rejected_reason'     => fn () => $table->text('identity_rejected_reason')->nullable(),
                'identity_submitted_at'        => fn () => $table->timestamp('identity_submitted_at')->nullable(),
                'identity_verified_at'         => fn () => $table->timestamp('identity_verified_at')->nullable(),
                'age_verified_at'              => fn () => $table->timestamp('age_verified_at')->nullable(),
                'veriff_session_id'            => fn () => $table->string('veriff_session_id')->nullable(),
            ];
            foreach ($cols as $name => $make) {
                if (! Schema::hasColumn('profiles', $name)) {
                    $make();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            foreach ([
                'identity_verification_status', 'identity_rejected_reason', 'identity_submitted_at',
                'identity_verified_at', 'age_verified_at', 'veriff_session_id',
            ] as $c) {
                if (Schema::hasColumn('profiles', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
