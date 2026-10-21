<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verbindliche 18+ Alters- & Identitätsprüfung (manuell, Ausweis + Video).
 *
 * BEWUSST GETRENNT von den bestehenden Prüfungen:
 *  - verification_* (Profile)  = manuelle FOTO-Verifizierung (Selfie mit Schild)
 *  - identity_*     (Profile)  = Veriff (automatisch, aktuell deaktiviert)
 *  - age_verifications (NEU)   = manuelle Ausweis-/Video-Altersprüfung
 *
 * Sensible Rohdaten (Ausweisbild, Video) werden NICHT hier, sondern als
 * Datei-Referenz auf der PRIVATEN Disk (storage/app/private) gespeichert.
 * Vor-/Nachname werden verschlüsselt abgelegt (Model-Cast 'encrypted').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('age_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique()->constrained()->cascadeOnDelete();

            // Persönliche Angaben – Datensparsamkeit: nur das Nötige.
            // first_name/last_name werden im Model verschlüsselt (encrypted cast),
            // daher 'text' statt 'string' (Chiffrat ist länger).
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->date('birth_date')->nullable(); // Grundlage der 18+-Berechnung

            // Datei-Referenzen auf der PRIVATEN Disk (niemals öffentlich).
            $table->string('document_path')->nullable();
            $table->string('document_mime')->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_mime')->nullable();

            // Status-Workflow: not_submitted → pending → verified | rejected
            $table->string('status')->default('not_submitted');
            $table->text('rejection_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            // Aufbewahrung / sichere Löschung (konfigurierbar, kein erfundenes Default).
            $table->timestamp('purge_after')->nullable();   // ab wann Dateien gelöscht werden dürfen
            $table->timestamp('files_purged_at')->nullable(); // wann die Dateien gelöscht wurden

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('age_verifications');
    }
};
