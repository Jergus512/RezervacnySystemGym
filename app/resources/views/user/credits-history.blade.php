@extends('layouts.app')

@section('title', 'Štatistiky kreditov')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="mb-4">
                <h1 class="h4 mb-2">Štatistiky kreditov</h1>
                <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                    Evidencia všetkých pohybov s kreditmi – nákup, pridanie, použitie a vrátenie.
                </p>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <p class="mb-2">
                        Tu bude prehľad všetkých pohybov s tvojimi kreditmi.
                    </p>
                    <ul class="mb-0 text-muted" style="font-size: 0.9rem;">
                        <li>Nákup / dobitie kreditov</li>
                        <li>Pridanie kreditov recepciou</li>
                        <li>Použitie kreditov na rezervácie tréningov</li>
                        <li>Vrátenie kreditov pri zrušených tréningoch</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
