# booklola.ch — Projektkontext für Claude Code

> **Diese Datei wird von jeder Claude-Code-Session beim Start automatisch gelesen.**
> Sie fasst zusammen, was das Projekt ist und wie man sicher daran arbeitet und deployt.
> Bei neuen Erkenntnissen bitte hier ergänzen, damit der Kontext über Sessions hinweg erhalten bleibt.

## Was ist booklola.ch

Schweizer Erotik-/Begleit-Kleinanzeigen-Plattform. Anbieter:innen ("Inserent:innen")
erstellen Profil-Inserate; Mitglieder können suchen, favorisieren, abonnieren und chatten.

## Tech-Stack

- **Backend:** Laravel 11 (PHP). Middleware-Konfiguration in `bootstrap/app.php`.
- **Frontend:** Inertia.js + Vue 3 (SPA, **kein** SSR). Seiten unter `resources/js/Pages/`.
- **Styling:** Tailwind CSS. Header/Footer-Container = `max-w-7xl mx-auto px-4`.
- **Admin:** Filament v5 (eigenes Panel, eigene Middleware — **nicht** die Web-Gruppe).
- **Build:** Vite (gehashte Chunk-Dateinamen + `manifest.json`).
- **DB:** MySQL/MariaDB.
- **Mail:** Gandi SMTP (E-Mail-Bestätigung bei Registrierung).

## Git & Deploy — WICHTIG

- **Arbeitsbranch:** `claude/upbeat-davinci-VmAXP`. Immer hierauf entwickeln & pushen.
- **Der Produktions-Server baut NICHT selbst.** Deploy = nur `git reset --hard` +
  Cache leeren. Der Server liefert die **committeten** kompilierten Assets aus
  `public/build/` aus.
- **`public/build/` steht in `.gitignore`** — trotzdem MÜSSEN die gebauten Assets
  mit committet werden. Bei jeder Frontend-Änderung also:
  ```bash
  npm run build
  git add -f public/build          # -f, weil gitignored
  git add <geänderte Quelldateien>
  git commit -m "…"
  git push -u origin claude/upbeat-davinci-VmAXP
  ```
- **Konsistenz-Regel:** Nach dem Build muss `public/build/manifest.json` auf genau die
  gehashten Dateien zeigen, die auch committet sind. Sonst → weiße Seite in Produktion.
  (Vite garantiert das, solange man den kompletten `public/build`-Ordner mit-committet
  und nicht selektiv nur einzelne Dateien staged.)
- **Deploy-Ablauf auf dem Server** (SSH; Zugangsdaten sind NICHT im Repo, liegen beim
  Betreiber):
  ```bash
  cd <laravel-root>
  git fetch origin && git reset --hard origin/claude/upbeat-davinci-VmAXP
  php artisan optimize:clear
  ```
  Danach im Browser Hard-Reload (Strg+F5).
- Vendor-Verzeichnis (`vendor/`) ist gitignored. Ein **frischer Container** braucht vor
  dem ersten Build `composer install --ignore-platform-reqs` (Build braucht u. a.
  `vendor/tightenco/ziggy`) und `npm ci`.

## Die zwei Betriebs-Modi

Beide werden über `.env`-Flags gesteuert (Config: `config/features.php`).

### 1. Launch-Modus — `LAUNCH_MODE` (aktuell: `true`)
- `true` = keine echten Zahlungen, keine Preise. Inserate **gratis**. Nur das kostenlose
  "Gratis Test"-Paket wird angezeigt (Laufzeit **30 Tage**, `LAUNCH_LISTING_DAYS`).
  Push läuft über kostenlose Launch-Credits (`LAUNCH_CREDITS_INITIAL`, Default 10).
- `false` = Normalbetrieb mit Monetarisierung (Preise/Abos/Payment/Stripe aktiv).
- Wichtige Formulierungen im Launch-Paket: "Inserat gratis verlängerbar",
  "✓ keine Zahlung erforderlich" (kein "Testmodus – kein Stripe").

### 2. Pre-Launch-Modus — `PRELAUNCH_MODE` (aktuell: `true`)
- `true` = **Gäste (nicht eingeloggt) sehen NUR** die Werbeseite `/inserieren` plus die
  Anmelde-Strecke. Der ganze Marktplatz (Startseite, Profile, Suche, Städte, Kategorien)
  ist für Gäste versteckt und wird auf `/inserieren` umgeleitet.
- **Eingeloggte Nutzer (Admin, Inserent:innen, Mitglieder) sehen alles normal.**
- `false` = Seite komplett öffentlich (Voll-Launch).
- Umgesetzt in Middleware `app/Http/Middleware/PreLaunch.php` (registriert in
  `bootstrap/app.php`, Web-Gruppe nach `SetLocale`).
