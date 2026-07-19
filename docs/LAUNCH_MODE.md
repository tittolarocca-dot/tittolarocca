# Launch-Modus & Credit-System

Diese Dokumentation beschreibt den Launch-Modus und das interne Credit-System.
**Es wurde keine bestehende Funktion gelöscht** – Payment/Abo/Push/Medien-Logik
bleibt erhalten und wird bei `LAUNCH_MODE=false` wieder aktiv.

---

## 1. LAUNCH_MODE ein-/ausschalten

In der `.env`:

```
LAUNCH_MODE=true      # Launch-Modus (keine Zahlungen, Credits, Gratis-Inserate)
LAUNCH_MODE=false     # Normalbetrieb (Preise/Abos/Push-Käufe/Payment wieder aktiv)
```

Nach jeder Änderung:

```bash
php artisan config:clear
```

Prüfen:

```bash
php artisan tinker --execute="echo config('features.launch_mode') ? 'AN' : 'AUS';"
```

### Beträge anpassen (`.env`)

```
LAUNCH_CREDITS_INITIAL=20   # Launch-Bonus pro neuer Inserentin (Standard 10)
PUSH_CREDIT_COST=1          # Credits pro Push
PRIVATE_GALLERY_BONUS=3     # Einmal-Bonus für Galerie-Freigabe
LAUNCH_LISTING_DAYS=14      # Gratis-Laufzeit eines Inserats im Launch
```

Danach `php artisan config:clear`.

---

## 2. Verhalten

### LAUNCH_MODE=true
- Keine echten Zahlungen; kein Stripe/Payrexx/Worldline etc. wird gestartet.
- Keine Preise / keine Payment-Buttons für Besucher.
- Inserate sind gratis (14 Tage aktiv).
- Push kostet **1 Launch-Credit** (kein CHF).
- Private Galerie bleibt geschützt; nur sichtbar, wenn die Inserentin sie
  freigibt – und dann **nur für eingeloggte Mitglieder**, nie für Gäste.

### LAUNCH_MODE=false
- Bestehende Preise/Abos/Push-Käufe/PPV werden wieder aktiv.
- Push läuft wieder über Stripe (CHF), private Galerie über Abo.

---

## 3. Credit-System

Guthaben hängt am **Benutzerkonto** (nicht am Profil), damit ein Inserent
mehrere Profile haben kann.

- `credit_balances` – Saldo je User (balance, total_granted, total_spent,
  total_purchased, total_bonus).
- `credit_transactions` – jede Bewegung wird protokolliert
  (amount, type, description, reference, created_by_admin_id, metadata).

Transaktionstypen: `initial_launch_bonus`, `manual_admin_credit`,
`manual_admin_debit`, `profile_push`, `bonus_private_gallery_launch`,
`refund`, `purchase` (letzterer erst mit echtem Kauf später).

Zentrale Logik: `App\Services\CreditService` (transaktionssicher, keine
negativen Salden ausser Admin erzwingt, idempotente Einmal-Gutschriften).

### Launch-Credits an bestehende Inserentinnen vergeben

```bash
php artisan credits:grant-launch
```

Bucht jeder Inserentin **einmalig** den Launch-Bonus (überspringt Konten, die
ihn schon haben). Neue Inserentinnen erhalten ihn automatisch, sobald sie die
Rolle `inserent` bekommen (Profil-Erstellung oder Admin-Rollenwechsel).

### Bestehende Konten von 10 auf z. B. 20 aufstocken

Der Bonus wird nur einmal gebucht. Für einen manuellen Nachschlag:
Adminpanel → **Benutzer** → Zeile → **Credits +** (mit Notiz), oder per Tinker:

```php
$svc = app(App\Services\CreditService::class);
App\Models\User::where('role','inserent')->each(fn($u) =>
    $svc->grant($u, 10, 'manual_admin_credit', ['description' => 'Aufstockung 10→20'])
);
```

---

## 4. Adminpanel

