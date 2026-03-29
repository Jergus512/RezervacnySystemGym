# Frontend na Hodnotenie Trénerov

Kompletný systém na hodnotenie trénerov zákazníkmi s hviezdičkami (1-5 hviezd), komentármi a štatistikami.

## 📋 Čo bolo implementované

### 1. **Backend**
- ✅ `TrainerRatingController` - spracovanie hodnotení
- ✅ `TrainerRating` model - databázová vrstva
- ✅ 3 API endpoints:
  - `POST /trainers/{trainer}/ratings` - uloženie hodnotenia
  - `GET /trainers/{trainer}/ratings` - zobrazenie všetkých hodnotení + štatistiky
  - `GET /trainings/{training}/my-rating` - moje hodnotenie pre konkrétny tréning

### 2. **Frontend - Vue komponent**
- ✅ `TrainerRatingComponent.vue` - interaktívny komponent s:
  - Hodnotením hviezdičkami (1-5)
  - Voliteľným komentárom (max 500 znakov)
  - Štatistikami: priemer, rozdelenie hviezd
  - Zoznamom posledných hodnotení

### 3. **Frontend - Blade komponenty**
- ✅ `trainer-rating-form.blade.php` - formulár bez Vue (čistý HTML/JS)
- ✅ `trainer-ratings-display.blade.php` - zobrazenie hodnotení a štatistík

### 4. **Routes**
```php
POST   /trainers/{trainer}/ratings              // Uloženie hodnotenia
GET    /trainers/{trainer}/ratings              // API - všetky hodnotenia
GET    /trainings/{training}/my-rating          // API - moje hodnotenie
```

---

## 🚀 Ako Integrovať do Aplikácie

### **OPTION 1: Používanie Vue Komponenty (Odporúčané)**

#### Krok 1: Importuj komponentu v Layout
```blade
<!-- resources/views/layouts/app.blade.php -->
<script>
    import TrainerRatingComponent from '@/components/TrainerRatingComponent.vue'
    
    export default {
        components: {
            TrainerRatingComponent
        }
    }
</script>
```

#### Krok 2: Použi komponentu v hocijakej Blade šablóne
```blade
<!-- Najjednoduché - len formulár -->
<trainer-rating-component 
    :trainer-id="{{ $trainer->id }}"
    :show-ratings="false"
></trainer-rating-component>

<!-- S hodnoteniami konkrétneho tréningu -->
<trainer-rating-component 
    :trainer-id="{{ $training->created_by_user_id }}"
    :training-id="{{ $training->id }}"
    :show-ratings="true"
></trainer-rating-component>
```

---

### **OPTION 2: Používanie Blade Komponent (Bez Vue)**

#### Krok 1: Formulár na hodnotenie
```blade
<!-- resources/views/some-page.blade.php -->
<x-trainer-rating-form :trainer="$trainer" />
```

#### Krok 2: Zobrazenie hodnotení
```blade
<x-trainer-ratings-display :trainer="$trainer" />
```

---

## 📍 Príklady Integrácie

### **1. Na stránke "Moje tréningy" - po absolvovanom tréningu**

```blade
@extends('layouts.app')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <h5>{{ $training->title }}</h5>
        <p class="text-muted">Tréner: {{ $training->creator->name }}</p>
        
        <!-- Komponent na hodnotenie -->
        <trainer-rating-component 
            :trainer-id="{{ $training->created_by_user_id }}"
            :training-id="{{ $training->id }}"
            :show-ratings="false"
        ></trainer-rating-component>
    </div>
</div>
@endsection
```

### **2. Na profile trénera**

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $trainer->name }}</h1>
    
    <!-- Hodnotenie + všetky hodnotenia -->
    <trainer-rating-component 
        :trainer-id="{{ $trainer->id }}"
        :show-ratings="true"
    ></trainer-rating-component>
</div>
@endsection
```

### **3. V kalendári tréningov**

```blade
<!-- Modálne okno alebo sekcia na detaily tréningu -->
<div class="modal-body">
    <h5>{{ $training->title }}</h5>
    <p>Tréner: {{ $training->creator->name }}</p>
    
    <!-- Formulár na hodnotenie -->
    <x-trainer-rating-form :trainer="$training->creator" :training="$training" />
