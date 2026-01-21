@extends('layouts.app')

@section('title', 'Vytvorenie oznamu')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Vytvorenie oznamu</h1>
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">Späť</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.announcements.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Názov (voliteľné)</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}"
                           class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Text oznamu</label>
                    <textarea id="content" name="content" rows="8" required
                              class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Zatiaľ jednoduchý editor (textarea). Keď budeš chcieť WYSIWYG (napr. TinyMCE/CKEditor), doplníme ho.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="active_from" class="form-label">Aktívny od</label>
                        <input id="active_from" name="active_from" type="datetime-local" value="{{ old('active_from') }}"
                               class="form-control @error('active_from') is-invalid @enderror">
                        @error('active_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="active_to" class="form-label">Aktívny do</label>
                        <input id="active_to" name="active_to" type="datetime-local" value="{{ old('active_to') }}"
                               class="form-control @error('active_to') is-invalid @enderror">
                        @error('active_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', true))>
                    <label class="form-check-label" for="is_active">Zapnutý (is_active)</label>
                </div>

                <button type="submit" class="btn btn-primary">Uložiť oznam</button>
            </form>
        </div>
    </div>
@endsection

