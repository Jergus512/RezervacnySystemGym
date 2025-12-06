@extends('layouts.app')

@section('title', 'Prihlásenie - Rezervačný systém')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Prihlásenie</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
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

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Pamätať si ma</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Prihlásiť sa</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <small>Nemáš účet? <a href="{{ route('register') }}">Registruj sa</a>.</small>
            </div>
        </div>
    </div>
</div>
@endsection

