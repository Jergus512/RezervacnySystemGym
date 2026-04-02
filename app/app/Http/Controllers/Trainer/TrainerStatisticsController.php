<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\CreditMovement;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainerStatisticsController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $startInput = $request->input('start');
        $endInput   = $request->input('end');

        $start = $startInput ? Carbon::parse($startInput)->startOfDay() : now()->copy()->startOfMonth()->subMonths(5);
        $end   = $endInput ? Carbon::parse($endInput)->endOfDay() : now()->copy()->endOfMonth();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfMonth(), $start->copy()->endOfMonth()];
        }

        // tréner vidí len svoje tréningy
        $trainings = Training::where('created_by_user_id', $user->id)
            ->where('is_active', true)
            ->whereBetween('start_at', [$start, $end])
            ->get();

        $trainingIds = $trainings->pluck('id');

        $stats = [
            'trainings_count'       => $trainings->count(),
            'reservations'          => 0,
            'canceled_reservations' => 0,
            'unique_participants'   => 0,
            'avg_occupancy'         => 0.0,
            'credits_gained'        => 0,
        ];

        if ($trainingIds->isNotEmpty()) {
            $reservationsQuery = DB::table('training_registrations')
                ->whereIn('training_id', $trainingIds);

            // Počítaj všetky rezervácie OKREM zrušených
            $stats['reservations'] = (clone $reservationsQuery)
                ->where('status', '!=', 'canceled')
                ->count();

            $stats['canceled_reservations'] = (clone $reservationsQuery)
                ->where('status', 'canceled')
                ->count();

            // Unique participants - všetky okrem zrušených
            $stats['unique_participants'] = (clone $reservationsQuery)
                ->where('status', '!=', 'canceled')
                ->distinct('user_id')
                ->count('user_id');

            $capacitySum = (int) $trainings->sum('capacity');

            if ($capacitySum > 0) {
                $ratio                  = ($stats['reservations'] * 100.0) / $capacitySum;
                $stats['avg_occupancy'] = (float) number_format($ratio, 1, '.', '');
            }

            // Kredity viazané na tréningy tohto trénera
            $creditsGained = CreditMovement::whereIn('type', ['training_charge', 'training_refund'])
                ->whereIn('training_id', $trainingIds)
                ->sum('amount');

            $stats['credits_gained'] = abs((int) $creditsGained);
        }

        return view('trainer.statistics.index', compact('start', 'end', 'stats', 'trainings'));
    }
}
