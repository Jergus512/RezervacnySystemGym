@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ $logoUrl }}" alt="Super Gym Logo" style="width: 120px; height: auto;">
</div>

Dobrý deň {{ $userName }},

Oznamujeme Vám, že tréning **{{ $trainingTitle }}** bol zrušený.

**Dátum a čas tréningu:** {{ $trainingDate }}

Ďakujeme za pochopenie.

S pozdravom,
**Tím Super Gym**

@endcomponent
