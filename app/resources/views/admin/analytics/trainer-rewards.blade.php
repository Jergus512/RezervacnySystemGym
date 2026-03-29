@extends('layouts.app')

@section('title', 'Odmeny trénerov - Admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Odmeny trénerov</h1>
            <div class="text-muted small">Výpočet top 3 trénérov podľa priemerného hodnotenia za mesiac</div>
        </div>
    </div>

    <!-- Výber mesiaca -->
    <form method="GET" class="card mb-4 p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="month" class="form-label mb-1">Vyberte mesiac</label>
                <input type="month" id="month" name="month" value="{{ $selectedMonth }}" class="form-control">
            </div>
            <div class="col-md-8 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Zobraziť</button>
                <a href="{{ route('admin.analytics.trainer-rewards') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- Top 3 trénerov -->
    <div class="row g-4">
        @forelse($topTrainers as $index => $data)
            @php
                $position = $index + 1;
                $badgeColor = match($position) {
                    1 => '#FFD700', // Zlatá
                    2 => '#C0C0C0', // Strieborná
                    3 => '#CD7F32', // Bronzová
                    default => '#999'
                };
                $badgeBg = match($position) {
                    1 => '#FFF8DC',
                    2 => '#F5F5F5',
                    3 => '#FFF5EE',
                    default => '#f9f9f9'
                };
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100" style="border: 3px solid {{ $badgeColor }}; background: {{ $badgeBg }};">
                    <!-- Pozícia badge -->
                    <div style="position: absolute; top: -15px; left: 20px; background: {{ $badgeColor }}; color: #333; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        {{ $position }}.
                    </div>

                    <div class="card-body pt-5">
                        <!-- Meno trénera -->
                        <h5 class="card-title mb-3">{{ $data['trainer']->name }}</h5>

                        <!-- Štatistika hodnotenia -->
                        <div class="mb-4">
                            <div class="d-flex align-items-baseline mb-2">
                                <span style="font-size: 32px; color: {{ $badgeColor }}; margin-right: 10px;">★</span>
                                <span style="font-size: 28px; font-weight: bold; color: #333;">{{ $data['avg_rating'] }}</span>
                            </div>
                            <div class="text-muted small">
                                na základe {{ $data['rating_count'] }} hodnotení
                            </div>
                        </div>

                        <!-- Detaily -->
                        <div class="bg-white p-3 rounded">
                            <div class="mb-2">
                                <span class="text-muted small">Priemerné hodnotenie:</span>
                                <div class="fw-bold" style="color: {{ $badgeColor }};">
                                    @for($i = 1; $i <= 5; $i++)
                                        {{ $i <= round($data['avg_rating']) ? '★' : '☆' }}
                                    @endfor
                                </div>
                            </div>
                            <div>
                                <span class="text-muted small">Počet hodnotení:</span>
                                <div class="fw-bold">{{ $data['rating_count'] }}</div>
                            </div>
                        </div>

                        <!-- Odmeňovanie -->
                        <div class="mt-4 pt-3" style="border-top: 2px solid {{ $badgeColor }};">
                            <div class="alert mb-0" style="background-color: #e8f5e9; border-color: #4CAF50; color: #2e7d32;">
                                <strong>Bonusový kód:</strong><br>
                                <code style="font-size: 12px; background: white; padding: 5px 8px; border-radius: 4px;">BONUS_{{ $position }}_{{ now()->format('mY') }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <strong>Žiadne dáta</strong> - V zvolenom mesiaci nemajú trénerov žiadne hodnotenia.
                </div>
            </div>
        @endforelse
    </div>

    <!-- História odmien -->
    @if($rewardsHistory->isNotEmpty())
        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title mb-3">História odmien</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Mesiac</th>
                            <th>Tréner</th>
                            <th>Priemerné hodnotenie</th>
                            <th>Počet hodnotení</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rewardsHistory as $reward)
                            <tr>
                                <td>{{ $reward['month'] }}</td>
                                <td>{{ $reward['trainer']->name }}</td>
                                <td>
                                    <span style="color: #ff9800;">★</span>
                                    {{ $reward['avg_rating'] }}
                                </td>
                                <td>{{ $reward['rating_count'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <style>
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }
    </style>
@endsection
