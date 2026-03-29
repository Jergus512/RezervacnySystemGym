@extends('layouts.app')

@section('title', 'Profil trénera - ' . $trainer->name)

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Informácie o trénorovi -->
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="mb-3">{{ $trainer->name }}</h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-user-tie"></i> Profesionálny tréner
                    </p>
                </div>
            </div>

            <!-- Komponent na hodnotenie a zobrazenie hodnotení -->
            <trainer-rating-component
                :trainer-id="{{ $trainer->id }}"
                :show-ratings="true"
            ></trainer-rating-component>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ℹ️ Informácie</h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Email:</strong><br>
                        {{ $trainer->email }}
                    </p>
                    <p class="mb-0">
                        <strong>Typ:</strong><br>
                        <span class="badge bg-success">Tréner</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script setup>
import TrainerRatingComponent from '@/components/TrainerRatingComponent.vue'
</script>
@endsection
