<x-mail::message>
# {{ $isApproved ? 'Foglalás jóváhagyva' : 'Foglalás elutasítva' }}

Szia {{ $name }}!

@if ($isApproved)
A(z) **{{ $boatName }}** hajóra leadott foglalásodat jóváhagyta **{{ $ownerName }}**.
@else
A(z) **{{ $boatName }}** hajóra leadott foglalásodat elutasította **{{ $ownerName }}**.
@endif

**Időszak:** {{ $startDate }} - {{ $endDate }}

<x-mail::button :url="$reservationsUrl">
Foglalásaim megnyitása
</x-mail::button>

@if ($isApproved)
Jó utat kívánunk és köszönjük, hogy a {{ $appName }} platformot használod.
@else
Kérjük, nézd meg a foglalásaidat, és válassz másik időpontot vagy másik hirdetést.
@endif

Üdvözlettel,
{{ $appName }} csapata
</x-mail::message>