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
}
