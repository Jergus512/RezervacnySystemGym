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

        // Upcoming trainings that the user is registered for ("zakúpené")
        $trainings = $user->trainings()
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->get();

        return view('training.my-trainings', [
            'trainings' => $trainings,
        ]);
    }
}
