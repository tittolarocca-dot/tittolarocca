<x-mail::message>
# Neues Abonnement 🎉

**{{ $subscriber->name }}** hat dein Profil **{{ $profile->display_name }}** abonniert.

**Betrag:** CHF {{ number_format($amountChf, 2) }} / Monat

Du erhältst davon **CHF {{ number_format($amountChf * 0.8, 2) }}** (80%) monatlich.

<x-mail::button :url="route('inserat.messages')">
Nachrichten öffnen
</x-mail::button>

Viel Erfolg auf der bookbunny!
</x-mail::message>
