@extends('layouts.app')

@section('title', 'Dashboard - Rezervačný systém')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Dashboard</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">Vitaj, {{ auth()->user()->name }}. Tu neskôr pridáme správu rezervácií.</p>
            </div>
        </div>
    </div>
</div>
@endsection

