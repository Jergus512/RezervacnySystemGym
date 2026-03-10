@extends('layouts.app')

@section('title', 'Štatistiky tréningov')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h4 mb-2">Štatistiky tréningov</h1>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        Prehľad absolvovaných tréningov, rozdelenie podľa typu a trénera a minuté kredity.
                    </p>
                </div>
            </div>

            {{-- Filter panel --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="from" class="form-label mb-1" style="font-size: 0.85rem;">Od dátumu</label>
                            <input type="date" name="from" id="from" class="form-control form-control-sm"
                                   value="{{ $startDate ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label for="to" class="form-label mb-1" style="font-size: 0.85rem;">Do dátumu</label>
                            <input type="date" name="to" id="to" class="form-control form-control-sm"
                                   value="{{ $endDate ?? '' }}">
                        </div>
                        <div class="col-md-4 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm">Zobraziť</button>
                            <a href="{{ route('user-trainings.history') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                    <p class="mt-2 mb-0 text-muted" style="font-size: 0.8rem;">
                        Ak nezvolíš dátum, zobrazujú sa posledné 3 mesiace.
                    </p>
                </div>
            </div>

            {{-- Summary cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1" style="font-size: 0.8rem;">Počet tréningov</div>
                            <div class="h4 mb-0">{{ $totalTrainings }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1" style="font-size: 0.8rem;">Minuté kredity</div>
                            <div class="h4 mb-0">{{ $totalCredits }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1" style="font-size: 0.8rem;">Priemer kreditov / tréning</div>
                            <div class="h4 mb-0">
                                @if($totalTrainings > 0)
                                    {{ number_format($totalCredits / $totalTrainings, 1, ',', ' ') }}
                                @else
                                    –
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Breakdown by type and trainer --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-3">Podľa typu tréningu</h2>
                            @if($byType->isEmpty())
                                <p class="mb-0 text-muted" style="font-size: 0.9rem;">Žiadne tréningy v zvolenom období.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                        <tr>
                                            <th>Typ</th>
                                            <th class="text-end">Tréningy</th>
                                            <th class="text-end">Kredity</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($byType as $typeName => $stats)
                                            <tr>
                                                <td>{{ $typeName }}</td>
                                                <td class="text-end">{{ $stats['count'] }}</td>
                                                <td class="text-end">{{ $stats['credits'] }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-3">Podľa trénera</h2>
                            @if($byTrainer->isEmpty())
                                <p class="mb-0 text-muted" style="font-size: 0.9rem;">Žiadne tréningy v zvolenom období.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                        <tr>
                                            <th>Tréner</th>
                                            <th class="text-end">Tréningy</th>
                                            <th class="text-end">Kredity</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($byTrainer as $trainerName => $stats)
                                            <tr>
                                                <td>{{ $trainerName }}</td>
                                                <td class="text-end">{{ $stats['count'] }}</td>
                                                <td class="text-end">{{ $stats['credits'] }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detailed list of trainings --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Zoznam tréningov</h2>
                    @if($trainings->isEmpty())
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">V tomto období si nemal(a) žiadne tréningy.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Dátum</th>
                                    <th>Čas</th>
                                    <th>Názov</th>
                                    <th>Typ</th>
                                    <th>Tréner</th>
                                    <th class="text-end">Kredity</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($trainings as $training)
                                    <tr>
                                        <td>{{ optional($training->start_at)->format('d.m.Y') }}</td>
                                        <td>{{ optional($training->start_at)->format('H:i') }}&nbsp;-&nbsp;{{ optional($training->end_at)->format('H:i') }}</td>
                                        <td>{{ $training->title }}</td>
                                        <td>{{ optional($training->trainingType)->name ?? '—' }}</td>
                                        <td>{{ optional($training->creator)->name ?? '—' }}</td>
                                        <td class="text-end">{{ $training->price }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
