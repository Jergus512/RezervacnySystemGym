<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditMovement;
use App\Models\Training;
use App\Models\TrainingType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
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
        $trainingPopularity = $this->getTrainingPopularity($start, $end);

        return view('admin.analytics.index', compact(
            'start',
            'end',
            'monthlyStats',
            'reservationStats',
            'financialOverview',
            'trainerPerformance',
            'trainingPopularity',
        ));
    }

    protected function getMonthlyStats(Carbon $start, Carbon $end): array
    {
        $period = CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->endOfMonth());

        $reservationsPerMonth = DB::table('training_registrations')
            ->selectRaw('strftime("%Y-%m", created_at) as ym, count(*) as registrations')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->pluck('registrations', 'ym');

        // Jednoduchšie: ak máš v tabuľke stĺpec status pre zrušenie, berieme ich podľa updated_at
        $cancellationsPerMonth = DB::table('training_registrations')
            ->selectRaw('strftime("%Y-%m", updated_at) as ym, count(*) as cancellations')
            ->where('status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end])
            ->groupBy('ym')
            ->pluck('cancellations', 'ym');

        // Priemerná obsadenosť za mesiac – rátame najprv obsadenosť pre každý tréning a potom spriemerujeme
        $occupancyRows = DB::table('trainings')
            ->leftJoin('training_registrations', 'trainings.id', '=', 'training_registrations.training_id')
            ->selectRaw('trainings.id as training_id, strftime("%Y-%m", trainings.start_at) as ym, trainings.capacity, count(training_registrations.id) as registrations')
            ->whereBetween('trainings.start_at', [$start, $end])
            ->groupBy('trainings.id', 'ym')
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
        $canceled          = (clone $baseQuery)->where('training_registrations.status', 'cancelled')->count();

        $dayOfWeek = (clone $baseQuery)
            ->selectRaw('strftime("%w", trainings.start_at) as dow, count(*) as c')
            ->groupBy('dow')
            ->orderBy('c', 'desc')
            ->get();

        $timeSlots = (clone $baseQuery)
            ->selectRaw('strftime("%H:00", trainings.start_at) as slot, count(*) as c')
            ->groupBy('slot')
            ->orderBy('c', 'desc')
            ->get();

        return [
            'total_reservations'   => $totalReservations,
            'canceled_reservations'=> $canceled,
            'days_of_week'         => $dayOfWeek,
            'time_slots'           => $timeSlots,
        ];
    }

    protected function getFinancialOverview(Carbon $start, Carbon $end): array
    {
        $creditsSold = CreditMovement::where('type', 'reception_add')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        // V tvojom systéme sú pohyby pravdepodobne: +reception_add, -training_charge, +training_refund
        $creditsUsed = CreditMovement::where('type', 'training_charge')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $creditsRefunded = CreditMovement::where('type', 'training_refund')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $totalUsedNet = (int) $creditsUsed + (int) $creditsRefunded;

        $totalRemaining = (int) User::sum('credits');

        return [
            'sold'      => (int) $creditsSold,
            'used'      => $totalUsedNet,
            'remaining' => $totalRemaining,
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
                    'trainer'         => $trainer,
                    'trainings_count' => 0,
                    'reservations'    => 0,
                    'avg_occupancy'   => 0.0,
                    'rating'          => null,
                ];
                continue;
            }

            $reservationsCount = DB::table('training_registrations')
                ->whereIn('training_id', $trainingIds)
                ->where('status', '!=', 'cancelled')
                ->count();

            $capacitySum = (int) $trainings->sum('capacity');

            $avgOccupancy = 0.0;
            if ($capacitySum > 0) {
                $ratio       = ($reservationsCount * 100.0) / $capacitySum;
                $avgOccupancy = (float) number_format($ratio, 1, '.', '');
            }

            $rows[] = [
                'trainer'         => $trainer,
                'trainings_count' => $trainings->count(),
                'reservations'    => $reservationsCount,
                'avg_occupancy'   => $avgOccupancy,
                'rating'          => null, // zatiaľ nemáš ratings tabuľku
            ];
        }

        return $rows;
    }

    protected function getTrainingPopularity(Carbon $start, Carbon $end): array
    {
        $byType = Training::select('training_type_id', DB::raw('count(*) as trainings_count'))
            ->whereBetween('start_at', [$start, $end])
            ->groupBy('training_type_id')
            ->get();

        if ($byType->isEmpty()) {
            return [];
        }

        $reservationsByType = DB::table('training_registrations')
            ->join('trainings', 'training_registrations.training_id', '=', 'trainings.id')
            ->select('trainings.training_type_id', DB::raw('count(*) as reservations_count'))
            ->whereBetween('trainings.start_at', [$start, $end])
            ->groupBy('trainings.training_type_id')
            ->pluck('reservations_count', 'training_type_id');

        $capacityByType = Training::select('training_type_id', DB::raw('sum(capacity) as capacity_sum'))
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
