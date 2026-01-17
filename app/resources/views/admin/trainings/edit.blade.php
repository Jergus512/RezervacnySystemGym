@extends('layouts.app')

@section('title', 'Admin: Upraviť tréning')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h3 mb-0">Admin: Upraviť tréning</h1>
        <a href="{{ route('training-calendar.index') }}" class="btn btn-outline-secondary">Späť na kalendár</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.trainings.update', $training) }}" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">Názov</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $training->title) }}" required
                           class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Popis</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $training->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_at" class="form-label">Začiatok</label>
                        <input id="start_at" name="start_at" type="datetime-local"
                               value="{{ old('start_at', $training->start_at?->format('Y-m-d\\TH:i')) }}" required
                               class="form-control @error('start_at') is-invalid @enderror">
                        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_at" class="form-label">Koniec</label>
                        <input id="end_at" name="end_at" type="datetime-local"
                               value="{{ old('end_at', $training->end_at?->format('Y-m-d\\TH:i')) }}" required
                               class="form-control @error('end_at') is-invalid @enderror">
                        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="capacity" class="form-label">Kapacita</label>
                        <input id="capacity" name="capacity" type="number" min="0" value="{{ old('capacity', $training->capacity) }}" required
                               class="form-control @error('capacity') is-invalid @enderror">
                        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">Cena (kredity)</label>
                        <input id="price" name="price" type="number" min="0" value="{{ old('price', $training->price) }}" required
                               class="form-control @error('price') is-invalid @enderror">
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="created_by_user_id" class="form-label">Tréner</label>
                        <select id="created_by_user_id" name="created_by_user_id" class="form-select @error('created_by_user_id') is-invalid @enderror">
                            <option value="">— bez trénera —</option>
                            @foreach($trainers as $tr)
                                <option value="{{ $tr->id }}" @selected((string) old('created_by_user_id', $training->created_by_user_id) === (string) $tr->id)>
                                    {{ $tr->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('created_by_user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Určuje, kto tréning vedie/vytvoril (zobrazí sa v kalendári).</div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $training->is_active))>
                    <label class="form-check-label" for="is_active">Aktívny</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Uložiť</button>

                    <button type="submit" form="delete-training" class="btn btn-outline-danger">Odstrániť</button>
                </div>
            </form>

            <form id="delete-training" method="POST" action="{{ route('admin.trainings.destroy', $training) }}" class="d-none"
                  onsubmit="return confirm('Naozaj chceš odstrániť tento tréning?');">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection

