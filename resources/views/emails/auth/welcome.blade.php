<x-mail::message>
# Sikeres regisztráció

Szia {{ $name }}!

Sikeresen létrehoztuk a DockJet fiókját.

Most már bejelentkezve használhatod a platformot:

<x-mail::button :url="$homeUrl">
DockJet megnyitása
</x-mail::button>

Köszönjük, hogy csatlakoztál!

Üdvözlettel,
{{ $appName }} csapata
</x-mail::message>
