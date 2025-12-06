@extends('layouts.app')

@section('title', 'Upraviť používateľa')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Upraviť používateľa</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Meno</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nové heslo (voliteľné)</label>
                        <input id="password" type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ak necháš prázdne, heslo sa nezmení.</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Potvrdenie nového hesla</label>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="form-control">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_admin" name="is_admin" @checked(old('is_admin', $user->is_admin))>
                        <label class="form-check-label" for="is_admin">
                            Admin používateľ
                        </label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Späť</a>
                        <button type="submit" class="btn btn-primary">Uložiť zmeny</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
