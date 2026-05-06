<x-mail::message>
# Auszahlung wird bearbeitet 💰

Deine Auszahlung für **{{ $payout->period_start->format('M Y') }}** wird jetzt bearbeitet.

| | |
|---|---|
| Brutto | CHF {{ number_format($payout->gross_amount_chf, 2) }} |
| Provision (20%) | CHF {{ number_format($payout->commission_chf, 2) }} |
| **Netto** | **CHF {{ number_format($payout->net_amount_chf, 2) }}** |

Der Betrag wird innerhalb von 3–5 Werktagen auf dein IBAN überwiesen.

<x-mail::button :url="route('inserat.payouts')">
Auszahlungen ansehen
</x-mail::button>

bookbunny
</x-mail::message>
