@extends('layouts.app')

@section('title', 'Evidencia kreditov')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h4 mb-2">Evidencia kreditov</h1>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        Prehľad všetkých pohybov s kreditmi – nákup, pridanie, použitie a vrátenie.
                    </p>
                </div>
                <div class="text-end">
                    <div class="text-muted" style="font-size: 0.8rem;">Aktuálny zostatok</div>
                    <div class="h4 mb-0">{{ $balance }}</div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1" style="font-size: 0.8rem;">Pridané kredity</div>
                            <div class="h5 mb-0">{{ $totalAdded }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1" style="font-size: 0.8rem;">Minuté kredity</div>
                            <div class="h5 mb-0">{{ abs($totalSubtracted) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">História pohybov</h2>
                    @if($movements->isEmpty())
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">Zatiaľ nemáš žiadne pohyby s kreditmi.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Dátum</th>
                                    <th>Popis</th>
                                    <th class="text-end">Zmena</th>
                                    <th class="text-end">Typ</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($movements as $movement)
                                    <tr>
                                        <td>{{ optional($movement->created_at)->format('d.m.Y H:i') }}</td>
                                        <td>{{ $movement->description ?? '—' }}</td>
                                        <td class="text-end">
                                            @if($movement->amount > 0)
                                                <span class="text-success">+{{ $movement->amount }}</span>
                                            @elseif($movement->amount < 0)
                                                <span class="text-danger">{{ $movement->amount }}</span>
                                            @else
                                                {{ $movement->amount }}
                                            @endif
                                        </td>
                                        <td class="text-end" style="font-size: 0.8rem;">
                                            {{ $movement->type }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 mb-0 text-muted" style="font-size: 0.8rem;">
                            Zobrazujú sa posledné pohyby (max. 200 záznamov).
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
