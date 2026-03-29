@extends('layouts.app')

@section('title', 'Odmeny trénerov - Admin')

@section('content')
<div class="container-fluid px-3 px-md-5 py-5" style="background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);">
    <!-- Header -->
    <div class="mb-5">
        <h1 class="display-5 fw-bold mb-2" style="color: #1a1a1a;">Odmeny trénerov</h1>
        <p class="text-muted fs-6">Top 3 trénerov podľa priemerného hodnotenia za zvolený mesiac</p>
    </div>

    <!-- Výber mesiaca -->
    <form method="GET" class="row g-3 mb-5">
        <div class="col-md-6 col-lg-3">
            <label for="month" class="form-label fw-bold mb-2">Vyberte mesiac:</label>
            <select id="month" name="month" class="form-select form-select-lg" onchange="this.form.submit()">
                @php
                    $selectedDate = \Carbon\Carbon::parse($selectedMonth . '-01');
                    // Zobraz posledných 24 mesiacov
                    for ($i = 23; $i >= 0; $i--) {
                        $date = now()->copy()->subMonths($i)->startOfMonth();
                        $value = $date->format('Y-m');
                        $label = $date->format('F Y'); // napr. "March 2026"
                        $selected = $value === $selectedMonth ? 'selected' : '';
                        echo "<option value=\"$value\" $selected>$label</option>";
                    }
                @endphp
            </select>
        </div>
        <div class="col-md-6 col-lg-3 d-flex align-items-end">
            <a href="{{ route('admin.analytics.trainer-rewards') }}" class="btn btn-outline-secondary w-100">Reset na dnes</a>
        </div>
    </form>

    <!-- Aktuálne obdobie -->
    <div class="alert alert-info mb-5" style="background-color: #e3f2fd; border-color: #2196F3; color: #1565c0;">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill" style="font-size: 24px; margin-right: 15px;"></i>
            <div>
                <strong>Aktuálne obdobie:</strong>
                @php
                    $selectedDate = \Carbon\Carbon::parse($selectedMonth . '-01');
                    echo $selectedDate->format('F Y');
                @endphp
                <br>
                <small>Zobrazujú sa tréneri s najvyšším priemerným hodnotením za tento mesiac</small>
            </div>
        </div>
    </div>

    <!-- Top 3 trénerov -->
    <div class="row g-4 mb-5">
        @forelse($topTrainers as $index => $data)
            @php
                $position = $index + 1;
                $medalEmoji = match($position) {
                    1 => '🥇',
                    2 => '🥈',
                    3 => '🥉',
                    default => '#'
                };
                $badgeColor = match($position) {
                    1 => '#FFD700',
                    2 => '#C0C0C0',
                    3 => '#CD7F32',
                    default => '#999'
                };
                $gradientBg = match($position) {
                    1 => 'linear-gradient(135deg, #FFF8DC 0%, #FFFACD 100%)',
                    2 => 'linear-gradient(135deg, #F5F5F5 0%, #FFFFFF 100%)',
                    3 => 'linear-gradient(135deg, #FFF5EE 0%, #FFE4B5 100%)',
                    default => 'linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%)'
                };
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-lg" style="border: none; background: {{ $gradientBg }}; border-top: 5px solid {{ $badgeColor }};">
                    <!-- Pozícia badge -->
                    <div style="position: absolute; top: -20px; left: 20px; font-size: 48px; line-height: 1;">
                        {{ $medalEmoji }}
                    </div>

                    <div class="card-body pt-5">
                        <!-- Meno a pozícia trénera -->
                        <div class="mb-4">
                            <h5 class="card-title mb-1">{{ $data['trainer']->name }}</h5>
                            <span class="badge rounded-pill" style="background-color: {{ $badgeColor }}; color: #fff; font-size: 14px;">
                                #{{ $position }} miesto
                            </span>
                        </div>

                        <!-- Veľké hodnotenie -->
                        <div class="mb-4 p-4 rounded-3" style="background-color: rgba(255,255,255,0.7); text-align: center;">
                            <div style="font-size: 48px; color: {{ $badgeColor }}; margin-bottom: 10px;">
                                ★
                            </div>
                            <div style="font-size: 36px; font-weight: bold; color: #333; margin-bottom: 5px;">
                                {{ $data['avg_rating'] }}
                            </div>
                            <div class="text-muted small">
                                priemerné hodnotenie
                            </div>
                        </div>

                        <!-- Počet hodnotení -->
                        <div class="d-flex justify-content-around mb-4">
                            <div class="text-center">
                                <div style="font-size: 20px; font-weight: bold; color: {{ $badgeColor }};">
                                    {{ $data['rating_count'] }}
                                </div>
                                <div class="text-muted small">hodnotení</div>
                            </div>
                            <div class="text-center">
                                <div style="font-size: 20px;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span style="color: {{ $i <= round($data['avg_rating']) ? $badgeColor : '#ddd' }};">★</span>
                                    @endfor
                                </div>
                                <div class="text-muted small">hviezdy</div>
                            </div>
                        </div>

                        <!-- Bonusový kód -->
                        <div class="alert mb-0" style="background-color: #e8f5e9; border-color: #4CAF50; color: #2e7d32; text-align: center;">
                            <strong style="display: block; margin-bottom: 8px;">Bonusový kód</strong>
                            <code style="font-size: 13px; background: white; padding: 8px 12px; border-radius: 6px; font-weight: bold;">
                                BONUS_{{ $position }}_{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('mY') }}
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning" style="background-color: #fff3e0; border-color: #ff9800; color: #e65100;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Žiadne dáta</strong> - V zvolenom mesiaci trénerov nemajú žiadne hodnotenia.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Históri odmien -->
    @if(count($rewardsHistory) > 0)
        <div class="card shadow-sm">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                <h5 class="card-title mb-0" style="color: #1a1a1a;">
                    <i class="bi bi-clock-history" style="margin-right: 8px;"></i>
                    História odmien (posledných 12 mesiacov)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th style="color: #666;">Mesiac</th>
                            <th style="color: #666;">Pozícia</th>
                            <th style="color: #666;">Tréner</th>
                            <th style="color: #666;">Priemerné hodnotenie</th>
                            <th style="color: #666;">Počet hodnotení</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $groupedByMonth = [];
                            foreach ($rewardsHistory as $reward) {
                                if (!isset($groupedByMonth[$reward['month']])) {
                                    $groupedByMonth[$reward['month']] = [];
                                }
                                $groupedByMonth[$reward['month']][] = $reward;
                            }
                        @endphp
                        @foreach($groupedByMonth as $month => $trainers)
                            @foreach($trainers as $index => $reward)
                                @php
                                    $position = $index + 1;
                                    $badgeColor = match($position) {
                                        1 => '#FFD700',
                                        2 => '#C0C0C0',
                                        3 => '#CD7F32',
                                        default => '#999'
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $month }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" style="background-color: {{ $badgeColor }}; color: #fff;">
                                            #{{ $position }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $reward['trainer']->name }}</strong>
                                    </td>
                                    <td>
                                        <span style="color: {{ $badgeColor }}; font-weight: bold;">
                                            ★ {{ $reward['avg_rating'] }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $reward['rating_count'] }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
    }
    .form-select-lg {
        padding: 12px 15px;
        font-size: 16px;
        border: 2px solid #e9ecef;
        transition: all 0.2s ease;
    }
    .form-select-lg:focus {
        border-color: #ff9800;
        box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
