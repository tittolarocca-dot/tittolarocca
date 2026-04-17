<x-mail::message>
# Neue Nachricht 💬

**{{ $sender->name }}** hat dir eine Nachricht geschickt:

> {{ $preview }}

<x-mail::button :url="route('inserat.messages')">
Nachricht lesen
</x-mail::button>

Inserate Plattform
</x-mail::message>
