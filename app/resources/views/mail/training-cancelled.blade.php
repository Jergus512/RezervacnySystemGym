@component('mail::message')
<div style="text-align: center; margin-bottom: 25px;">
    <img src="{{ $logoUrl }}" alt="Super Gym Logo" style="width: 100px; height: auto;">
</div>

<div style="font-size: 14px; line-height: 1.6; color: #333;">
    <p>Dobrý deň {{ $userName }},</p>

    <p>Oznamujeme Vám, že tréning <strong>{{ $trainingTitle }}</strong> bol zrušený.</p>

    <p><strong>Dátum a čas tréningu:</strong> {{ $trainingDate }}</p>

    <p>Ďakujeme za pochopenie.</p>

    <p>S pozdravom,<br>
    <strong>Tím Super Gym</strong></p>
</div>
@endcomponent
