<?php

namespace App\Services;

use App\Models\TrainerRating;
use App\Models\TrainerReward;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainerRewardService
{
    /**
     * Konfigurácia pre výpočet odmien
     * Tieto hodnoty je možné nastaviť podľa firemných pravidiel
     */
    private const BASE_REWARD_PERCENTAGE = 10; // 10% z kredítov
    private const RATING_BONUS_PER_STAR = 50; // €50 za každú hviezdu v priemere (max 5)
    private const PERFORMANCE_BONUS_PER_OCCUPANCY = 2; // €2 za každý % obsadenosti

    /**
     * Vypočítaj a ulož odmeny pre konkrétneho trénera v danom období
     */
    public function calculateAndSaveReward(User $trainer, Carbon $periodStart, Carbon $periodEnd): TrainerReward
    {
        if (!$trainer->isTrainer()) {
            throw new \InvalidArgumentException('Používateľ nie je tréner.');
        }

        // Získaj štatistiky trénera za período
        $trainings = DB::table('trainings')
            ->where('created_by_user_id', $trainer->id)
            ->whereBetween('start_at', [$periodStart, $periodEnd])
            ->get();

        $trainingIds = $trainings->pluck('id');

        // Základné dáta
        $trainingsCount = $trainings->count();
        $totalCapacity = (int) $trainings->sum('capacity');

        if ($trainingIds->isEmpty()) {
            return TrainerReward::updateOrCreate(
                [
                    'trainer_id' => $trainer->id,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ],
                [
                    'trainings_count' => 0,
                    'total_registrations' => 0,
                    'canceled_registrations' => 0,
                    'avg_occupancy' => 0,
                    'credits_gained' => 0,
                    'avg_user_rating' => null,
                    'base_reward' => 0,
                    'rating_bonus' => 0,
                    'performance_bonus' => 0,
                    'total_reward' => 0,
                    'reward_notes' => 'Tréner nemá žiadne tréningy v danom období.',
                ]
            );
        }

        // Počítaj registrácie a zrušenia
        $registrationsQuery = DB::table('training_registrations')
            ->whereIn('training_id', $trainingIds);

        $totalRegistrations = (clone $registrationsQuery)->count();
        $canceledRegistrations = (clone $registrationsQuery)
            ->where('status', 'canceled')
            ->count();

        $activeRegistrations = $totalRegistrations - $canceledRegistrations;

        // Počítaj obsadenosť
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

        // Počítaj priemerné hodnotenie od zákazníkov
        $avgUserRating = TrainerRating::where('trainer_id', $trainer->id)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->avg('rating');

        // Vypočítaj odmeny
        $baseReward = $this->calculateBaseReward($creditsGained);
        $ratingBonus = $this->calculateRatingBonus($avgUserRating ?? 0);
        $performanceBonus = $this->calculatePerformanceBonus($avgOccupancy);

        $totalReward = $baseReward + $ratingBonus + $performanceBonus;

        // Vytvor poznámky o výpočte
        $rewardNotes = $this->generateRewardNotes(
            $baseReward,
            $ratingBonus,
            $performanceBonus,
            $avgUserRating,
            $avgOccupancy
        );

        // Ulož alebo aktualizuj odmenu
        return TrainerReward::updateOrCreate(
            [
                'trainer_id' => $trainer->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'trainings_count' => $trainingsCount,
                'total_registrations' => $totalRegistrations,
                'canceled_registrations' => $canceledRegistrations,
                'avg_occupancy' => round($avgOccupancy, 2),
                'credits_gained' => $creditsGained,
                'avg_user_rating' => $avgUserRating ? round($avgUserRating, 2) : null,
                'base_reward' => round($baseReward, 2),
                'rating_bonus' => round($ratingBonus, 2),
                'performance_bonus' => round($performanceBonus, 2),
                'total_reward' => round($totalReward, 2),
                'reward_notes' => $rewardNotes,
            ]
        );
    }

    /**
     * Vypočítaj základnú odmenu ako percento z kredítov
     */
    private function calculateBaseReward(int $creditsGained): float
    {
        // Základná odmena = kredity * percento
        return ($creditsGained * self::BASE_REWARD_PERCENTAGE) / 100;
    }

    /**
     * Vypočítaj bonus za hodnotenie od zákazníkov
     * Maximálne 5 hviezd = maximálny bonus
     */
    private function calculateRatingBonus(float $avgRating): float
    {
        if ($avgRating <= 0) {
            return 0;
        }

        // Bonus za priemerné hodnotenie (1-5 hviezd)
        // 5 hviezd = €250 bonus
        return $avgRating * self::RATING_BONUS_PER_STAR;
    }

    /**
     * Vypočítaj bonus za výkon (obsadenosť)
     * Vyššia obsadenosť = vyšší bonus
     */
    private function calculatePerformanceBonus(float $avgOccupancy): float
    {
        if ($avgOccupancy <= 0) {
            return 0;
        }

        // Bonus za každý % obsadenosti
        // Napr. 80% obsadenosť = 80 * €2 = €160 bonus
        return $avgOccupancy * self::PERFORMANCE_BONUS_PER_OCCUPANCY;
    }

    /**
     * Vytvor detailnú poznámku o výpočte
     */
    private function generateRewardNotes(
        float $baseReward,
        float $ratingBonus,
        float $performanceBonus,
        ?float $avgRating,
        float $avgOccupancy
    ): string {
        $notes = "VÝPOČET ODMENY:\n";
        $notes .= "- Základná odmena (10% z kredítov): €" . number_format($baseReward, 2) . "\n";
        $notes .= "- Bonus za hodnotenie (" . ($avgRating ? number_format($avgRating, 1) : 'bez') . " ⭐): €" . number_format($ratingBonus, 2) . "\n";
        $notes .= "- Bonus za obsadenosť (" . number_format($avgOccupancy, 1) . "%): €" . number_format($performanceBonus, 2) . "\n";
        $notes .= "CELKOVÁ ODMENA: €" . number_format($baseReward + $ratingBonus + $performanceBonus, 2);

        return $notes;
    }

    /**
     * Vypočítaj odmeny pre všetkých trénerov v danom období
     */
    public function calculateAllTrainersRewards(Carbon $periodStart, Carbon $periodEnd): void
    {
        $trainers = User::where('is_trainer', true)->get();

        foreach ($trainers as $trainer) {
            $this->calculateAndSaveReward($trainer, $periodStart, $periodEnd);
        }
    }
}
