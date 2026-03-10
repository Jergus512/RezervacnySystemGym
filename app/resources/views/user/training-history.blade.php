@extends('layouts.app')

@section('title', 'Štatistiky tréningov')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="mb-4">
                <h1 class="h4 mb-2">Štatistiky tréningov</h1>
                <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                    Prehľad absolvovaných tréningov a základných štatistík.
                </p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <p class="mb-2">
                        Tu bude prehľad všetkých tréningov, ktoré si absolvoval(a), máš rezervované
                        alebo si v minulosti navštívil(a).
                    </p>
                    <ul class="mb-0 text-muted" style="font-size: 0.9rem;">
                        <li>Počet absolvovaných tréningov v zvolenom období</li>
                        <li>Prehľad podľa typu tréningu</li>
                        <li>Prehľad podľa trénera</li>
                        <li>Počet minutých kreditov za tréningy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
