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
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Rezervácie (spolu)</h2>
                    <div class="h3 mb-1">{{ $reservationStats['total_reservations'] }}</div>
                    <div class="text-muted small">Z toho zrušené: {{ $reservationStats['canceled_reservations'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Predané kredity</h2>
                    <div class="h3 mb-1">{{ $financialOverview['sold'] }}</div>
                    <div class="text-muted small">za zvolené obdobie</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted mb-2">Použité kredity</h2>
                    <div class="h3 mb-1">{{ $financialOverview['used'] }}</div>
                    <div class="text-muted small">minuté kredity na rezervácie v období</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
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
prvy top tr                        <th>Zrušené rezervácie</th>
                        <th>Získané kredity</th>
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
                            <td>{{ $row['canceled_reservations'] }}</td>
                            <td>{{ $row['credits_gained'] }}</td>
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
                            <td colspan="7" class="text-center text-muted py-3">Žiadne dáta.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">💰 Odmeny trénerov za obdobie</h2>
            <p class="text-muted small mb-3">Odmeny sú vypočítané na základe počtu tréningov, obsadenosti, hodnotenia od zákazníkov a získaných kreditov.</p>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                    <tr>
                        <th>Tréner</th>
                        <th>Počet mesiacov</th>
                        <th>Celková odmena (€)</th>
                        <th>Detaily</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($trainerRewards as $row)
                        <tr>
                            <td><strong>{{ $row['trainer']->name }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">{{ $row['rewards_count'] }}</span>
                            </td>
                            <td>
                                <strong style="font-size: 18px; color: #28a745;">€{{ number_format($row['total_amount'], 2) }}</strong>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#reward-details-{{ $row['trainer']->id }}" aria-expanded="false">
                                    Zobraziť 👁️
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="reward-details-{{ $row['trainer']->id }}">
                            <td colspan="4" class="p-3 bg-light">
                                <h6 class="mb-2">Podrobný rozpad odmeny za {{ $start->format('d.m.Y') }} - {{ $end->format('d.m.Y') }}:</h6>
                                <div class="row">
                                    @php
                                        $rewardDetails = \App\Models\TrainerReward::where('trainer_id', $row['trainer']->id)
                                            ->whereBetween('created_at', [$start, $end])
                                            ->orderBy('period_start', 'desc')
                                            ->get();
                                    @endphp
                                    @forelse($rewardDetails as $reward)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 bg-white">
                                                <div class="card-body p-2">
                                                    <h6 class="card-title mb-2">
                                                        Mesiac: {{ \Carbon\Carbon::parse($reward->period_start)->format('m/Y') }}
                                                    </h6>
                                                    <ul class="list-unstyled small">
                                                        <li><strong>Tréningy:</strong> {{ $reward->trainings_count }}</li>
                                                        <li><strong>Rezervácie:</strong> {{ $reward->total_registrations }} (zrušené: {{ $reward->canceled_registrations }})</li>
                                                        <li><strong>Obsadenosť:</strong> {{ number_format($reward->avg_occupancy, 1) }}%</li>
                                                        <li><strong>Kredity:</strong> {{ $reward->credits_gained }}</li>
                                                        @if($reward->avg_user_rating)
                                                            <li><strong>Hodnotenie:</strong> {{ number_format($reward->avg_user_rating, 2) }}/5 ⭐</li>
                                                        @endif
                                                    </ul>
                                                    <hr class="my-2">
                                                    <ul class="list-unstyled small text-success">
                                                        <li><strong>Základná odmena:</strong> €{{ number_format($reward->base_reward, 2) }}</li>
                                                        <li><strong>Bonus za hodnotenie:</strong> €{{ number_format($reward->rating_bonus, 2) }}</li>
                                                        <li><strong>Bonus za obsadenosť:</strong> €{{ number_format($reward->performance_bonus, 2) }}</li>
                                                    </ul>
                                                    <hr class="my-2">
                                                    <div class="alert alert-success mb-0 p-2">
                                                        <strong>CELKOM: €{{ number_format($reward->total_reward, 2) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <p class="text-muted small">Nie sú k dispozícii detaily odmeny.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Žiadne zmeny.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 alert alert-info">
                <h6 class="alert-heading">📋 Spôsob výpočtu odmien:</h6>
                <ul class="mb-0 small">
                    <li><strong>Základná odmena:</strong> 10% z celkových kreditov, ktoré tréner získal</li>
                    <li><strong>Bonus za hodnotenie:</strong> €50 za každú hviezdu v priemere (max €250 za 5 hviezd)</li>
                    <li><strong>Bonus za obsadenosť:</strong> €2 za každý % priemernej obsadenosti (max €200 za 100%)</li>
                </ul>
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
