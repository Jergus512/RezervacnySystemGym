@extends('layouts.app')

@section('title', 'Prehľad absolvovaných tréningov')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-0 text-white">Prehľad absolvovaných tréningov</h1>
            </div>

            <div class="card bg-dark border-secondary text-white mb-4">
                <div class="card-body">
                    <p class="mb-2">
                        Tu bude prehľad všetkých tréningov, ktoré si absolvoval(a).
                    </p>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        Neskôr sem môžeme doplniť detailnejší report (dátum, typ tréningu, tréner, počet použitých kreditov, atď.).
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

