@extends('layouts.app')

@section('title', 'Moje tréningy')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">📅 Moje tréningy</h1>

    <div class="row">
        <div class="col-lg-8">
            <!-- Nadchádzajúce tréningy -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i>
                        Nadchádzajúce tréningy ({{ $upcomingTrainings->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($upcomingTrainings->count() > 0)
                        @foreach($upcomingTrainings as $training)
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="card-title mb-2">
                                                {{ $training->title }}
                                            </h6>
                                            <p class="text-muted mb-2">
                                                <i class="far fa-calendar"></i>
                                                {{ $training->start_at->format('d.m.Y H:i') }}
                                                -
                                                {{ $training->end_at->format('H:i') }}
                                            </p>
                                            <p class="mb-0">
                                                <strong>Tréner:</strong> {{ $training->creator->name }}<br>
                                                <span class="badge bg-info">
                                                    {{ $training->trainingType?->name ?? 'Bez typu' }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <p class="mb-2">
                                                <strong>Cena:</strong>
                                                <span class="badge bg-warning text-dark">
                                                    {{ $training->price ?? 0 }} kreditov
                                                </span>
                                            </p>
                                            <button
                                                class="btn btn-sm btn-danger"
                                                onclick="unregisterFromTraining({{ $training->id }})"
                                            >
                                                <i class="fas fa-times"></i> Odhlásiť sa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            Nemáš žiadne nadchádzajúce tréningy.
                            <a href="{{ route('training-calendar.index') }}" class="alert-link">Prihlásiť sa na tréning</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Minulé tréningy s možnosťou hodnotenia -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i>
                        Absolvované tréningy ({{ $pastTrainings->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($pastTrainings->count() > 0)
                        @foreach($pastTrainings as $training)
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <!-- Základné informácie -->
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <h6 class="card-title mb-2">{{ $training->title }}</h6>
                                            <p class="text-muted mb-2">
                                                <i class="far fa-calendar"></i>
                                                {{ $training->start_at->format('d.m.Y H:i') }}
                                                -
                                                {{ $training->end_at->format('H:i') }}
                                            </p>
                                            <p class="mb-0">
                                                <strong>Tréner:</strong> {{ $training->creator->name }}<br>
                                                <span class="badge bg-success">Absolvované</span>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-success" style="font-size: 14px;">
                                                <i class="fas fa-star"></i> Absolvované
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Komponent na hodnotenie -->
                                    <hr class="my-3">
                                    <div class="trainer-rating-section">
                                        <x-trainer-rating-form
                                            :trainer="$training->creator"
                                            :training="$training"
                                        />
                                    </div>

                                    <!-- Blade komponent na zobrazenie hodnotení -->
                                    @if(App\Models\TrainerRating::where('trainer_id', $training->created_by_user_id)->exists())
                                        <hr class="my-3">
                                        <div class="trainer-ratings-section">
                                            <x-trainer-ratings-display :trainer="$training->creator" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            Zatiaľ si neabsolvoval žiadne tréningy.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar - Štatistika a Rýchle Odkazy -->
        <div class="col-lg-4">
            <!-- Štatistika -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📊 Tvoje štatistiky</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <strong>Dostupné kredity:</strong>
                            <span class="badge bg-primary float-end" style="font-size: 14px;">
                                {{ auth()->user()->credits ?? 0 }}
                            </span>
                        </li>
                        <li class="mb-3">
                            <strong>Nadchádzajúce:</strong>
                            <span class="badge bg-info float-end" style="font-size: 14px;">
                                {{ $upcomingTrainings->count() }}
                            </span>
                        </li>
                        <li class="mb-0">
                            <strong>Absolvované:</strong>
                            <span class="badge bg-success float-end" style="font-size: 14px;">
                                {{ $pastTrainings->count() }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Rýchle Odkazy -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🔗 Rýchle odkazy</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('training-calendar.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nový tréning
                        </a>
                        <a href="{{ route('user-credits.history') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> História kreditov
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info">
                <h6 class="alert-heading">💡 Tip</h6>
                <small>
                    Keď absolvuješ tréning, môžeš ohodnotiť trénera hviezdičkami.
                    Tvoje hodnotenie pomáha iným používateľom vybrať si ten správny tréning!
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function unregisterFromTraining(trainingId) {
    if (confirm('Naozaj sa chceš odhlásiť z tohto tréningu? Kredity ti budú vrátené podľa politiky vrátenia.')) {
        fetch(`/trainings/${trainingId}/register`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok || response.status === 204) {
                // Úspech - obnov stránku
                location.reload();
            } else {
                alert('Chyba pri odhlasovaní z tréningu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Chyba pri komunikácii so serverom.');
        });
    }
}
</script>

<style>
.trainer-rating-section {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
}

.trainer-ratings-section {
    background-color: #f0f7ff;
    padding: 1rem;
    border-radius: 0.25rem;
}
</style>
@endsection
