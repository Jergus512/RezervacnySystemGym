@extends('layouts.app')

@section('title', 'Evidencia zmien kreditov')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-0 text-white">Evidencia zmien kreditov</h1>
            </div>

            <div class="card bg-dark border-secondary text-white mb-4">
                <div class="card-body">
                    <p class="mb-2">
                        Tu bude prehľad všetkých zmien tvojich kreditov (použitie, nákup, vrátenie).
                    </p>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        Funkčnú evidenciu je možné doplniť napojením na tabuľku transakcií kreditov
                        (napr. <code>credit_transactions</code>) – aktuálne ide len o podstránku a navigáciu.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
