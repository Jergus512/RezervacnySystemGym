@extends('layouts.app')

@section('title', 'Admin analytika')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Admin analytika</h1>
            <div class="text-muted small">Prehľad štatistík rezervácií, financií a tréningov.</div>
        </div>
    </div>

    <form method="GET" class="card mb-4 p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="start" class="form-label mb-1">Od dátumu</label>
                <input type="date" id="start" name="start" value="{{ $start->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="end" class="form-label mb-1">Do dátumu</label>
                <input type="date" id="end" name="end" value="{{ $end->format('Y-m-d') }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filtrovať</button>
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Rezervácie (spolu)</h2>
                    <div class="h3 mb-1">{{ $reservationStats['total_reservations'] }}</div>
                    <div class="text-muted small">Z toho zrušené: {{ $reservationStats['canceled_reservations'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Predané kredity</h2>
                    <div class="h3 mb-1">{{ $financialOverview['sold'] }}</div>
                    <div class="text-muted small">za zvolené obdobie</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Kredity u klientov</h2>
                    <div class="h3 mb-1">{{ $financialOverview['remaining'] }}</div>
                    <div class="text-muted small">aktuálny zostatok všetkých používateľov</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Mesačné štatistiky</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Mesiac</th>
                        <th>Počet rezervácií</th>
                        <th>Počet zrušení</th>
                        <th>Priemerné zaplnenie (%)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($monthlyStats as $m)
                        <tr>
                            <td>{{ $m['label'] }}</td>
                            <td>{{ $m['registrations'] }}</td>
                            <td>{{ $m['cancellations'] }}</td>
                            <td>{{ $m['avg_occupancy'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Žiadne dáta.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Najobľúbenejšie dni v týždni</h2>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th>Deň</th>
                                <th>Rezervácie</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $dowNames = ['Nedeľa', 'Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota'];
                            @endphp
                            @forelse($reservationStats['days_of_week'] as $row)
                                <tr>
                                    <td>{{ $dowNames[(int)$row->dow] ?? $row->dow }}</td>
                                    <td>{{ $row->c }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Žiadne dáta.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Najaktívnejšie časové sloty</h2>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th>Čas</th>
                                <th>Rezervácie</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($reservationStats['time_slots'] as $row)
                                <tr>
                                    <td>{{ $row->slot }}</td>
                                    <td>{{ $row->c }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Žiadne dáta.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Výkon trénerov</h2>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                    <tr>
                        <th>Tréner</th>
                        <th>Počet tréningov</th>
                        <th>Počet rezervácií</th>
                        <th>Priemerné zaplnenie (%)</th>
                        <th>Hodnotenie</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($trainerPerformance as $row)
                        <tr>
                            <td>{{ $row['trainer']->name }}</td>
                            <td>{{ $row['trainings_count'] }}</td>
                            <td>{{ $row['reservations'] }}</td>
                            <td>{{ $row['avg_occupancy'] }}</td>
                            <td>
                                @if($row['rating'] === null)
                                    <span class="text-muted small">nie je k dispozícii</span>
                                @else
                                    {{ number_format($row['rating'], 1) }} / 5
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Žiadne dáta.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Obľúbenosť typov tréningov</h2>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                    <tr>
                        <th>Typ tréningu</th>
                        <th>Počet tréningov</th>
                        <th>Počet rezervácií</th>
                        <th>Priemerné zaplnenie (%)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($trainingPopularity as $row)
                        <tr>
                            <td>{{ $row['type']?->name ?? 'Neznámy typ' }}</td>
                            <td>{{ $row['trainings_count'] }}</td>
                            <td>{{ $row['reservations'] }}</td>
                            <td>{{ $row['avg_occupancy'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Žiadne dáta.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

