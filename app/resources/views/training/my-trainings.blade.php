@extends('layouts.app')

@section('title', 'Moje tréningy')

@section('content')
<div class="container-fluid px-3 px-md-5 py-5" style="background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);">
    <!-- Header -->
    <div class="mb-5">
        <h1 class="display-5 fw-bold mb-2" style="color: #1a1a1a;">Moje tréningy</h1>
        <p class="text-muted fs-6">Spravuj si svoj tréningový plán a sleduj svoj progres</p>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="p-4 rounded-3" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #ff9800;">
                <div class="text-muted small mb-2">Dostupné kredity</div>
                <div class="display-6 fw-bold" style="color: #ff9800;">{{ auth()->user()->credits ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-4 rounded-3" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #4CAF50;">
                <div class="text-muted small mb-2">Nadchádzajúce</div>
                <div class="display-6 fw-bold" style="color: #4CAF50;">{{ $upcomingTrainings->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-4 rounded-3" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #2196F3;">
                <div class="text-muted small mb-2">Absolvované</div>
                <div class="display-6 fw-bold" style="color: #2196F3;">{{ $pastTrainings->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-4 rounded-3" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #9C27B0;">
                <a href="{{ route('training-calendar.index') }}" class="text-decoration-none">
                    <div class="text-muted small mb-2">Akcia</div>
                    <div class="fw-bold" style="color: #9C27B0; font-size: 1.1rem;">Nový tréning →</div>
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Nadchádzajúce tréningy -->
            <div class="mb-5">
                <h2 class="h4 fw-bold mb-4" style="color: #1a1a1a;">
                    <i class="bi bi-calendar-event" style="color: #4CAF50; margin-right: 10px;"></i>
                    Nadchádzajúce tréningy
                </h2>

                @if($upcomingTrainings->count() > 0)
                    <div class="row g-3">
                        @foreach($upcomingTrainings as $training)
                            <div class="col-12">
                                <div class="p-4 rounded-3" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #4CAF50; transition: all 0.3s ease;">
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <div class="mb-3">
                                                <h5 class="mb-2" style="color: #1a1a1a; font-weight: 600;">{{ $training->title }}</h5>
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <i class="bi bi-calendar3" style="margin-right: 8px; color: #ff9800;"></i>
                                                    {{ $training->start_at->format('d.m.Y H:i') }} - {{ $training->end_at->format('H:i') }}
                                                </div>
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <i class="bi bi-person-circle" style="margin-right: 8px; color: #2196F3;"></i>
                                                    <strong>{{ $training->creator?->name ?? 'Neznámy tréner' }}</strong>
                                                </div>
                                                @if($training->trainingType)
                                                    <div>
                                                        <span class="badge rounded-pill" style="background-color: #ff9800; font-size: 12px;">
                                                            {{ $training->trainingType->name }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <div class="text-muted small mb-1">Cena</div>
                                                    <div class="h5 mb-0" style="color: #ff9800; font-weight: 600;">
                                                        {{ $training->price ?? 0 }} <span style="font-size: 0.8em;">kreditov</span>
                                                    </div>
                                                </div>
                                                <button
                                                    class="btn btn-sm rounded-2"
                                                    style="background-color: #ff4444; color: white; border: none; padding: 8px 16px; font-weight: 500;"
                                                    onclick="unregisterFromTraining(event, {{ $training->id }})"
                                                >
                                                    <i class="bi bi-x-circle" style="margin-right: 5px;"></i>
                                                    Odhlásiť
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-5 rounded-3 text-center" style="background: #fff3e0; border: 2px dashed #ff9800;">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ff9800; margin-bottom: 15px; display: block;"></i>
                        <p class="text-muted mb-3">Nemáš žiadne nadchádzajúce tréningy</p>
                        <a href="{{ route('training-calendar.index') }}" class="btn btn-sm" style="background-color: #ff9800; color: white; border: none;">
                            Prihlásiť sa na tréning
                        </a>
                    </div>
                @endif
            </div>

            <!-- Absolvované tréningy -->
            <div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 fw-bold" style="color: #1a1a1a; margin: 0;">
                        <i class="bi bi-check-circle" style="color: #2196F3; margin-right: 10px;"></i>
                        Absolvované tréningy
                    </h2>
                    <!-- Filter na mesiac -->
                    <div style="min-width: 220px;">
                        <select id="monthFilter" class="form-select form-select-sm" onchange="filterByMonth(this.value)">
                            <option value="">Všetky mesiace</option>
                            @php
                                $months = [];
                                foreach ($pastTrainings as $training) {
                                    $monthKey = $training->start_at->format('Y-m');
                                    if (!isset($months[$monthKey])) {
                                        $months[$monthKey] = $training->start_at->format('F Y');
                                    }
                                }
                                arsort($months);
                                foreach ($months as $key => $label) {
                                    echo "<option value=\"$key\">$label</option>";
                                }
                            @endphp
                        </select>
                    </div>
                </div>

                @if($pastTrainings->count() > 0)
                    <div class="row g-3">
                        @foreach($pastTrainings as $training)
                            <div
                                class="col-12 training-card"
                                data-month="{{ $training->start_at->format('Y-m') }}"
                            >
                                <div class="p-4 rounded-3" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #2196F3; transition: all 0.3s ease;">
                                    <!-- Základné informácie -->
                                    <div class="row align-items-start">
                                        <div class="col-md-8 mb-3 mb-md-0">
                                            <h5 class="mb-2" style="color: #1a1a1a; font-weight: 600;">{{ $training->title }}</h5>
                                            <div class="d-flex align-items-center text-muted small mb-2">
                                                <i class="bi bi-calendar3" style="margin-right: 8px; color: #ff9800;"></i>
                                                {{ $training->start_at->format('d.m.Y H:i') }} - {{ $training->end_at->format('H:i') }}
                                            </div>
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="bi bi-person-circle" style="margin-right: 8px; color: #2196F3;"></i>
                                                <strong>{{ $training->creator?->name ?? 'Neznámy tréner' }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <span class="badge rounded-pill" style="background-color: #4CAF50; font-size: 12px;">
                                                ✓ Absolvované
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Hodnotenie trénera -->
                                    <div class="mt-4 pt-4" style="border-top: 1px solid #eee;">
                                        @include('components.trainer-rating-form', [
                                            'trainer' => $training->creator,
                                            'training' => $training
                                        ])
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-5 rounded-3 text-center" style="background: #e3f2fd; border: 2px dashed #2196F3;">
                        <i class="bi bi-emoji-neutral" style="font-size: 3rem; color: #2196F3; margin-bottom: 15px; display: block;"></i>
                        <p class="text-muted">Zatiaľ si neabsolvoval žiadne tréningy</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Rýchle odkazy -->
            <div class="p-4 rounded-3 mb-4" style="background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h5 class="fw-bold mb-3" style="color: #1a1a1a;">Rýchle odkazy</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('training-calendar.index') }}" class="btn btn-sm rounded-2" style="background-color: #ff9800; color: white; border: none; padding: 10px;">
                        <i class="bi bi-plus-circle" style="margin-right: 5px;"></i>
                        Nový tréning
                    </a>
                    <a href="{{ route('user-credits.history') }}" class="btn btn-sm rounded-2" style="background-color: #2196F3; color: white; border: none; padding: 10px;">
                        <i class="bi bi-graph-up" style="margin-right: 5px;"></i>
                        História kreditov
                    </a>
                </div>
            </div>

            <!-- Tip -->
            <div class="p-4 rounded-3" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 4px solid #ff9800;">
                <h6 class="fw-bold mb-2" style="color: #e65100;">
                    <i class="bi bi-lightbulb" style="margin-right: 5px;"></i>
                    Tip
                </h6>
                <small style="color: #d84315;">
                    Ohodnoť trénera hviezdičkami a pripíš sa k bonusom. Tréneri s najvyšším priemerným hodnotením za mesiac získavajú odmeny.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form pro DELETE requesty -->
<div id="unregisterForm" style="display: none;">
    <form id="destroyTrainingForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
function unregisterFromTraining(event, trainingId) {
    event.preventDefault();

    if (confirm('Naozaj sa chceš odhlásiť z tohto tréningu? Kredity budú vrátené podľa platného refundového poriadku.')) {
        const form = document.getElementById('destroyTrainingForm');
        form.action = `/trainings/${trainingId}/register`;
        form.submit();
    }
}

function filterByMonth(month) {
    const cards = document.querySelectorAll('.training-card');

    if (month === '') {
        // Ak je mesiac prázdny, zobraz všetky karty
        cards.forEach(card => {
            card.style.display = 'block';
        });
    } else {
        // Inak zobraz iba karty s vybraným mesiacom
        cards.forEach(card => {
            if (card.getAttribute('data-month') === month) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
}

// Inicializácia - zobraz všetky karty na začiatku
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.training-card');
    cards.forEach(card => {
        card.style.display = 'block';
    });
});
</script>

<style>
[style*="box-shadow"] {
    transition: box-shadow 0.3s ease, transform 0.2s ease !important;
}

[style*="box-shadow"]:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.12) !important;
    transform: translateY(-2px) !important;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    opacity: 0.9;
}

.bi {
    display: inline-block;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem !important;
    }

    .display-6 {
        font-size: 1.5rem !important;
    }

    .p-4 {
        padding: 1.5rem !important;
    }
}
</style>
@endsection
