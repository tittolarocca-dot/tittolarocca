<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Automatische Spracherkennung + Vorrang der manuellen Wahl.
 * Prüft die SetLocale-Middleware am echten '/'-Request (app()->getLocale()).
 */
class LocaleDetectionTest extends TestCase
{
    use RefreshDatabase;

    private function assertLocale(string $expected): void
    {
        $this->assertSame($expected, app()->getLocale());
    }

    public function test_browser_de_ch(): void
    {
        $this->withHeaders(['Accept-Language' => 'de-CH,de;q=0.9'])->get('/')->assertOk();
        $this->assertLocale('de');
    }

    public function test_browser_fr_ch(): void
    {
        $this->withHeaders(['Accept-Language' => 'fr-CH,fr;q=0.9,en;q=0.5'])->get('/')->assertOk();
        $this->assertLocale('fr');
    }

    public function test_browser_it_ch(): void
    {
        $this->withHeaders(['Accept-Language' => 'it-CH,it;q=0.9'])->get('/')->assertOk();
        $this->assertLocale('it');
    }

    public function test_browser_multi_prefers_first_supported(): void
    {
        $this->withHeaders(['Accept-Language' => 'fr-CH,de-CH,en'])->get('/')->assertOk();
        $this->assertLocale('fr');
    }

    public function test_region_fallback_when_no_accept_language(): void
    {
        // Keine Browsersprache → CH-Region Genf → Französisch
        $this->withHeaders(['CF-Region-Code' => 'GE', 'CF-IPCountry' => 'CH'])->get('/')->assertOk();
        $this->assertLocale('fr');
    }

    public function test_googlebot_without_accept_language_gets_default(): void
    {
        // Kein Accept-Language, US-IP → Standard Deutsch (konsistent, kein Cloaking)
        $this->withHeaders(['CF-IPCountry' => 'US'])->get('/')->assertOk();
        $this->assertLocale('de');
    }

    public function test_cookie_manual_choice_beats_detection(): void
    {
        // Browser sagt Deutsch, aber manuelle Wahl (Cookie) = Französisch → bleibt fr
        $this->withUnencryptedCookie('locale', 'fr')
            ->withHeaders(['Accept-Language' => 'de-CH,de'])
            ->get('/')->assertOk();
        $this->assertLocale('fr');
    }

    public function test_logged_in_user_locale_beats_detection(): void
    {
        $user = User::create([
            'name' => 'u', 'email' => 'u@x.ch', 'password' => bcrypt('x'),
            'role' => 'member', 'status' => 'active', 'email_verified_at' => now(),
            'locale' => 'it',
        ]);

        $this->actingAs($user)
            ->withHeaders(['Accept-Language' => 'de-CH,de'])
            ->get('/')->assertOk();
        $this->assertLocale('it');
    }

    public function test_switch_persists_cookie_session_and_user(): void
    {
        $user = User::create([
            'name' => 'u2', 'email' => 'u2@x.ch', 'password' => bcrypt('x'),
            'role' => 'member', 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->get('/lang/fr')->assertRedirect();

        $this->assertSame('fr', $user->fresh()->locale);        // User-Spalte gesetzt
        $this->assertSame('fr', session('locale'));             // Session gesetzt
        // Cookie wird in der Antwort gesetzt (1 Jahr)
    }
}
