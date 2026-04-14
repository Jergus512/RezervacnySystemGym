# Oprava: Absolvované tréningy sa nezobrazujú a nemôžu sa ohodnotiť

## Problém
Keď ste absolvovali tréning ako používateľ klient:
1. Tréning sa najskôr zobrazoval v "Absolvovaných tréningoch"
2. Neskôr sa vymažal alebo sa stal neaktívnym
3. Nemohli ste ho už ohodnotiť
4. Tréning zmizol z histórie

## Root Cause (Pôvodná príčina)
Problém bol v databázovej logike a dotazoch:

### 1. Globálny filter na `TrainingRegistration`
Model `TrainingRegistration` mal globálny scope `'only_active'`, ktorý **automaticky filtroval** všetky registrácie so statusom `'canceled'`:
```php
protected static function booted(): void
{
    static::addGlobalScope('only_active', function (Builder $query) {
        $query->where('training_registrations.status', '!=', 'canceled');
    });
}
```

### 2. Relacia `trainings()` v User modeli
Relacia filtrovala iba `'active'` registrácie:
```php
public function trainings(): BelongsToMany
{
    return $this->belongsToMany(Training::class, 'training_registrations')
        ->wherePivot('status', 'active')  // ← PROBLÉM: iba aktívne
        ->withTimestamps();
}
```

### 3. Logika v `MyTrainingsController`
Minulé tréningy sa načítavali cez tú istú relaciju, ktorá filtrovala aktívne registrácie:
```php
$pastTrainings = $user->trainings()  // ← Iba 'active' status
    ->where('start_at', '<', now())
    ->where('is_active', true)  // ← PROBLÉM: iba aktívne tréningy
    ->get();
```

### 4. Kontrola v `TrainerRatingController`
Pri overovaní toho, či používateľ absolvoval tréning, sa nepočítali zrušené registrácie.

## Riešenie

### 1. ✅ Nová relacia `allTrainings()` v User modeli
Vytvorili sme novú relaciju, ktorá vracia **všetky** tréningy vrátane tých so `'canceled'` statusom:
```php
public function allTrainings(): BelongsToMany
{
    return $this->belongsToMany(Training::class, 'training_registrations')
        ->withPivot('status')
        ->withTimestamps();
}
```

### 2. ✅ Nová relacia `allUsers()` v Training modeli
Podobne ako vyššie:
```php
public function allUsers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'training_registrations')
        ->withPivot('status')
        ->withTimestamps();
}
```

### 3. ✅ Opravená logika v `MyTrainingsController`
Minulé tréningy teraz používajú **raw query s `whereIn()` podotázkou**, ktorá priamo hľadá tréningy bez filtrácii na status registrácie:
```php
// Minulé tréningy - všetky registrácie na minulých tréningoch (aj zrušené)
$pastTrainings = Training::query()
    ->whereIn('id', function ($query) use ($user) {
        $query->select('training_id')
            ->from('training_registrations')
            ->where('user_id', $user->id);
    })
    ->where('start_at', '<', now())
    ->orderBy('start_at', 'desc')
    ->with('creator', 'trainingType')
    ->get();
```

**Prečo raw query?** Relácie v Laravel aplikujú globálne scope, takže aj `allTrainings()` by filtrovala zrušené registrácie. Raw query toto obchádza a hľadá všetky tréningy, kde má používateľ akúkoľvek registráciu (bez ohľadu na status).

### 4. ✅ Opravená validácia v `TrainerRatingController`
Pri overovaní toho, či používateľ absolvoval tréning, teraz akceptujeme aj `'canceled'` registrácie:
```php
$hasAttended = DB::table('training_registrations')
    ->where('training_id', $training->id)
    ->where('user_id', $user->id)
    ->whereIn('status', ['active', 'canceled'])  // ← Akceptujeme oba stavy
    ->exists();
```

## Výsledok
Teraz:
- ✅ Všetky absolvované tréningy (vrátane zrušených) sa zobrazia v histórii
- ✅ Môžete ich ohodnotiť bez ohľadu na to, či boli zrušené
- ✅ Hodnotenia ostanú viditeľné aj po zrušení tréningu
- ✅ Globálny filter `'only_active'` sa stále aplikuje na ďalšie časti aplikácie, kde je potrebný (napr. nadchádzajúce tréningy)

## Zmenené súbory
1. `/app/app/Models/User.php` - Pridaná relacia `allTrainings()`
2. `/app/app/Models/Training.php` - Pridaná relacia `allUsers()`
3. `/app/app/Http/Controllers/MyTrainingsController.php` - Opravená logika s raw query na načítavanie `pastTrainings`
4. `/app/app/Http/Controllers/TrainerRatingController.php` - Opravená validácia registrácie