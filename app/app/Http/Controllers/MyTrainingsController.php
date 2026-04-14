<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class MyTrainingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->isRegularUser()) {
            abort(403);
        }

        // Nadchádzajúce tréningy - aktívne registrácie na budúcich tréningoch
        $upcomingTrainings = $user->trainings()
            ->where('start_at', '>=', now())
            ->where('is_active', true)
            ->orderBy('start_at')
            ->with('creator', 'trainingType')
            ->get();

        // Minulé tréningy - všetky registrácie na minulých tréningoch (aj zrušené)
        // Načítame tréningy priamo z DB bez globálneho scope na registrácie
        $pastTrainings = Training::query()
            ->whereIn('id', function ($query) use ($user) {
                $query->select('training_id')
                    ->from('training_registrations')
                    ->where('user_id', $user->id);
            })
            ->where('start_at', '<', now())
            ->orderBy('start_at', 'desc')
            ->with('creator', 'trainingType')
            ->get();

        return view('training.my-trainings', [
            'upcomingTrainings' => $upcomingTrainings,
            'pastTrainings' => $pastTrainings,
        ]);
    }
}
