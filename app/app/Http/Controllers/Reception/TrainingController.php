<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Notifications\TrainingCancelledNotification;
use Illuminate\Support\Facades\Notification;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $show = $request->query('show', 'upcoming'); // upcoming | all | cancelled
        $now = now();

        $query = Training::query()->with('creator:id,name');

        // Filtering based on "show"
        if ($show === 'upcoming') {
            $query->where('start_at', '>=', $now)->where('is_active', true);
        } elseif ($show === 'cancelled') {
            $query->where('is_active', false);
        } // "all" filter shows both active and inactive trainings
        elseif ($show === 'all') {
            $query->where(function ($w) use ($now) {
                $w->where('start_at', '>=', $now)->orWhere('is_active', false);
            });
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', '%'.$q.'%')
                    ->orWhere('description', 'like', '%'.$q.'%');
            });
        }

        $trainings = $query->orderBy('start_at')
            ->paginate(15)
            ->withQueryString();

        return view('reception.trainings.index', compact('trainings', 'q', 'show'));
    }

    public function toggleActive(Request $request, Training $training): RedirectResponse
    {
        // Reception is allowed via middleware on routes; ensure training exists
        $validated = $request->validate([
            'action' => ['required', 'in:deactivate,activate'],
        ]);

        $action = $validated['action'];

        if ($action === 'deactivate') {
            $training->update(['is_active' => false, 'canceled_at' => now()]);

            // Notify participants and handle refunds
            foreach ($training->registrations as $registration) {
                // Notify the user about the cancellation
                $user = $registration->user;
                if ($user) {
                    Notification::send($user, new TrainingCancelledNotification($training));
                }

                // Handle refund logic (if applicable)
                // Ensure full refund for all registrations regardless of status
                $price = (int) ($training->price ?? 0);
                if ($price > 0) {
                    $user->increment('credits', $price);

                    // Zaznamenaj zmenu kreditov v CreditMovement
                    \App\Models\CreditMovement::create([
                        'user_id' => $user->id,
                        'training_id' => $training->id,
                        'amount' => $price,
                        'type' => 'training_refund',
                        'description' => 'Vrátenie kreditov za zrušený tréning (recepcia): ' . $training->title,
                        'meta' => [
                            'training_id' => $training->id,
                            'start_at' => optional($training->start_at)->toIso8601String(),
                            'reason' => 'training_canceled_by_reception',
                        ],
                    ]);

                    $registration->update(['status' => 'canceled']);
                }
            }

            $msg = 'Tréning bol zrušený (deaktivovaný).';
            $performedAction = 'deactivate';
        } else {
            $training->update(['is_active' => true]);
            $msg = 'Tréning bol znova aktivovaný.';
            $performedAction = 'activate';
        }

        // Audit logging is now handled automatically by TrainingObserver
        // No need to manually create TrainingAudit records here

        return redirect()->route('reception.trainings.index')->with('status', $msg);
    }
}
