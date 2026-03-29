<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyTrainingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->isRegularUser()) {
            abort(403);
        }

        // Nadchádzajúce tréningy - ktoré majú rezerváciu
        $upcomingTrainings = $user->trainings()
            ->where('start_at', '>=', now())
            ->where('is_active', true)
            ->orderBy('start_at')
            ->with('creator', 'trainingType')
            ->get();

        // Minulé tréningy - ktoré už prebehli
        $pastTrainings = $user->trainings()
            ->where('start_at', '<', now())
            ->where('is_active', true)
            ->orderBy('start_at', 'desc')
            ->with('creator', 'trainingType')
            ->get();

        return view('training.my-trainings', [
            'upcomingTrainings' => $upcomingTrainings,
            'pastTrainings' => $pastTrainings,
        ]);
    }
}