- **Achtung Sonderfälle in der Middleware:** Pfade, die Gäste im Pre-Launch trotzdem
  erreichen dürfen, stehen in `GUEST_ALLOWED` (inserieren, registrieren, login, logout,
  email, impressum, datenschutz, agb, lang, media, sitemap.xml, stripe, veriff).
  **Jeder Pfad, der mit `livewire` beginnt, wird durchgelassen** — sonst schlägt der
  Filament-Admin-Login fehl (die Authentifizierung passiert erst INNERHALB des
  Livewire-Update-Requests; würde er als "Gast" abgefangen, landet der Admin auf
  `/inserieren`).

### 3. Weiteres Flag
- `VERIFF_ENABLED` (aktuell `false`): steuert, ob der Veriff-Block (Identitäts-/
  Altersprüfung) im Inserenten-Dashboard angezeigt wird. Standard aus, solange
  unentschieden.

## Werbe-Landingpage `/inserieren`

- Route `landing.advertise`, Seite `resources/js/Pages/Landing/Advertise.vue`.
- Zielgruppe: neue Inserent:innen. Kernbotschaften: Inserate sind **dauerhaft kostenlos**
  (nicht nur Launch-Phase), "Schweizweit sichtbar", Hero-Hinweis
  "Keine Kreditkarte · keine Verpflichtung · jederzeit löschbar".
- Enthält Abschnitt "So präsentierst du dich auf BookLola" mit **echten Screenshots** der
  Seite (Fotos durch Illustrationen ersetzt, Demo-Text) unter
  `public/images/demo/` (`profil.png`, `uebersicht.png`).

## Weitere umgesetzte Features (Inventar)

- **SEO (Phasen 0–4 umgesetzt):** Server-seitige Meta-/OG-/JSON-LD-Injektion ohne SSR via
  request-scoped Singleton `app/Support/SeoData.php` + View-Composer, gelesen in
  `resources/views/app.blade.php`. Dynamische Titel, Canonical über `route()`, Sitemap
  (`SitemapController`, Route `/sitemap.xml` — XML wird im Controller als String gebaut,
  **kein** Blade wegen `short_open_tag` auf dem Server). **Master-Schalter
  `SEO_INDEXABLE`** steuert `noindex`; steht auf `false` und wird beim Go-Live zuletzt
  auf `true` gesetzt. **Phase 5 (hreflang) und Phase 6 (SSR) sind bewusst NICHT
  umgesetzt** (rein schweizweite Seite).
- **Rechtstexte:** `/impressum`, `/datenschutz`, `/agb` (Entwürfe; Betreiber-Identität als
  Platzhalter; juristische Prüfung steht noch aus).
- **Chat-Anti-Spam ("Option A"):** Ein Mitglied kann pro Konversation nur **eine**
  Nachricht senden, bis die Anbieterin antwortet; max. 10 neue Konversationen/Tag.
  Logik in `app/Http/Controllers/Member/MessageController.php`.
- **Admin-Testkonten:** Über das Filament-Panel lassen sich vor-verifizierte Konten
  ohne Mailbestätigung anlegen (Rolle/Status-Dropdowns, "E-Mail bestätigt"-Toggle).
- **Mobile-UX:** Swipeable Foto-Galerie auf Listing-Cards; swipeable Profil-Lightbox
  (Pfeile auf Mobile ausgeblendet); robustes "Profil teilen" (native Share + Clipboard-
  Fallback).
- **Mitglieder-Dashboard & Chat** laufen auf Header-Breite (`max-w-7xl`).

## Go-Live-Checkliste (wenn Betreiber bereit ist)

1. Cloudflare Access (Zugriffssperre) entfernen/anpassen — falls wieder aktiv.
   (Wurde bereits deaktiviert; blockierte sonst Google & OG-Scraper.)
2. `PRELAUNCH_MODE=false` setzen → Marktplatz öffentlich.
3. `LAUNCH_MODE` nach Bedarf (bleibt vermutlich zunächst `true`).
4. `SEO_INDEXABLE=true` setzen → Seite für Google indexierbar. **Zuletzt.**
5. Nach jeder `.env`-Änderung auf dem Server: `php artisan config:clear` (bzw.
   `optimize:clear`).
6. Sitemap in der Google Search Console einreichen.

## Sicherheit / NICHT ins Repo

- Server-Zugangsdaten (Host/IP, SSH-User, Passwort) gehören **nicht** ins Repo — nur
  beim Betreiber.
- Der geheime Admin-Panel-Pfad wird bewusst obskur gehalten und **nicht** in `robots.txt`
  oder Doku genannt.
- `.env` niemals committen.

## Nützliche Fakten aus der Vergangenheit

- **Admin-Login landete auf `/inserieren`:** Ursache war die PreLaunch-Middleware, die den
  Livewire-Login-POST als Gast abfing. Fix = `livewire`-Pfade durchlassen (s. o.).
- **Verification-Foto 500 im Admin:** Datei liegt auf Disk `local`
  (`storage/app/private`). Korrekt ausliefern mit
  `Storage::disk('local')->path($profile->verification_photo)`.
- **Weiße Seite nach Deploy:** entsteht, wenn `manifest.json` auf nicht-committete/
  gitignorete Assets zeigt → immer kompletten `public/build` mit `git add -f` committen.