</div>
```

---

## 🎨 CSS Styling

Komponenty používajú Bootstrap 5 classes. Ak chceš upraviť štýly:

### **Farby hviezd**
```css
/* Zmena farby hviezd v TrainerRatingComponent.vue */
.star-btn:hover,
.star-btn.active {
    color: #ffc107;  /* Zlatá - zmeniť na akúkoľvek farbu */
}
```

### **Veľkosť hviezd**
```css
.star-btn {
    font-size: 28px;  /* Zmeniť veľkosť */
}
```

---

## 📊 Ako Fungujú Štatistiky

### **Priemer hodnotenia**
```
Priemer = (Suma všetkých hodnotení) / (Počet hodnotení)
```

### **Rozdelenie hviezd**
```
Percento = (Počet hodnotení s X hviezdami / Celkový počet) * 100
```

### **Ako sa Používajú v Odmene Trénerov**
V `TrainerRewardService`:
```php
$avgUserRating = TrainerRating::where('trainer_id', $trainer->id)
    ->whereBetween('created_at', [$periodStart, $periodEnd])
    ->avg('rating');

// Bonus za hodnotenie: €50 za každú hviezdu
$ratingBonus = $avgUserRating * 50;  // Max €250 za 5⭐
```

---

## 🔐 Bezpečnosť a Validácia

### **Backend Validácia**
```php
// Len zákazníci môžu hodnotiť
if (!$user->isRegularUser()) {
    abort(403);
}

// Hodnotenie 1-5
if ($rating < 1 || $rating > 5) {
    abort(422);
}

// Komentár max 500 znakov
if (strlen($comment) > 500) {
    abort(422);
}

// Kontrola či používateľ navštevoval tréning
$hasAttended = DB::table('training_registrations')
    ->where('training_id', $training->id)
    ->where('user_id', $user->id)
    ->where('status', 'active')
    ->exists();

if (!$hasAttended) {
    abort(403, 'Môžeš hodnotiť iba trénerov, ktorých tréningy si navštevoval.');
}
```

### **Frontend Validácia (Vue)**
```javascript
if (rating === 0) {
    errorMessage = 'Musíš vybrať hodnotenie!'
    return
}

if (comment.length > 500) {
    errorMessage = 'Komentár je príliš dlhý!'
    return
}
```

---

## 🔄 Aktualizácia Odmien pri Novom Hodnotení

Keď používateľ ulož hodnotenie, systém:
1. Uloží hodnotenie do `trainer_ratings` tabuľky
2. Vypočíta priemernú hodnotu všetkých hodnotení
3. Pri ďalšom výpočte odmien sa berie do úvahy toto priemernú hodnotu

```php
// V TrainerRewardService::calculateAndSaveReward()
$avgUserRating = TrainerRating::where('trainer_id', $trainer->id)
    ->whereBetween('created_at', [$periodStart, $periodEnd])
    ->avg('rating');

$ratingBonus = $this->calculateRatingBonus($avgUserRating ?? 0);
```

---

## 🐛 Troubleshooting

### **Komponent sa nezobrazuje**
- ✅ Skontroluj či je Vue nakonfigurované v `resources/js/app.js`
- ✅ Skontroluj či je komponent importovaný v Layout
- ✅ Skontroluj `npm run dev` alebo `npm run build`

### **Hodnotenia sa neukladajú**
- ✅ Skontroluj Network tab v DevTools
- ✅ Skontroluj či je CSRF token v HTML
- ✅ Skontroluj server logs (`php artisan tail`)

### **Štatistiky sa nezobrazia**
- ✅ Skontroluj či sú v databáze záznamy v `trainer_ratings` tabuľke
- ✅ Skontroluj či je endpoint `/trainers/{trainer}/ratings` dostupný

---

## 📈 Budúce Vylepšenia (Voliteľne)

- [ ] Odpovede trénera na komentáre
- [ ] Filtranie hodnotení (podľa hviezd)
- [ ] Emailová notifikácia trénerovi keď dostane hodnotenie
- [ ] Cenzúra neslušných slov
- [ ] Emoji picker pre komentáre
- [ ] Export hodnotení do PDF
- [ ] Grafické zobrazenie trendu hodnotení v čase

---

## 📝 Zhrnutie Tabuľiek a Modelov

```
┌─────────────────────────────────────┐
│ trainer_ratings                     │
├─────────────────────────────────────┤
│ id (PK)                             │
│ trainer_id (FK → users)             │
│ user_id (FK → users)                │
│ training_id (FK → trainings)        │
│ rating (1-5)                        │
│ comment (text, nullable)            │
│ created_at                          │
│ updated_at                          │
│ UNIQUE(trainer_id, user_id, training_id) │
└─────────────────────────────────────┘
```

---

**Vše je hotovo a funguje! 🎉**
