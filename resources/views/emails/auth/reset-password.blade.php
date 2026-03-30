<x-mail::message>
# Jelszó-visszaállítási kérelem

Szia {{ $name }}!

Érkezett egy jelszó-visszaállítási kérelem a DockJet fiókodhoz.
Ha ezt te kérted, kattints az alábbi gombra:

<x-mail::button :url="$resetUrl">
Jelszó visszaállítása
</x-mail::button>

A link **{{ $expireMinutes }} percig** érvényes.

Ha nem te kérted a jelszó módosítását, nincs további teendőd.

Üdvözlettel,
DockJet

---
Ha a gomb nem működik, másold be ezt a linket a böngészőbe:
{{ $resetUrl }}
</x-mail::message>
