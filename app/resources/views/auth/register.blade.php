@extends('layouts.app')

@php($hideTopbar = true)

@section('title', 'Registrácia - Rezervačný systém')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Registrácia</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Meno</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
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

                    <button type="submit" class="btn btn-primary w-100">Zaregistrovať sa</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <small>Už máš účet? <a href="{{ route('login') }}">Prihlás sa</a>.</small>
            </div>
        </div>
    </div>
</div>
@endsection
