<x-mail::message>
# Dein Profil ist jetzt aktiv! ✅

Dein Inserat **{{ $profile->display_name }}** wurde freigeschaltet und ist jetzt für alle Besucher sichtbar.

<x-mail::button :url="route('profile.show', $profile->slug)">
Profil ansehen
</x-mail::button>

Tipp: Lade Fotos hoch um mehr Abonnenten zu gewinnen!

Inserate Plattform
</x-mail::message>
