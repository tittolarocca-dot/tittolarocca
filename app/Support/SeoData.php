<?php

namespace App\Support;

/**
 * Request-weiter SEO-Container. Controller setzen pro Seite Titel, Description
 * und ob die Seite indexierbar sein darf; das Root-Blade (app.blade.php)
 * rendert daraus die <head>-Tags serverseitig – also auch für Crawler/Social,
 * die kein JavaScript ausführen.
 *
 * Titel-Konvention: gespeichert wird der Seitenteil OHNE Marke ($pageTitle).
 * fullTitle() hängt " – {site_name}" an – identisch zum Inertia-title-Callback
 * in app.js, damit Server- und Client-Titel exakt übereinstimmen.
 */
class SeoData
{
    public string $pageTitle = '';
    public ?string $description = null;
    public ?string $canonicalOverride = null;
    public bool $allowIndex = false;

    public string $ogType = 'website';
    public ?string $ogImageOverride = null;
    /** @var array<int,array<string,mixed>> */
    public array $jsonLd = [];

    public function __construct()
    {
        $this->description = config('seo.default_description');
    }

    /** Canonical-URL – standardmäßig die aktuelle Request-URL (ohne Query). */
    public function canonical(): string
    {
        return $this->canonicalOverride ?? url()->current();
    }

    /** Seitenteil des Titels OHNE Marke – für den Client-Head (Inertia). */
    public function forPage(string $pageTitle, ?string $description = null, bool $allowIndex = true): self
    {
        $this->pageTitle = $pageTitle;

        if ($description !== null && $description !== '') {
            $this->description = $description;
        }

        $this->allowIndex = $allowIndex;

        return $this;
    }

    public function setCanonical(string $url): self
    {
        $this->canonicalOverride = $url;
        return $this;
    }

    /** Vollständiger Titel MIT Marke – identisch zum Inertia-Suffix in app.js. */
    public function fullTitle(): string
    {
        $site = config('seo.site_name', config('app.name'));
        return $this->pageTitle === '' ? $site : "{$this->pageTitle} – {$site}";
    }

    /**
     * Effektiver robots-Wert. Solange der Master-Schalter (config seo.indexable)
     * aus ist, bleibt ALLES auf noindex – unabhängig von allowIndex.
     */
    public function robots(): string
    {
        return (config('seo.indexable') && $this->allowIndex)
            ? 'index, follow'
            : 'noindex, nofollow';
    }

    // ── Open Graph / Social ──────────────────────────────────────────────────

    public function setOg(string $type, ?string $image = null): self
    {
        $this->ogType = $type;
        if ($image) {
            $this->ogImageOverride = $image;
        }
        return $this;
    }

    /** OG-/Twitter-Bild – Fallback auf das Booklola-Logo. */
    public function ogImage(): string
    {
        return $this->ogImageOverride ?? asset('images/logo-booklola.png');
    }

    /** og:locale im Format language_TERRITORY (Schweiz-orientiert). */
    public function ogLocale(): string
    {
        return [
            'de' => 'de_CH', 'fr' => 'fr_CH', 'it' => 'it_CH',
            'en' => 'en_US', 'es' => 'es_ES', 'hu' => 'hu_HU', 'ro' => 'ro_RO',
        ][app()->getLocale()] ?? 'de_CH';
    }

    // ── Strukturierte Daten (JSON-LD) ────────────────────────────────────────

    public function addJsonLd(array $data): self
    {
        $this->jsonLd[] = $data;
        return $this;
    }

    /**
     * Fügt eine BreadcrumbList hinzu. $items = Liste von [name, url] in Reihenfolge.
     */
    public function addBreadcrumb(array $items): self
    {
        $list = [];
        $position = 1;
        foreach ($items as [$name, $url]) {
            $list[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $name,
                'item'     => $url,
            ];
        }

        return $this->addJsonLd([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $list,
        ]);
    }
}
