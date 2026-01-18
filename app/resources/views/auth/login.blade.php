@extends('layouts.app')

@php($hideTopbar = true)

@section('title', 'Prihlásenie - Rezervačný systém')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="p-4 p-md-5 rounded-4 shadow-lg auth-card"
             style="background: rgba(255,255,255,.92); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">
            <div class="text-center mb-4">
                <h3 class="mb-1 fw-semibold">Prihlásenie</h3>
                <div class="text-muted small">Vitaj späť. Prihlás sa do systému.</div>
            </div>

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

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Pamätať si ma</label>
                </div>

                <button type="submit" class="btn w-100"
                        style="background:#f97316; border-color:#f97316; color:#fff;">Prihlásiť sa
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">Nemáš účet? <a href="{{ route('register') }}">Registruj sa</a>.</small>
            </div>
        </div>
    </div>
</div>
@endsection