- **Verwaltung → Benutzer**: Spalten Credits/Erhalten/Verbraucht; Zeilen- und
  Edit-Aktionen „Credits +/−" (mit Pflicht-Notiz, optional negativ erzwingen).
- **Finanzen → Credits**: Salden je Benutzer + Aktionen.
- **Finanzen → Credit-Transaktionen**: vollständiges Protokoll, Filter nach Typ.
- **Finanzen → Zahlungen**: unverändert; im Launch-Modus Hinweis
  „Im Launch-Modus sind echte Zahlungen deaktiviert."
- **Moderation → Profile**: Spalten „Privat / Launch-Galerie / Pushs / Letzter
  Push"; Edit-Formular hat den Toggle „Private Galerie im Launch freigeben".

---

## 5. Private Galerie im Launch

Pro Profil steuerbar über den Toggle **„Private Galerie während Launch
kostenlos für registrierte Mitglieder freigeben"** (Standard: aus) – sowohl im
Inserentinnen-Profil als auch im Adminpanel.

- Freigegeben + Gast → CTA „Kostenlos registrieren & ansehen".
- Freigegeben + eingeloggtes Mitglied → Galerie sichtbar.
- Nicht freigegeben → verschwommen/gesperrt, kein Preis, kein Payment.
- Keine privaten Medien → Hinweistext.

Bei Freigabe **mit** vorhandenen privaten Medien erhält die Inserentin einmalig
+3 Launch-Credits (`bonus_private_gallery_launch`).

---

## 6. Installation / Update

```bash
git pull
php artisan migrate
php artisan config:clear
php artisan cache:clear
npm run build
```

Optional: `php artisan credits:grant-launch` für bestehende Inserentinnen.

---

## 7. Später: echte Credit-Käufe ergänzen

Vorbereitet, aber **nicht aktiv**:

- `config/features.php → credit_packages` (1 Credit = CHF 1; 20/50/100).
- `credit_transactions.reference_type/reference_id` + `total_purchased` sind
  bereit, eine spätere Zahlung mit der Credit-Gutschrift zu verknüpfen.

Zum Aktivieren später (nach `LAUNCH_MODE=false`):

1. Zahlungsanbieter anbinden (Stripe/Worldpay/Safepay …).
2. Nach erfolgreicher Zahlung `CreditService::grant($user, $credits, 'purchase',
   ['reference_type' => 'payment', 'reference_id' => $paymentId])` aufrufen.
3. Optional eine `payment_transactions`-Tabelle für Payment-ID/Anbieter/Status/
   Refund ergänzen und mit der Credit-Transaktion verknüpfen.

Der Launch-Modus-Guard in den Checkout-Methoden verhindert währenddessen jeden
echten Checkout.

---

## 8. Geänderte / neue Dateien (Übersicht)

**Neu**
- `config/features.php`
- `app/Models/CreditBalance.php`, `app/Models/CreditTransaction.php`
- `app/Services/CreditService.php`
- `app/Exceptions/InsufficientCreditsException.php`
- `app/Observers/UserObserver.php`
- `app/Console/Commands/GrantLaunchCredits.php`
- `app/Filament/Actions/CreditAdjustActions.php`
- `app/Filament/Resources/CreditBalances/*`
- `app/Filament/Resources/CreditTransactions/*`
- `resources/js/Components/MemberGateModal.vue`
- Migrationen: `*_create_credit_balances_table`,
  `*_create_credit_transactions_table`,
  `*_add_launch_gallery_free_to_profiles`

**Geändert**
- `app/Models/User.php`, `app/Models/Profile.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/Inserent/{ListingController,ProfileController,DashboardController}.php`
- `app/Http/Controllers/Member/{SubscriptionController,MessageController}.php`
- `app/Filament/Resources/Users/*`, `.../Profiles/*`, `.../ListingOrders/Pages/*`
- `resources/js/Pages/Profile/Show.vue`,
  `resources/js/Pages/Inserent/{Dashboard,Profile}.vue`
- `lang/*/dashboard.php`, `lang/*/profile.php`, `lang/*/inserent.php`,
  `lang/*/gate.php`
- `.env.example`
