<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\CreditMovement;
use App\Models\PenaltySetting;
use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnregistrationController extends Controller
{
    public function index()
    {
        return view('reception.unregistration.index');
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        // Search for users by email or name - only regular users
        $users = User::query()
            ->where('is_admin', false)
            ->where('is_trainer', false)
            ->where('is_reception', false)
            ->where(function ($query) use ($q) {
                $query->where('email', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%');
            })
            ->orderBy('email')
            ->limit(10)
            ->get(['id', 'name', 'email', 'credits']);

        return response()->json($users);
    }

    public function trainings(Request $request, User $user)
    {
        // Only regular users
        if (!$user->isRegularUser()) {
            abort(404);
        }

        // Get all trainings user is registered for (only active registrations)
        $trainings = Training::query()
            ->whereHas('registrations', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'active');
            })
            ->where('start_at', '>=', now()) // Only upcoming trainings
            ->orderBy('start_at')
            ->get(['id', 'title', 'start_at', 'end_at', 'price']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'credits' => (int) ($user->credits ?? 0),
            ],
            'trainings' => $trainings->map(function (Training $t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'start_at' => $t->start_at?->toIso8601String(),
                    'end_at' => $t->end_at?->toIso8601String(),
                    'price' => (int) $t->price,
                    'start_at_formatted' => $t->start_at?->format('d.m.Y H:i'),
                ];
            }),
        ]);
    }

    public function unregister(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'training_id' => ['required', 'integer', 'exists:trainings,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $training = Training::findOrFail($validated['training_id']);

        // Verify user is regular user
        if (!$user->isRegularUser()) {
            abort(422, 'Iba bežní používatelia sa dajú odhlasovať z tréningov.');
        }

        // Verify training hasn't started
        if ($training->start_at && $training->start_at->isPast()) {
            abort(422, 'Z tréningu, ktorý už začal, sa nedá odhlásiť.');
        }

        DB::transaction(function () use ($user, $training) {
            // Lock user row to prevent double-refund
            $user = $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();

            // Get the registration
            $registration = TrainingRegistration::withoutGlobalScopes()
                ->where('training_id', $training->id)
                ->where('user_id', $user->id)
                ->first();

            // No registration or already canceled => nothing to do
            if (!$registration || $registration->status === 'canceled') {
                return;
            }

            // Mark as canceled
            $registration->status = 'canceled';
            $registration->save();

            $price = (int) ($training->price ?? 0);
            if ($price > 0) {
                // Calculate refund with penalty settings
                $refundAmount = 0;
                try {
                    $settings = PenaltySetting::getSingleton();
                } catch (\Throwable $e) {
                    $settings = null;
                }

                if (!$training->start_at || !$settings) {
                    // No start time or no settings => full refund
                    $refundAmount = $price;
                } else {
                    // Use signed diff so past start times produce negative values
                    $minutesUntilStart = (int) now()->diffInMinutes($training->start_at, false);
                    $window = (int) ($settings->refund_window_minutes ?? 0);

                    if ($minutesUntilStart >= $window) {
                        // Early cancellation => full refund
                        $refundAmount = $price;
                    } else {
                        // Late cancellation => apply penalty policy
                        $policy = $settings->penalty_policy ?? 'none';
                        if ($policy === 'half') {
                            $refundAmount = intdiv($price, 2);
                        } else {
                            $refundAmount = 0;
                        }
                    }
                }

                if ($refundAmount > 0) {
                    $user->increment('credits', $refundAmount);

                    CreditMovement::create([
                        'user_id' => $user->id,
                        'training_id' => $training->id,
                        'amount' => $refundAmount,
                        'type' => 'training_refund',
                        'description' => 'Vrátenie kreditov za zrušený tréning (recepcia): ' . $training->title,
                        'meta' => [
                            'training_id' => $training->id,
                            'start_at' => optional($training->start_at)->toIso8601String(),
                            'refund_amount' => $refundAmount,
                            'penalty_applied' => $refundAmount < $price,
                        ],
                    ]);
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Používateľ bol odhlásený z tréningu.',
            ]);
        }

        return back()->with('status', 'Používateľ bol odhlásený z tréningu.');
    }
}
