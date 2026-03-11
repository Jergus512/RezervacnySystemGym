@extends('layouts.app')

@section('title', 'Moje trénerské štatistiky')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Moje trénerské štatistiky</h1>
            <div class="text-muted small">Prehľad mojich tréningov a výkonu za zvolené obdobie.</div>
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
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Počet mojich tréningov</h2>
                    <div class="h3 mb-1">{{ $stats['trainings_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Rezervácie</h2>
                    <div class="h3 mb-1">{{ $stats['reservations'] }}</div>
                    <div class="text-muted small">Z toho zrušené: {{ $stats['canceled_reservations'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Priemerné zaplnenie</h2>
                    <div class="h3 mb-1">{{ $stats['avg_occupancy'] }} %</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Získané kredity</h2>
                    <div class="h3 mb-1">{{ $stats['credits_gained'] }}</div>
                    <div class="text-muted small">na mojich tréningoch (charge/refund)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Moje tréningy v období</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Dátum a čas</th>
                        <th>Názov</th>
                        <th>Kapacita</th>
                        <th>Obsadenosť</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($trainings as $training)
                        @php
                            $resCount = $training->users()->count();
                            $capacity = (int) $training->capacity;
                            $occ = $capacity > 0 ? number_format(($resCount * 100.0) / $capacity, 1, '.', '') : 0;
                        @endphp
                        <tr>
                            <td>{{ $training->start_at?->format('d.m.Y H:i') }}</td>
                            <td>{{ $training->title }}</td>
                            <td>{{ $capacity }}</td>
                            <td>{{ $occ }} % ({{ $resCount }} z {{ $capacity }})</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">V tomto období nemáš žiadne tréningy.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

