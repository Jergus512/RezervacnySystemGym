<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainerRating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TrainerRatingController extends Controller
{
    /**
     * Ulož hodnotenie trénera
     */
    public function store(Request $request, User $trainer): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (!$user || !$user->isRegularUser()) {
            abort(403, 'Iba zákazníci môžu hodnotiť trénerov.');
        }

        if (!$trainer->isTrainer()) {
            abort(404, 'Tréner neexistuje.');
        }

        // Validuj vstup
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'training_id' => 'nullable|exists:trainings,id',
        ]);

        // Ak je zadaný training_id, skontroluj či používateľ naozaj navštevoval tréning
        if ($validated['training_id']) {
            $training = Training::findOrFail($validated['training_id']);

            // Skontroluj či používateľ mal rezerváciu na tomto tréningu
            $hasAttended = DB::table('training_registrations')
                ->where('training_id', $training->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();

            if (!$hasAttended) {
                abort(403, 'Môžeš hodnotiť iba trénerov, ktorých tréningy si navštevoval.');
            }
        }

        try {
            $rating = TrainerRating::updateOrCreate(
                [
                    'trainer_id' => $trainer->id,
                    'user_id' => $user->id,
                    'training_id' => $validated['training_id'],
                ],
                [
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'],
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hodnotenie bolo uložené!',
                    'rating' => $rating,
                ], 201);
            }

            return back()->with('status', 'Tvoje hodnotenie bolo úspešne uložené.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Chyba pri ukladaní hodnotenia.',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Chyba pri ukladaní hodnotenia.');
        }
    }

    /**
     * Získaj všetky hodnotenia pre konkrétneho trénera
     */
    public function getTrainerRatings(User $trainer): JsonResponse
    {
        if (!$trainer->isTrainer()) {
            abort(404, 'Tréner neexistuje.');
        }

        $ratings = TrainerRating::where('trainer_id', $trainer->id)
            ->with('user', 'training')
            ->orderBy('created_at', 'desc')
            ->get();

        $avgRating = TrainerRating::where('trainer_id', $trainer->id)
            ->avg('rating');

        $ratingCount = $ratings->count();

        // Rozdelenie podľa počtu hviezd
        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = TrainerRating::where('trainer_id', $trainer->id)
                ->where('rating', $i)
                ->count();
            $ratingDistribution[$i] = [
                'count' => $count,
                'percentage' => $ratingCount > 0 ? round(($count / $ratingCount) * 100) : 0,
            ];
        }

        return response()->json([
            'trainer' => $trainer,
            'avg_rating' => $avgRating ? round($avgRating, 2) : 0,
            'total_ratings' => $ratingCount,
            'distribution' => $ratingDistribution,
            'ratings' => $ratings,
        ]);
    }

    /**
     * Príslušnosť: Vrátenie hodnotenia pre konkrétny tréning
     */
    public function getUserRatingForTraining(Request $request, Training $training): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['rating' => null], 401);
        }

        $rating = TrainerRating::where('training_id', $training->id)
            ->where('trainer_id', $training->created_by_user_id)
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'rating' => $rating?->rating,
            'comment' => $rating?->comment,
            'created_at' => $rating?->created_at,
        ]);
    }
}
