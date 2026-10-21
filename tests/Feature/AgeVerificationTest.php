<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\AgeVerification;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgeVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeInserent(string $slug): User
    {
        $city = City::firstOrCreate(['slug' => 'zuerich'], ['name' => 'Zürich', 'canton' => 'ZH']);
        $cat  = Category::firstOrCreate(['slug' => 'escort'], ['name' => 'Escort']);
        $u = User::create([
            'name' => $slug, 'email' => $slug.'@x.ch', 'password' => bcrypt('x'),
            'role' => 'inserent', 'email_verified_at' => now(),
        ]);
        Profile::create([
            'user_id' => $u->id, 'display_name' => ucfirst($slug), 'status' => 'active',
            'city_id' => $city->id, 'category_id' => $cat->id, 'subscription_price_chf' => 0,
            'listing_expires_at' => now()->addDays(30),
        ]);
        return $u->fresh();
    }

    private function uploadBoth(User $u): AgeVerification
    {
        $this->actingAs($u)->post(route('inserat.age.document.upload'), [
            'document' => UploadedFile::fake()->image('ausweis.jpg', 800, 500),
        ])->assertSessionHasNoErrors();

        $this->actingAs($u)->post(route('inserat.age.video.upload'), [
            'video' => UploadedFile::fake()->create('clip.mp4', 2000, 'video/mp4'),
        ])->assertSessionHasNoErrors();

        // Frische Query – nicht die ggf. gecachte Relation auf $u verwenden.
        return AgeVerification::whereHas('profile', fn ($q) => $q->where('user_id', $u->id))->firstOrFail();
    }

    public function test_full_submit_flow_sets_pending_and_stores_files_privately(): void
    {
        Storage::fake('local');
        $u = $this->makeInserent('amira');

        $av = $this->uploadBoth($u);
        $this->assertTrue($av->hasDocument());
        $this->assertTrue($av->hasVideo());
        Storage::disk('local')->assertExists($av->document_path);
        Storage::disk('local')->assertExists($av->video_path);
        $this->assertStringStartsWith('age-verifications/', $av->document_path);

        $this->actingAs($u)->post(route('inserat.age.submit'), [
            'first_name' => 'Amira', 'last_name' => 'Keller', 'birth_date' => '1995-05-05',
        ])->assertSessionHasNoErrors();

        $av->refresh();
        $this->assertSame('pending', $av->status);
        $this->assertNotNull($av->submitted_at);
        $this->assertSame('Amira', $av->first_name);   // entschlüsselt gelesen
        $this->assertDatabaseHas('admin_logs', ['action' => 'age_verification.submitted', 'target_id' => $av->id]);
    }

    public function test_under_18_is_rejected_server_side(): void
    {
        Storage::fake('local');
        $u = $this->makeInserent('mina');
        $this->uploadBoth($u);

        $this->actingAs($u)->post(route('inserat.age.submit'), [
            'first_name' => 'Mina', 'last_name' => 'Jung', 'birth_date' => now()->subYears(17)->format('Y-m-d'),
        ])->assertSessionHasErrors('birth_date');

        $this->assertSame('not_submitted', AgeVerification::whereHas('profile', fn ($q) => $q->where('user_id', $u->id))->firstOrFail()->status);
    }

    public function test_foreign_user_cannot_access_document_or_video_403(): void
    {
        Storage::fake('local');
        $owner = $this->makeInserent('owner');
        $av = $this->uploadBoth($owner);

        $intruder = $this->makeInserent('intruder');

        // Fremder Inserent → 403 auf beide Dateien
        $this->actingAs($intruder)->get(route('age-verification.document', $av))->assertStatus(403);
        $this->actingAs($intruder)->get(route('age-verification.video', $av))->assertStatus(403);

        // Gast → Redirect zum Login (nicht erreichbar)
        auth()->logout();
        $this->get(route('age-verification.document', $av))->assertRedirect();

        // Eigentümer:in → 200
        $this->actingAs($owner)->get(route('age-verification.document', $av))->assertOk();

        // Admin → 200
        $admin = User::create(['name' => 'adm', 'email' => 'adm@x.ch', 'password' => bcrypt('x'), 'role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get(route('age-verification.video', $av))->assertOk();
    }

    public function test_uploads_blocked_while_pending(): void
    {
        Storage::fake('local');
        $u = $this->makeInserent('lea');
        $av = $this->uploadBoth($u);
        $av->update(['status' => 'pending']);

        $this->actingAs($u)->post(route('inserat.age.document.upload'), [
            'document' => UploadedFile::fake()->image('neu.jpg'),
        ])->assertSessionHas('error');

        $this->actingAs($u)->post(route('inserat.age.submit'), [
            'first_name' => 'Lea', 'last_name' => 'X', 'birth_date' => '1990-01-01',
        ])->assertSessionHas('error');
    }

    public function test_bad_video_mime_is_rejected(): void
    {
        Storage::fake('local');
        $u = $this->makeInserent('eva');
        // Als .mp4 getarnte Textdatei → MIME ist text/plain, nicht video/*
        $fake = UploadedFile::fake()->createWithContent('clip.mp4', '<?php echo 1; ?>');
        $this->actingAs($u)->post(route('inserat.age.video.upload'), ['video' => $fake])
            ->assertSessionHasErrors('video');
    }

    public function test_purge_command_deletes_files_but_keeps_status(): void
    {
        Storage::fake('local');
        $u = $this->makeInserent('nora');
        $av = $this->uploadBoth($u);
        $av->update([
            'status' => 'verified',
            'purge_after' => now()->subDay(),
        ]);
        $docPath = $av->document_path;

        $this->artisan('age-verifications:purge')->assertSuccessful();

        $av->refresh();
        Storage::disk('local')->assertMissing($docPath);
        $this->assertNull($av->document_path);
        $this->assertNull($av->video_path);
        $this->assertSame('verified', $av->status);        // Status bleibt
        $this->assertNotNull($av->files_purged_at);
    }
}
