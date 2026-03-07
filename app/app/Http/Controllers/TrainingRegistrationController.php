<?php

namespace App\Http\Controllers;

use App\Models\Training;
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

            $price = (int) ($training->price ?? 0);

            if ($price > 0 && (int) $user->credits < $price) {
                abort(422, 'Nemáš dostatok kreditov.');
            }

            $training->users()->attach($user->id);

            if ($price > 0) {
                $user->decrement('credits', $price);
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

            $isRegistered = $training->users()
                ->where('users.id', $user->id)
                ->exists();

            if (! $isRegistered) {
                return;
            }

            $training->users()->detach($user->id);

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
                }
            }
        });

        if ($request->expectsJson()) {
            return back()->setStatusCode(204);
        }

        return back()->with('status', 'Odhlásenie z tréningu bolo úspešné.');
    }
}
