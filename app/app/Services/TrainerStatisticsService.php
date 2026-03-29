<?php

namespace App\Services;

use App\Models\CreditMovement;
use App\Models\Training;
use App\Models\TrainerStatistics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainerStatisticsService
{
    /**
     * Vypočítaj a ulož štatistiky pre konkrétneho trénera v danom období
     */
    public function calculateAndSaveStatistics(User $trainer, Carbon $periodStart, Carbon $periodEnd): TrainerStatistics
    {
        if (!$trainer->isTrainer()) {
            throw new \InvalidArgumentException('Používateľ nie je tréner.');
        }

        // Získaj všetky tréningy trénera v danom období
        $trainings = Training::where('created_by_user_id', $trainer->id)
            ->whereBetween('start_at', [$periodStart, $periodEnd])
            ->get();

        $trainingIds = $trainings->pluck('id');

        // Ak nie sú žiadne tréningy, vytvor záznam s nulami
        if ($trainingIds->isEmpty()) {
            return TrainerStatistics::updateOrCreate(
                [
                    'user_id' => $trainer->id,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ],
                [
                    'trainings_count' => 0,
                    'total_participants' => 0,
                    'unique_participants' => 0,
                    'total_registrations' => 0,
                    'canceled_registrations' => 0,
                    'avg_occupancy' => 0,
                    'credits_gained' => 0,
                    'total_capacity' => 0,
                    'performance_rating' => null,
                    'loyalty_score' => null,
                    'cancellation_rate' => 0,
                ]
            );
        }

        // Počítaj registrácie
        $registrationsQuery = DB::table('training_registrations')
            ->whereIn('training_id', $trainingIds);

        $totalRegistrations = (clone $registrationsQuery)->count();
        $canceledRegistrations = (clone $registrationsQuery)
            ->where('status', 'canceled')
            ->count();

        $activeRegistrations = $totalRegistrations - $canceledRegistrations;

        // Počítaj unikátnych účastníkov
        $uniqueParticipants = (clone $registrationsQuery)
            ->distinct('user_id')
            ->count('user_id');

        // Počítaj kapacitu a obsadenosť
        $totalCapacity = (int) $trainings->sum('capacity');
        $avgOccupancy = 0.0;
        if ($totalCapacity > 0) {
            $avgOccupancy = ($activeRegistrations * 100.0) / $totalCapacity;
        }

        // Počítaj získané kredity
        $creditsGained = (int) DB::table('credit_movements')
            ->whereIn('training_id', $trainingIds)
            ->where('type', 'training_charge')
            ->sum('amount');
        $creditsGained = abs($creditsGained);

        // Počítaj percento zrušení
        $cancellationRate = 0;
        if ($totalRegistrations > 0) {
            $cancellationRate = (int) round(($canceledRegistrations * 100) / $totalRegistrations);
        }

        // Počítaj lojalitu (opakovateľnosť - podiel unikátnych vs. celkového)
        $loyaltyScore = 0.0;
        if ($uniqueParticipants > 0) {
            $loyaltyScore = ($uniqueParticipants / $totalRegistrations) * 100;
            $loyaltyScore = min(100, $loyaltyScore); // Max 100%
        }

        // Počítaj performance rating (1-5 hviezd)
        $performanceRating = $this->calculatePerformanceRating(
            $avgOccupancy,
            $cancellationRate,
            $loyaltyScore,
            $trainings->count()
        );

        // Ulož alebo aktualizuj štatistiky
        return TrainerStatistics::updateOrCreate(
            [
                'user_id' => $trainer->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'trainings_count' => $trainings->count(),
                'total_participants' => $totalRegistrations,
                'unique_participants' => $uniqueParticipants,
                'total_registrations' => $totalRegistrations,
                'canceled_registrations' => $canceledRegistrations,
                'avg_occupancy' => round($avgOccupancy, 2),
                'credits_gained' => $creditsGained,
                'total_capacity' => $totalCapacity,
                'performance_rating' => round($performanceRating, 2),
                'loyalty_score' => round($loyaltyScore, 2),
                'cancellation_rate' => $cancellationRate,
            ]
        );
    }

    /**
     * Vypočítaj performance rating (1-5 hviezd) na základe viacerých faktorov
     *
     * Faktor 1: Obsadenosť (30%) - čím vyššia, tým lepšie
     * Faktor 2: Percento zrušení (30%) - čím nižšie, tým lepšie
     * Faktor 3: Lojalita (20%) - opakovateľnosť účastníkov
     * Faktor 4: Počet tréningov (20%) - aktívnosť trénera
     */
    private function calculatePerformanceRating(
        float $occupancy,
        int $cancellationRate,
        float $loyaltyScore,
        int $trainingsCount
    ): float {
        // Ak tréner nemá žiadne tréningy, daj mu 0
        if ($trainingsCount === 0) {
            return 0.0;
        }

        // Faktor 1: Obsadenosť (0-100%) mapujeme na 0-5
        $occupancyScore = ($occupancy / 100) * 5;

        // Faktor 2: Inverzia zrušení (0% zrušení = 5 hviezd, 100% = 0 hviezd)
        $cancellationScore = ((100 - $cancellationRate) / 100) * 5;

        // Faktor 3: Lojalita (0-100%) mapujeme na 0-5
        $loyaltyScoreNormalized = ($loyaltyScore / 100) * 5;

        // Faktor 4: Počet tréningov - bonus ak má viac ako 5 tréningov
        $activityScore = min(5, ($trainingsCount / 5) * 5);

        // Priemerná hodnota so váhami: 30% + 30% + 20% + 20%
        $finalRating = (
            ($occupancyScore * 0.30) +
            ($cancellationScore * 0.30) +
            ($loyaltyScoreNormalized * 0.20) +
            ($activityScore * 0.20)
        );

        return max(0, min(5, $finalRating));
    }

    /**
     * Vypočítaj štatistiky pre všetkých trénerov v danom období
     */
    public function calculateAllTrainersStatistics(Carbon $periodStart, Carbon $periodEnd): void
    {
        $trainers = User::where('is_trainer', true)->get();

        foreach ($trainers as $trainer) {
            $this->calculateAndSaveStatistics($trainer, $periodStart, $periodEnd);
        }
    }

    /**
     * Vrať aktuálne štatistiky pre trénera (posledný vypočítaný rekord)
     */
    public function getLatestStatistics(User $trainer): ?TrainerStatistics
    {
        return TrainerStatistics::where('user_id', $trainer->id)
            ->latest('period_end')
            ->first();
    }
}
