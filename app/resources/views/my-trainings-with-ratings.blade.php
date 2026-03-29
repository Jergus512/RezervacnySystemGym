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
                    <h5 class="mb-0">Nadchádzajúce tréningy</h5>
                </div>
                <div class="card-body">
                    @if($upcomingTrainings->count() > 0)
                        @foreach($upcomingTrainings as $training)
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="card-title mb-1">{{ $training->title }}</h6>
                                            <p class="text-muted mb-2">
                                                <i class="far fa-calendar"></i>
                                                {{ $training->start_at->format('d.m.Y H:i') }}
                                                -
                                                {{ $training->end_at->format('H:i') }}
                                            </p>
                                            <p class="mb-0">
                                                <span class="badge bg-info">{{ $training->trainingType?->name ?? 'Bez typu' }}</span>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <p class="mb-2">
                                                <strong>Cena:</strong> {{ $training->price ?? 0 }} kreditov
                                            </p>
                                            <button
                                                class="btn btn-sm btn-danger"
                                                @click="unregisterFromTraining({{ $training->id }})"
                                                v-if="canUnregister({{ $training->id }})"
                                            >
                                                Odhlásiť sa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-0">
                            Nemáš žiadne nadchádzajúce tréningy.
                            <a href="{{ route('training-calendar.index') }}">Prihlásiť sa na tréning</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Minulé tréningy s možnosťou hodnotenia -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Absolvované tréningy</h5>
                </div>
                <div class="card-body">
                    @if($pastTrainings->count() > 0)
                        @foreach($pastTrainings as $training)
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="card-title mb-1">{{ $training->title }}</h6>
                                            <p class="text-muted mb-2">
                                                <i class="far fa-calendar"></i>
                                                {{ $training->start_at->format('d.m.Y H:i') }}
                                                -
                                                {{ $training->end_at->format('H:i') }}
                                            </p>
                                            <p class="mb-0">
                                                <strong>Tréner:</strong> {{ $training->creator->name }}
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-success">Absolvované</span>
                                        </div>
                                    </div>

                                    <!-- Komponent na hodnotenie trénera -->
                                    <trainer-rating-component
                                        :trainer-id="{{ $training->created_by_user_id }}"
                                        :training-id="{{ $training->id }}"
                                        :show-ratings="false"
                                    ></trainer-rating-component>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-info mb-0">
                            Zatiaľ si neabsolvoval žiadne tréningy.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar - Štatistika -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📊 Tvoje štatistiky</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <strong>Kredity:</strong>
                            <span class="badge bg-primary float-end">{{ auth()->user()->credits ?? 0 }}</span>
                        </li>
                        <li class="mb-3">
                            <strong>Nadchádzajúce:</strong>
                            <span class="badge bg-info float-end">{{ $upcomingTrainings->count() }}</span>
                        </li>
                        <li class="mb-0">
                            <strong>Absolvované:</strong>
                            <span class="badge bg-success float-end">{{ $pastTrainings->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Odkaz na kalendár -->
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="card-title">Chceš sa prihlásiť?</h6>
                    <a href="{{ route('training-calendar.index') }}" class="btn btn-primary w-100">
                        Zobraziť všetky tréningy
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script setup>
import TrainerRatingComponent from '@/components/TrainerRatingComponent.vue'

const unregisterFromTraining = async (trainingId) => {
    if (confirm('Chceš sa odisť z tohto tréningu?')) {
        try {
            const response = await fetch(`/trainings/${trainingId}/register`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (response.ok) {
                location.reload()
            } else {
                alert('Chyba pri odhlasovaní z tréningu.')
            }
        } catch (error) {
            console.error('Error:', error)
            alert('Chyba pri komunikácii so serverom.')
        }
    }
}

const canUnregister = (trainingId) => {
    // Logika na kontrolu či sa používateľ môže odhlásiť
    return true
}
</script>
