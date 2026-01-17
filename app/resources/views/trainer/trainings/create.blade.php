@extends('layouts.app')

@section('title', 'Vytvorenie tréningu')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Vytvorenie tréningu</h1>
    </div>

    <p class="text-muted mb-3">Vyplň parametre tréningu podľa tabuľky <code>trainings</code>.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('trainer.trainings.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Názov (title)</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required
                        class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Popis (description)</label>
                    <textarea id="description" name="description" rows="3"
                        class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_at" class="form-label">Začiatok (start_at)</label>
                        <input id="start_at" name="start_at" type="datetime-local" value="{{ old('start_at') }}" required
                            class="form-control @error('start_at') is-invalid @enderror">
                        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_at" class="form-label">Koniec (end_at)</label>
                        <input id="end_at" name="end_at" type="datetime-local" value="{{ old('end_at') }}" required
                            class="form-control @error('end_at') is-invalid @enderror">
                        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="capacity" class="form-label">Kapacita (capacity)</label>
                        <input id="capacity" name="capacity" type="number" min="0" value="{{ old('capacity', 0) }}" required
                            class="form-control @error('capacity') is-invalid @enderror">
                        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Cena v kreditoch (price)</label>
                        <input id="price" name="price" type="number" min="0" value="{{ old('price', 0) }}" required
                            class="form-control @error('price') is-invalid @enderror">
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', true))>
                    <label class="form-check-label" for="is_active">Aktívny (is_active)</label>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="repeat_weekly" name="repeat_weekly" @checked(old('repeat_weekly'))>
                            <label class="form-check-label" for="repeat_weekly">Opakovať každý týždeň</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="repeat_weeks" class="form-label">Počet týždňov (vrátane prvého)</label>
                        <input id="repeat_weeks" name="repeat_weeks" type="number" min="1" max="52" value="{{ old('repeat_weeks', 1) }}"
                               class="form-control @error('repeat_weeks') is-invalid @enderror">
                        @error('repeat_weeks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Použije sa iba ak zaškrtneš opakovanie.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Vytvoriť tréning</button>
            </form>
        </div>
    </div>
@endsection
