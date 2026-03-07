@extends('layouts.app')

@section('title', 'Admin - Nastavenia')

@section('content')
    <div class="container py-4">
        <h1 class="mb-3">Nastavenia penalizácie za zrušenie rezervácie</h1>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="refund_window_hours" class="form-label"></label>
                        <div class="d-flex gap-2 align-items-center">
                            <input id="refund_window_hours" name="refund_window_hours" type="number" class="form-control w-25 @error('refund_window_hours') is-invalid @enderror" value="{{ old('refund_window_hours', intdiv($settings->refund_window_minutes ?? 0, 60)) }}" min="0">
                            <span>hodín</span>
                            <input id="refund_window_minutes_part" name="refund_window_minutes_part" type="number" class="form-control w-25 @error('refund_window_minutes_part') is-invalid @enderror" value="{{ old('refund_window_minutes_part', ($settings->refund_window_minutes ?? 0) % 60) }}" min="0" max="59">
                            <span>minút</span>
                        </div>
                        @error('refund_window_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('refund_window_minutes_part')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Ak sa používateľ odhlási viac ako tento počet minút pred začiatkom tréningu, vráti sa mu určitá suma.
                            @php
                                $minutes = old('refund_window_minutes', $settings->refund_window_minutes);
                                $hours = intdiv($minutes, 60);
                                $remainder = $minutes % 60;
                            @endphp
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Politika penalizácie (pri neskorom odhlásení)</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="penalty_policy" id="policy_half" value="half" {{ old('penalty_policy', $settings->penalty_policy) === 'half' ? 'checked' : '' }}>
                                <label class="form-check-label" for="policy_half">Vráti sa polovica</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="penalty_policy" id="policy_none" value="none" {{ old('penalty_policy', $settings->penalty_policy) === 'none' ? 'checked' : '' }}>
                                <label class="form-check-label" for="policy_none">Nebude sa vrátená žiadna suma</label>
                            </div>
                        </div>
                        @error('penalty_policy')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Uložiť nastavenia</button>
                        <a href="{{ route('admin.trainings.index') }}" class="btn btn-secondary">Späť na tréningy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
