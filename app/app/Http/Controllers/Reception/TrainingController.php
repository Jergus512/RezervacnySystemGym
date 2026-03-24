<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingAudit;
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

        // Ensure we read the latest DB state (avoid any stale in-memory model)
        $old = (bool) Training::whereKey($training->id)->value('is_active');

        if ($action === 'deactivate') {
            $training->update(['is_active' => false]);

            // Notify participants and handle refunds
            foreach ($training->registrations as $registration) {
                // Notify the user about the cancellation
                $user = $registration->user;
                if ($user) {
                    Notification::send($user, new TrainingCancelledNotification($training));
                }

                // Handle refund logic (if applicable)
                // Ensure full refund for all active registrations
                $price = (int) ($training->price ?? 0);
                if ($price > 0 && $registration->status === 'active') {
                    $registration->user->increment('credits', $price);
                    $registration->update(['status' => 'refunded']);
                }
            }

            $msg = 'Tréning bol zrušený (deaktivovaný).';
            $performedAction = 'deactivate';
        } else {
            $training->update(['is_active' => true]);
            $msg = 'Tréning bol znova aktivovaný.';
            $performedAction = 'activate';
        }

        // Create audit record
        try {
            // refresh model to reflect the new state
            $training->refresh();
             TrainingAudit::create([
                 'training_id' => $training->id,
                 'performed_by_user_id' => auth()?->id(),
                 'action' => $performedAction,
                 'meta' => [
                     'old_is_active' => $old,
                     'new_is_active' => (bool) $training->is_active,
                 ],
             ]);
        } catch (\Throwable $e) {
            // don't break the user flow if audit fails; optionally log
        }

        return redirect()->route('reception.trainings.index')->with('status', $msg);
    }
}
