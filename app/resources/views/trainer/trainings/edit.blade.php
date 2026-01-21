@extends('layouts.app')

@section('title', 'Upraviť tréning')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h3 mb-0">Upraviť tréning</h1>
        <a href="{{ route('trainer.trainings.index') }}" class="btn btn-outline-secondary">Späť</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('trainer.trainings.update', $training) }}" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="training_type_id" class="form-label">Typ tréningu</label>
                    <select id="training_type_id" name="training_type_id" class="form-select @error('training_type_id') is-invalid @enderror">
                        <option value="">— bez typu —</option>
                        @foreach(($trainingTypes ?? []) as $tt)
                            <option value="{{ $tt->id }}" @selected((string) old('training_type_id', $training->training_type_id) === (string) $tt->id)>
                                {{ $tt->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('training_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Názov (title)</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $training->title) }}" required
                           class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Popis (description)</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $training->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_at" class="form-label">Začiatok (start_at)</label>
                        <input id="start_at" name="start_at" type="datetime-local"
                               value="{{ old('start_at', $training->start_at?->format('Y-m-d\TH:i')) }}" required
                               class="form-control @error('start_at') is-invalid @enderror">
                        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_at" class="form-label">Koniec (end_at)</label>
                        <input id="end_at" name="end_at" type="datetime-local"
                               value="{{ old('end_at', $training->end_at?->format('Y-m-d\TH:i')) }}" required
                               class="form-control @error('end_at') is-invalid @enderror">
                        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="capacity" class="form-label">Kapacita (capacity)</label>
                        <input id="capacity" name="capacity" type="number" min="0" value="{{ old('capacity', $training->capacity) }}" required
                               class="form-control @error('capacity') is-invalid @enderror">
                        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Cena v kreditoch (price)</label>
                        <input id="price" name="price" type="number" min="0" value="{{ old('price', $training->price) }}" required
                               class="form-control @error('price') is-invalid @enderror">
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $training->is_active))>
                    <label class="form-check-label" for="is_active">Aktívny (is_active)</label>
                </div>

                <button type="submit" class="btn btn-primary">Uložiť zmeny</button>
            </form>
        </div>
    </div>
@endsection
