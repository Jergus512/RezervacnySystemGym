<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditMovement;
use App\Models\Training;
use App\Models\TrainingType;
use App\Models\TrainerReward;
use App\Models\User;
use App\Services\TrainerStatisticsService;
use App\Services\TrainerRewardService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected TrainerStatisticsService $trainerStatsService;
    protected TrainerRewardService $trainerRewardService;

    public function __construct(TrainerStatisticsService $trainerStatsService, TrainerRewardService $trainerRewardService)
    {
        $this->trainerStatsService = $trainerStatsService;
        $this->trainerRewardService = $trainerRewardService;
    }

    public function index(Request $request): View
    {
        $startInput = $request->input('start');
        $endInput   = $request->input('end');

        $start = $startInput ? Carbon::parse($startInput)->startOfDay() : now()->copy()->startOfMonth()->subMonths(5);
        $end   = $endInput ? Carbon::parse($endInput)->endOfDay() : now()->copy()->endOfMonth();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfMonth(), $start->copy()->endOfMonth()];
        }

        $monthlyStats       = $this->getMonthlyStats($start, $end);
        $reservationStats   = $this->getReservationStats($start, $end);
        $financialOverview  = $this->getFinancialOverview($start, $end);
        $trainerPerformance = $this->getTrainerPerformance($start, $end);
        $trainerRewards     = $this->getTrainerRewards($start, $end);
        $trainingPopularity = $this->getTrainingPopularity($start, $end);

        return view('admin.analytics.index', compact(
            'start',
            'end',
            'monthlyStats',
            'reservationStats',
            'financialOverview',
            'trainerPerformance',
            'trainerRewards',
            'trainingPopularity',
        ));
    }

    /**
     * Stránka s výpočtom odmien trénérov podľa hodnotenia za konkrétny mesiac
     */
    public function trainerRewards(Request $request): View
    {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $selectedMonth = $monthInput;

        try {
            $selectedDate = Carbon::parse($monthInput . '-01');
        } catch (\Exception $e) {
            $selectedDate = now();
            $monthInput = $selectedDate->format('Y-m');
        }

        $monthStart = $selectedDate->copy()->startOfMonth();
        $monthEnd = $selectedDate->copy()->endOfMonth();

        // Získaj top 3 trénérov za vybraný mesiac
        $topTrainers = $this->getTopTrainersByRatingForMonth($monthStart, $monthEnd);

        // Získaj históriu posledných 12 mesiacov
        $rewardsHistory = $this->getTrainerRewardsHistory(12);

        return view('admin.analytics.trainer-rewards', compact(
            'selectedMonth',
            'topTrainers',
            'rewardsHistory',
        ));
    }

    /**
     * Výpočet top 3 trénérov podľa priemerného hodnotenia za konkrétny mesiac
     */
    protected function getTopTrainersByRatingForMonth(Carbon $start, Carbon $end): array
    {
        $trainers = User::where('is_trainer', true)->get();
        $rows = [];

        foreach ($trainers as $trainer) {
            // Získaj všetky hodnotenia pre trénera v danom časovom období
            $ratings = DB::table('trainer_ratings')
                ->where('trainer_id', $trainer->id)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            if ($ratings->isEmpty()) {
                continue; // Preskočiť trénerov bez hodnotení v danom období
            }

            $avgRating = $ratings->avg('rating');
            $ratingCount = $ratings->count();

            $rows[] = [
                'trainer' => $trainer,
                'avg_rating' => (float) number_format($avgRating, 2, '.', ''),
                'rating_count' => $ratingCount,
            ];
        }

        // Zoradiť podľa priemerného hodnotenia (descending)
        usort($rows, static function (array $a, array $b): int {
            return $b['avg_rating'] <=> $a['avg_rating'];
        });

        // Vrátiť len top 3
        return array_slice($rows, 0, 3);
    }

    /**
     * História odmien za posledných N mesiacov
     */
    protected function getTrainerRewardsHistory(int $months = 12): array
    {
        $result = [];
        $trainers = User::where('is_trainer', true)->get();

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = now()->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $topThree = $this->getTopTrainersByRatingForMonth($monthStart, $monthEnd);

            foreach ($topThree as $data) {
                $result[] = [
                    'month' => $monthStart->format('m/Y'),
                    'trainer' => $data['trainer'],
                    'avg_rating' => $data['avg_rating'],
                    'rating_count' => $data['rating_count'],
                ];
            }
        }

        return $result;
    }

    protected function getMonthlyStats(Carbon $start, Carbon $end): array
    {
        $period = CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->endOfMonth());

        $reservationsPerMonth = DB::table('training_registrations')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as registrations')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->pluck('registrations', 'ym');

        // Count cancellations per month based on when the registration was marked as canceled
        $cancellationsPerMonth = DB::table('training_registrations')
            ->selectRaw('DATE_FORMAT(updated_at, "%Y-%m") as ym, COUNT(*) as cancellations')
            ->where('status', 'canceled')
            ->whereBetween('updated_at', [$start, $end])
            ->groupBy('ym')
            ->pluck('cancellations', 'ym');

        // Priemerná obsadenosť za mesiac – MySQL agregácia
        $occupancyRows = DB::table('trainings')
            ->leftJoin('training_registrations', 'trainings.id', '=', 'training_registrations.training_id')
            ->selectRaw('trainings.id as training_id, DATE_FORMAT(trainings.start_at, "%Y-%m") as ym, trainings.capacity, COUNT(training_registrations.id) as registrations')
            ->whereBetween('trainings.start_at', [$start, $end])
            ->groupBy('trainings.id', 'ym', 'trainings.capacity')
            ->get();

        $perMonth = [];
        foreach ($occupancyRows as $row) {
            if (! $row->capacity || $row->capacity <= 0) {
                continue;
            }
            $occ = ($row->registrations * 100.0) / (float) $row->capacity;
            $ym  = $row->ym;
            if (! isset($perMonth[$ym])) {
                $perMonth[$ym] = ['sum' => 0.0, 'count' => 0];
            }
            $perMonth[$ym]['sum']   += $occ;
            $perMonth[$ym]['count'] += 1;
        }

        $avgOccupancyPerMonth = [];
        foreach ($perMonth as $ym => $data) {
            if ($data['count'] > 0) {
                $avgOccupancyPerMonth[$ym] = $data['sum'] / $data['count'];
            }
        }

        $result = [];
        foreach ($period as $month) {
            $key = $month->format('Y-m');
            $avg = isset($avgOccupancyPerMonth[$key]) ? (float) $avgOccupancyPerMonth[$key] : 0.0;
            $result[] = [
                'label'         => $month->format('m/Y'),
                'registrations' => (int) ($reservationsPerMonth[$key] ?? 0),
                'cancellations' => (int) ($cancellationsPerMonth[$key] ?? 0),
                'avg_occupancy' => (float) number_format($avg, 1, '.', ''),
            ];
        }

        return $result;
    }

    protected function getReservationStats(Carbon $start, Carbon $end): array
    {
        $baseQuery = DB::table('training_registrations')
            ->join('trainings', 'training_registrations.training_id', '=', 'trainings.id')
            ->whereBetween('trainings.start_at', [$start, $end]);

        $totalReservations = (clone $baseQuery)->count();

        // Canceled = záznamy označené status = 'canceled'
        $canceled = (clone $baseQuery)
            ->where('training_registrations.status', 'canceled')
            ->count();

        $dayOfWeek = (clone $baseQuery)
            ->selectRaw('DAYOFWEEK(trainings.start_at) as dow, COUNT(*) as c')
            ->groupBy('dow')
            ->orderBy('c', 'desc')
            ->get();

        $timeSlots = (clone $baseQuery)
            ->selectRaw('LPAD(HOUR(trainings.start_at), 2, "0") as slot, COUNT(*) as c')
            ->groupBy('slot')
            ->orderBy('c', 'desc')
            ->get();

        return [
            'total_reservations'    => $totalReservations,
            'canceled_reservations' => $canceled,
            'days_of_week'          => $dayOfWeek,
            'time_slots'            => $timeSlots,
        ];
    }

    protected function getFinancialOverview(Carbon $start, Carbon $end): array
    {
        $creditsSold = CreditMovement::where('type', 'reception_add')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        // Použité kredity na rezervácie (negatívne pohyby – odčítavajú sa z účtu)
        $creditsCharged = CreditMovement::where('type', 'training_charge')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        // Kredit vrátený klientom (napr. pri zrušení tréningu – pridáva sa späť na účet)
        $creditsRefunded = CreditMovement::where('type', 'training_refund')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        // Čisté použitie = koľko kreditov reálne "zmizlo" z účtov kvôli tréningom
        // Obidva pohyby sú v DB záporné čísla, takže sčítame a potom berieme absolútnu hodnotu
        $creditsUsedNet = (int) $creditsCharged + (int) $creditsRefunded;

        $totalRemaining = (int) User::sum('credits');

        return [
            'sold'          => (int) $creditsSold,
            // absolútna hodnota iba charges (bez vrátení)
            'used'          => abs((int) $creditsCharged),
            // čisté použitie po započítaní refundov
            'used_net'      => abs($creditsUsedNet),
            'refunded'      => abs((int) $creditsRefunded),
            'remaining'     => $totalRemaining,
        ];
    }

    protected function getTrainerPerformance(Carbon $start, Carbon $end): array
    {
        $trainers = User::where('is_trainer', true)->get();

        $rows = [];
        foreach ($trainers as $trainer) {
            $trainings = Training::where('created_by_user_id', $trainer->id)
                ->whereBetween('start_at', [$start, $end])
                ->get();

            $trainingIds = $trainings->pluck('id');
            if ($trainingIds->isEmpty()) {
                $rows[] = [
                    'trainer'                => $trainer,
                    'trainings_count'        => 0,
                    'reservations'           => 0,
                    'avg_occupancy'          => 0.0,
                    'rating'                 => null,
                    'credits_gained'         => 0,
                    'canceled_reservations'  => 0,
                ];
                continue;
            }

            $reservationsQuery = DB::table('training_registrations')
                ->whereIn('training_id', $trainingIds);

            $reservationsCount = (clone $reservationsQuery)->count();

            $canceledReservations = (clone $reservationsQuery)
                ->where('status', 'canceled')
                ->count();

            // Kredity získané trénerom - suma všetkých training_charge pohybov pre jeho tréningy
            // Sčítame aj training_refund pohyby (vrátené kredity - KLADNÉ čísla)
            $creditsCharged = (int) DB::table('credit_movements')
                ->whereIn('training_id', $trainingIds)
                ->where('type', 'training_charge')
                ->sum('amount');

            $creditsRefunded = (int) DB::table('credit_movements')
                ->whereIn('training_id', $trainingIds)
                ->where('type', 'training_refund')
                ->sum('amount');

            // charges sú záporné (napr. -100), refunds sú kladné (napr. +30)
            // Čisté = charges + refunds (napr. -100 + 30 = -70, abs = 70)
            $creditsGained = abs($creditsCharged + $creditsRefunded);

            $capacitySum = (int) $trainings->sum('capacity');

            $avgOccupancy = 0.0;
            if ($capacitySum > 0) {
                $ratio        = ($reservationsCount * 100.0) / $capacitySum;
                $avgOccupancy = (float) number_format($ratio, 1, '.', '');
            }

            $rows[] = [
                'trainer'               => $trainer,
                'trainings_count'       => $trainings->count(),
                'reservations'          => $reservationsCount,
                'avg_occupancy'         => $avgOccupancy,
                'rating'                => null,
                'credits_gained'        => $creditsGained,
                'canceled_reservations' => $canceledReservations,
            ];
        }

        return $rows;
    }

    protected function getTrainerRewards(Carbon $start, Carbon $end): array
    {
        $trainers = User::where('is_trainer', true)->get();

        $rows = [];
        foreach ($trainers as $trainer) {
            $rewards = TrainerReward::where('trainer_id', $trainer->id)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $totalAmount = $rewards->sum('amount');

            $rows[] = [
                'trainer'     => $trainer,
                'rewards_count' => $rewards->count(),
                'total_amount'  => $totalAmount,
            ];
        }

        return $rows;
    }

    protected function getTrainingPopularity(Carbon $start, Carbon $end): array
    {
        $byType = Training::select('training_type_id', DB::raw('COUNT(*) as trainings_count'))
            ->whereBetween('start_at', [$start, $end])
            ->groupBy('training_type_id')
            ->get();

        if ($byType->isEmpty()) {
            return [];
        }

        $reservationsByType = DB::table('training_registrations')
            ->join('trainings', 'training_registrations.training_id', '=', 'trainings.id')
            ->select('trainings.training_type_id', DB::raw('COUNT(*) as reservations_count'))
            ->whereBetween('trainings.start_at', [$start, $end])
            ->groupBy('trainings.training_type_id')
            ->pluck('reservations_count', 'training_type_id');

        $capacityByType = Training::select('training_type_id', DB::raw('SUM(capacity) as capacity_sum'))
            ->whereBetween('start_at', [$start, $end])
            ->groupBy('training_type_id')
            ->pluck('capacity_sum', 'training_type_id');

        $types = TrainingType::whereIn('id', $byType->pluck('training_type_id')->filter())
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($byType as $row) {
            $typeId      = $row->training_type_id;
            $reservations = (int) ($reservationsByType[$typeId] ?? 0);
            $capacity     = (int) ($capacityByType[$typeId] ?? 0);

            $avgOccupancy = 0.0;
            if ($capacity > 0) {
                $ratio       = ($reservations * 100.0) / $capacity;
                $avgOccupancy = (float) number_format($ratio, 1, '.', '');
            }

            $result[] = [
                'type'             => $types[$typeId] ?? null,
                'trainings_count'  => (int) $row->trainings_count,
                'reservations'     => $reservations,
                'avg_occupancy'    => $avgOccupancy,
            ];
        }

        usort($result, static function (array $a, array $b): int {
            return $b['reservations'] <=> $a['reservations'];
        });

        return $result;
    }
}
