<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserCreditsController extends Controller
{
    /**
     * Show authenticated user's credit change history.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (! $user || ! $user->isRegularUser()) {
            abort(403);
        }

        $movements = $user->creditMovements()
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $balance = (int) ($user->credits ?? 0);
        $totalAdded = $movements->where('amount', '>', 0)->sum('amount');
        $totalSubtracted = $movements->where('amount', '<', 0)->sum('amount');

        return view('user.credits-history', [
            'user' => $user,
            'movements' => $movements,
            'balance' => $balance,
            'totalAdded' => $totalAdded,
            'totalSubtracted' => $totalSubtracted,
        ]);
    }

    /**
     * Show authenticated user's completed trainings overview & statistics.
     */
    public function trainings(Request $request): View
    {
        $user = Auth::user();

        // Ensure only regular users can access their training statistics
        if (! $user || ! $user->isRegularUser()) {
            abort(403);
        }

        // Date range filters (default: last 90 days)
        $startDate = $request->input('from');
        $endDate = $request->input('to');

        if (! $startDate && ! $endDate) {
            $startDate = now()->subDays(90)->toDateString();
            $endDate = now()->toDateString();
        }

        $trainingsQuery = $user->trainings()
            ->with(['trainingType', 'creator'])
            ->orderByDesc('start_at');

        if ($startDate) {
            $trainingsQuery->whereDate('start_at', '>=', $startDate);
        }

        if ($endDate) {
            $trainingsQuery->whereDate('start_at', '<=', $endDate);
        }

        $trainings = $trainingsQuery->get();

        // Basic aggregates
        $totalTrainings = $trainings->count();
        $totalCredits = $trainings->sum('price');

        // Group by training type
        $byType = $trainings
            ->groupBy(fn (Training $t) => optional($t->trainingType)->name ?? 'Nezaradené')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'credits' => $group->sum('price'),
                ];
            })
            ->sortByDesc('count');

        // Group by trainer (creator)
        $byTrainer = $trainings
            ->groupBy(function (Training $t) {
                $creator = $t->creator;

                return $creator ? ($creator->name ?? ("Tréner #{$creator->id}")) : 'Neznámy tréner';
            })
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'credits' => $group->sum('price'),
                ];
            })
            ->sortByDesc('count');

        return view('user.training-history', [
            'user' => $user,
            'trainings' => $trainings,
            'totalTrainings' => $totalTrainings,
            'totalCredits' => $totalCredits,
            'byType' => $byType,
            'byTrainer' => $byTrainer,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
