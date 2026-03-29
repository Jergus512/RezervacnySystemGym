@extends('layouts.app')

@section('title', 'Moje tréningy')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Moje tréningy</h1>

    <div class="row">
        <div class="col-lg-8">
            <!-- Nadchádzajúce tréningy -->
            <div class="card mb-4 border-top border-5" style="border-top-color: #ff9800 !important;">
                <div class="card-header" style="background-color: #ff9800; color: white;">
                    <h5 class="mb-0">
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
                                            <h6 class="card-title mb-2">{{ $training->title }}</h6>
                                            <p class="text-muted mb-2">
                                                {{ $training->start_at->format('d.m.Y H:i') }} - {{ $training->end_at->format('H:i') }}
                                            </p>
                                            <p class="mb-0">
                                                <strong>Tréner:</strong> {{ $training->creator->name }}
                                            </p>
                                            <p class="mb-0">
                                                <span class="badge" style="background-color: #ff9800;">
                                                    {{ $training->trainingType?->name ?? 'Bez typu' }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-2">
                                                <strong>Cena:</strong>
                                                <span class="badge" style="background-color: #ff9800;">
                                                    {{ $training->price ?? 0 }} kreditov
                                                </span>
                                            </div>
                                            <button
                                                class="btn btn-sm"
                                                style="background-color: #ff9800; color: white; border: none;"
                                                onclick="unregisterFromTraining({{ $training->id }})"
                                            >
                                                Odhlásiť sa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert mb-0" style="background-color: #fff3e0; border-color: #ff9800; color: #e65100;">
                            Nemáš žiadne nadchádzajúce tréningy.
                            <a href="{{ route('training-calendar.index') }}" style="color: #e65100;">Prihlásiť sa na tréning</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Minulé tréningy s možnosťou hodnotenia -->
            <div class="card border-top border-5" style="border-top-color: #ff9800 !important;">
                <div class="card-header" style="background-color: #ff9800; color: white;">
                    <h5 class="mb-0">Absolvované tréningy ({{ $pastTrainings->count() }})</h5>
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
                                                {{ $training->start_at->format('d.m.Y H:i') }} - {{ $training->end_at->format('H:i') }}
                                            </p>
                                            <p class="mb-0">
                                                <strong>Tréner:</strong> {{ $training->creator->name }}
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="badge" style="background-color: #ff9800; font-size: 12px;">
                                                Absolvované
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

                                    <!-- Zobrazenie hodnotení -->
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
                        <div class="alert mb-0" style="background-color: #fff3e0; border-color: #ff9800; color: #e65100;">
                            Zatiaľ si neabsolvoval žiadne tréningy.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar - Štatistika a Rýchle Odkazy -->
        <div class="col-lg-4">
            <!-- Štatistika -->
            <div class="card mb-4 border-top border-5" style="border-top-color: #ff9800 !important;">
                <div class="card-header" style="background-color: #ff9800; color: white;">
                    <h5 class="mb-0">Tvoje štatistiky</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <strong>Dostupné kredity:</strong>
                            <span class="badge float-end" style="background-color: #ff9800; font-size: 14px;">
                                {{ auth()->user()->credits ?? 0 }}
                            </span>
                        </li>
                        <li class="mb-3">
                            <strong>Nadchádzajúce:</strong>
                            <span class="badge float-end" style="background-color: #ff9800; font-size: 14px;">
                                {{ $upcomingTrainings->count() }}
                            </span>
                        </li>
                        <li class="mb-0">
                            <strong>Absolvované:</strong>
                            <span class="badge float-end" style="background-color: #ff9800; font-size: 14px;">
                                {{ $pastTrainings->count() }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Rýchle Odkazy -->
            <div class="card mb-4 border-top border-5" style="border-top-color: #ff9800 !important;">
                <div class="card-header" style="background-color: #ff9800; color: white;">
                    <h5 class="mb-0">Rýchle odkazy</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('training-calendar.index') }}" class="btn btn-sm" style="background-color: #ff9800; color: white; border: none;">
                            Nový tréning
                        </a>
                        <a href="{{ route('user-credits.history') }}" class="btn btn-sm" style="background-color: #ff9800; color: white; border: none;">
                            História kreditov
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert" style="background-color: #fff3e0; border-color: #ff9800; border-left: 4px solid #ff9800;">
                <h6 style="color: #e65100;">Tip</h6>
                <small style="color: #e65100;">
                    Keď absolvuješ tréning, môžeš ohodnotiť trénera. Tvoje hodnotenie pomáha iným používateľom.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function unregisterFromTraining(trainingId) {
    if (confirm('Naozaj sa chceš odhlásiť z tohto tréningu?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/trainings/${trainingId}/register`;
        form.innerHTML = `
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.trainer-rating-section {
    background-color: #fff3e0;
    padding: 1rem;
    border-radius: 0.25rem;
    border-left: 4px solid #ff9800;
}

.trainer-ratings-section {
    background-color: #fff3e0;
    padding: 1rem;
    border-radius: 0.25rem;
    border-left: 4px solid #ff9800;
}

.btn:hover {
    opacity: 0.9;
}
</style>
@endsection
