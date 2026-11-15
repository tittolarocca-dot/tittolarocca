<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\User;
use App\Services\AdminBulkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBulkImportTest extends TestCase
{
    use RefreshDatabase;

    private function ctx(): array
    {
        $admin = User::create([
            'name' => 'adm', 'email' => 'adm@x.ch', 'password' => bcrypt('x'),
            'role' => 'admin', 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $city = City::create(['name' => 'Zürich', 'canton' => 'ZH', 'slug' => 'zuerich']);
        $cat  = Category::create(['name' => 'Escort', 'slug' => 'escort']);
        return [$admin, $city, $cat];
    }

    public function test_creates_active_verified_inserent_and_profile(): void
    {
        [$admin, $city, $cat] = $this->ctx();

        $rows = [
            ['display_name' => 'Laura', 'email' => 'laura@x.ch', 'city_id' => $city->id, 'category_id' => $cat->id,
             'age' => 24, 'phone' => '+41791234567', 'description' => 'Hallo Zürich', 'languages' => ['de', 'en']],
            ['display_name' => 'Mia', 'email' => 'mia@x.ch', 'city_id' => $city->id, 'category_id' => $cat->id,
             'age' => null, 'phone' => '', 'description' => '', 'languages' => []],
        ];

        $batch = app(AdminBulkImportService::class)->import($rows, $admin, 'test.csv');

        $this->assertSame(2, $batch->created_count);
        $this->assertSame(0, $batch->skipped_count);
        $this->assertSame(0, $batch->error_count);

        $laura = User::where('email', 'laura@x.ch')->first();
        $this->assertNotNull($laura);
        $this->assertSame('inserent', $laura->role);
        $this->assertSame('active', $laura->status);
        $this->assertNotNull($laura->email_verified_at);          // §7 sofort bestätigt
        $this->assertSame($batch->id, $laura->import_batch_id);   // Audit-Verknüpfung

        $p = $laura->profile;
        $this->assertNotNull($p);
        $this->assertSame('active', $p->status);                  // §12 sofort aktiv
        $this->assertTrue($p->listing_expires_at->isFuture());    // Laufzeit gesetzt
        $this->assertSame($city->id, $p->city_id);
        $this->assertSame($cat->id, (int) $p->category_id);
        $this->assertSame(24, (int) $p->age);
        $this->assertSame('+41791234567', $p->whatsapp_number);   // entschlüsselt
        $this->assertSame('unverified', $p->verification_status); // §14 NICHT auto-verifiziert
        $this->assertNull($p->identity_verification_status);
        $this->assertTrue($p->categories()->where('categories.id', $cat->id)->exists());
        $this->assertNotEmpty($p->slug);                          // Slug automatisch

        // Credits: bestehender Launch-Bonus via UserObserver (idempotent)
        if (config('features.launch_mode')) {
            $this->assertSame((int) config('features.launch_credits_initial', 10), $laura->creditsBalance());
        }

        // Mia: leeres Alter erlaubt, keine Telefonnummer
        $mia = User::where('email', 'mia@x.ch')->first()->profile;
        $this->assertNull($mia->age);
    }

    public function test_existing_email_is_skipped_never_overwritten(): void
    {
        [$admin, $city, $cat] = $this->ctx();

        $existing = User::create([
            'name' => 'Vorhanden', 'email' => 'dup@x.ch', 'password' => bcrypt('secret'),
            'role' => 'member', 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $rows = [
            ['display_name' => 'Neu', 'email' => 'dup@x.ch', 'city_id' => $city->id, 'category_id' => $cat->id,
             'age' => null, 'phone' => '', 'description' => '', 'languages' => []],
        ];

        $batch = app(AdminBulkImportService::class)->import($rows, $admin, 'd.csv');

        $this->assertSame(0, $batch->created_count);
        $this->assertSame(1, $batch->skipped_count);

        // Bestehender User unverändert (Rolle member, kein Profil, nicht importiert)
        $existing->refresh();
        $this->assertSame('member', $existing->role);
        $this->assertNull($existing->import_batch_id);
        $this->assertNull($existing->profile);
    }

    public function test_public_registration_still_requires_verification(): void
    {
        // Sonderregel gilt NUR für den Importer – öffentliche Registrierung unverändert.
        $res = $this->post('/registrieren', [
            'name' => 'Selbst', 'email' => 'selbst@x.ch', 'password' => 'secret123', 'password_confirmation' => 'secret123',
            'role' => 'inserent', 'agb' => true, 'age' => true,
        ]);

        $u = User::where('email', 'selbst@x.ch')->first();
        $this->assertNotNull($u);
        $this->assertNull($u->email_verified_at);  // NICHT automatisch bestätigt
        $this->assertNull($u->import_batch_id);
    }
}
