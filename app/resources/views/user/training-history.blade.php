@extends('layouts.app')

@section('title', 'Štatistiky tréningov')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 mb-1 text-white">Štatistiky tréningov</h1>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        Prehľad absolvovaných tréningov s možnosťou filtrovania (dátum, typ tréningu, tréner, ...).
                    </p>
                </div>
            </div>

            <div class="card bg-dark border-secondary text-white mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Obdobie od</label>
                            <input type="date" class="form-control form-control-sm bg-dark text-white border-secondary"
                                disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Obdobie do</label>
                            <input type="date" class="form-control form-control-sm bg-dark text-white border-secondary"
                                disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Typ tréningu</label>
                            <select class="form-select form-select-sm bg-dark text-white border-secondary" disabled>
                                <option>Všetky</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex justify-content-md-end">
                            <button class="btn btn-sm btn-outline-secondary mt-3 mt-md-0" disabled>
                                Filtrovať (čoskoro)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-dark border-secondary text-white">
                <div class="card-body">
                    <p class="mb-2">
                        Tu bude prehľad všetkých tréningov, ktoré si absolvoval(a), máš rezervované
                        alebo si v minulosti navštívil(a) – vrátane základných štatistík.
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
