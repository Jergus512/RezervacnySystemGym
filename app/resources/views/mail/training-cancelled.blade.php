@component('mail::message')
# ![Super Gym Logo]({{ $logoUrl }})

Dobrý deň {{ $userName }},

Oznamujeme Vám, že tréning **{{ $trainingTitle }}** bol zrušený.

**Dátum a čas tréningu:** {{ $trainingDate }}

Ďakujeme za pochopenie.

S pozdravom,
**Tím Super Gym**

@endcomponent
