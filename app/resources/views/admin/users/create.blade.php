@extends('layouts.app')

@section('title', 'Nový používateľ')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Vytvoriť nového používateľa</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Meno</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Heslo</label>
                        <input id="password" type="password" name="password" required
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Potvrdenie hesla</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="form-control">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="is_admin" name="is_admin">
                        <label class="form-check-label" for="is_admin">
                            Admin používateľ
                        </label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Späť</a>
                        <button type="submit" class="btn btn-primary">Uložiť</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

