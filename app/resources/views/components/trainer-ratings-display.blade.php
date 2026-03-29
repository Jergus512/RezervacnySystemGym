<!-- resources/views/components/trainer-ratings-display.blade.php -->
<div class="trainer-ratings-display">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">📊 Hodnotenia trénerov</h5>

            @php
                $ratings = App\Models\TrainerRating::where('trainer_id', $trainer->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $avgRating = $ratings->avg('rating') ?? 0;
                $ratingCount = $ratings->count();

                // Rozdelenie podľa hviezd
                $distribution = [];
                for ($i = 5; $i >= 1; $i--) {
                    $count = $ratings->where('rating', $i)->count();
                    $distribution[$i] = [
                        'count' => $count,
                        'percentage' => $ratingCount > 0 ? round(($count / $ratingCount) * 100) : 0
                    ];
                }
            @endphp

            <!-- Štatistika -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center">
                            <h3 class="mb-0">
                                <span style="color: #ffc107; font-size: 28px;">★</span>
                                {{ number_format($avgRating, 2) }}
                            </h3>
                            <small class="text-muted">
                                na základe {{ $ratingCount }} hodnotení
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Rozdelenie hviezd -->
                    @foreach($distribution as $stars => $stat)
                        <div class="mb-2">
                            <div class="d-flex align-items-center">
                                <small class="me-2" style="min-width: 30px;">
                                    @for($i = 1; $i <= $stars; $i++)
                                        <span style="color: #ffc107;">★</span>
                                    @endfor
                                </small>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div
                                        class="progress-bar bg-warning"
                                        style="width: {{ $stat['percentage'] }}%"
                                    >
                                        @if($stat['percentage'] > 10)
                                            {{ $stat['percentage'] }}%
                                        @endif
                                    </div>
                                </div>
                                <small class="ms-2">{{ $stat['count'] }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Zoznam hodnotení -->
            @if($ratingCount > 0)
                <h6 class="mb-3">Najnovšie hodnotenia:</h6>
                @foreach($ratings->take(10) as $rating)
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ $rating->user->name }}</h6>
                                    <div style="font-size: 16px; color: #ffc107;">
                                        @for($i = 1; $i <= 5; $i++)
                                            {{ $i <= $rating->rating ? '★' : '☆' }}
                                        @endfor
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ $rating->created_at->format('d.m.Y') }}
                                </small>
                            </div>
                            @if($rating->comment)
                                <p class="mb-0 text-muted">
                                    {{ $rating->comment }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info mb-0">
                    Tento tréner nemá zatiaľ žiadne hodnotenia.
                </div>
            @endif
        </div>
    </div>
</div>
