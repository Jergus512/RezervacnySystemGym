<?php

namespace App\Http\Controllers;

use App\Models\CreditMovement;
use App\Models\Training;
use App\Models\TrainingRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingRegistrationController extends Controller
{
    public function store(Request $request, Training $training): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isRegularUser()) {
            abort(403, 'Na tréningy sa môže prihlasovať iba bežný používateľ.');
        }

        DB::transaction(function () use ($training, $user) {
            $training->refresh();

            // Disallow registration if training is not active or has already started
            if (($training->is_active ?? true) === false) {
                abort(422, 'Tento tréning už nie je aktuálny.');
            }
            if ($training->start_at && $training->start_at->isPast()) {
                abort(422, 'Na tréning, ktorý už začal, sa nedá prihlásiť.');
            }

            // Lock user row to prevent double-charge in concurrent requests
            $user = $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();

            $alreadyRegistered = $training->users()
                ->where('users.id', $user->id)
                ->exists();

            // Already registered => do nothing (no charge)
            if ($alreadyRegistered) {
                return;
            }

            $registeredCount = $training->users()->count();

            if ($training->capacity > 0 && $registeredCount >= $training->capacity) {
                abort(422, 'Tréning je už plný.');
            }

            $training->users()->attach($user->id, ['status' => 'active']);

            $price = (int) ($training->price ?? 0);

            if ($price > 0) {
                $user->decrement('credits', $price);

                CreditMovement::create([
                    'user_id' => $user->id,
                    'amount' => -$price,
                    'type' => 'training_charge',
                    'description' => 'Rezervácia tréningu: '.$training->title,
                    'meta' => [
                        'training_id' => $training->id,
                        'start_at' => optional($training->start_at)->toIso8601String(),
                    ],
                ]);
            }
        });

        // For AJAX requests, return 204 so frontend knows it succeeded without redirect
        if ($request->expectsJson()) {
            return back()->setStatusCode(204);
        }

        return back()->with('status', 'Prihlásenie na tréning bolo úspešné.');
    }

    public function destroy(Request $request, Training $training): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isRegularUser()) {
            abort(403, 'Z tréningov sa môže odhlasovať iba bežný používateľ.');
        }

        DB::transaction(function () use ($training, $user) {
            $training->refresh();

            // Disallow unregistration if training has already started
            if ($training->start_at && $training->start_at->isPast()) {
                abort(422, 'Z tréningu, ktorý už začal, sa nedá odhlásiť.');
            }

            // Lock user row to prevent double-refund in concurrent requests
            $user = $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();

            // Work directly with the pivot model so we can see also canceled registrations if needed.
            // We only care about an active registration for unregistration logic.
            $registration = TrainingRegistration::withoutGlobalScopes()
                ->where('training_id', $training->id)
                ->where('user_id', $user->id)
                ->first();

            // No registration at all => nothing to do (avoid double-refunds)
            if (! $registration) {
                return;
            }

            // If it's already canceled, do nothing (idempotent endpoint)
            if ($registration->status === 'canceled') {
                return;
            }

            // Mark as canceled for analytics
            $registration->status = 'canceled';
            $registration->save();

            $price = (int) ($training->price ?? 0);
            if ($price > 0) {
                // Determine refund amount based on admin-configured penalty settings
                $refundAmount = 0;
                try {
                    $settings = \App\Models\PenaltySetting::getSingleton();
                } catch (\Throwable $e) {
                    // If settings table / model missing, fall back to full refund to be safe
                    $settings = null;
                }

                if (! $training->start_at || ! $settings) {
                    // If there's no start time or no settings available, refund full amount
                    $refundAmount = $price;
                } else {
                    // Use a signed diff so past start times produce negative values
                    $minutesUntilStart = (int) now()->diffInMinutes($training->start_at, false);
                    $window = (int) ($settings->refund_window_minutes ?? 0);

                    if ($minutesUntilStart >= $window) {
                        // Cancellation happened earlier than the configured window => full refund
                        $refundAmount = $price;
                    } else {
                        // Late cancellation => apply penalty policy
                        $policy = $settings->penalty_policy ?? 'none';
                        if ($policy === 'half') {
                            $refundAmount = intdiv($price, 2);
                        } else { // 'none' or unknown
                            $refundAmount = 0;
                        }
                    }
                }

                if ($refundAmount > 0) {
                    $user->increment('credits', $refundAmount);

                    CreditMovement::create([
                        'user_id' => $user->id,
                        'amount' => $refundAmount,
                        'type' => 'training_refund',
                        'description' => 'Vrátenie kreditov za zrušený tréning: '.$training->title,
                        'meta' => [
                            'training_id' => $training->id,
                            'start_at' => optional($training->start_at)->toIso8601String(),
                        ],
                    ]);
                }
            }
        });

        if ($request->expectsJson()) {
            return back()->setStatusCode(204);
        }

        return back()->with('status', 'Odhlásenie z tréningu bolo úspešné.');
    }
}
