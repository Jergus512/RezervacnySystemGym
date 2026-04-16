<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TrainingCalendarController extends Controller
{
    public function index()
    {
        return view('training.calendar');
    }

    public function events(Request $request)
    {
        // When FullCalendar calls this endpoint, it usually sends `start` and `end`.
        // In tests or other callers, those may be absent; use a wider default range.
        if ($request->query('start') && $request->query('end')) {
            $start = Carbon::parse($request->query('start'));
            $end = Carbon::parse($request->query('end'));
        } else {
            $start = now()->subDays(30);
            $end = now()->addDays(30);
        }

        $userId = $request->user()?->id;

        $forRegistrationUser = $request->user() && $request->user()->isRegularUser();

        $trainings = Training::query()
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->withCount(['users' => function ($query) {
                // Count only active registrations, excluding canceled ones
                $query->where('training_registrations.status', 'active');
            }])
            ->with(['users:id,name', 'creator:id,name'])
            ->when($forRegistrationUser && $userId, function ($q) use ($userId) {
                $q->withExists([
                    'users as is_registered' => function ($uq) use ($userId) {
                        $uq->where('users.id', $userId)
                           ->where('training_registrations.status', 'active');
                    },
                ]);
            })
            ->orderBy('start_at')
            ->get();

        $events = $trainings->map(function (Training $t) {
            $isRegistered = (bool) ($t->is_registered ?? false);
            $isActive = (bool) ($t->is_active ?? true);

            // Training is past if it already ended (or, as fallback, if it already started).
            $isPast = $t->end_at ? $t->end_at->isPast() : ($t->start_at?->isPast() ?? false);

            // Styling rules:
            // - Inactive (explicitly deactivated) OR past: light grey
            // - Upcoming + active + registered: green (full event)
            // - Upcoming + active + not registered: default (blue)
            $bg = null;
            $border = null;
            if (! $isActive) {
                // Neaktívne tréningy sú vždy šedé, bez ohľadu na čas
                $bg = '#e9ecef';
                $border = '#ced4da';
            } elseif ($isPast) {
                // Minulé aktívne tréningy sú šedé
                $bg = '#e9ecef';
                $border = '#ced4da';
            } elseif ($isRegistered) {
                // Budúce aktívne tréningy s registráciou sú zelené
                $bg = '#198754';
                $border = '#198754';
            }

            return [
                'id' => $t->id,
                'title' => $t->title,
                'start' => $t->start_at?->toIso8601String(),
                'end' => $t->end_at?->toIso8601String(),
                'description' => $t->description,
                'capacity' => $t->capacity,
                'price' => $t->price,
                'registered' => $t->users_count,
                'attendees' => $t->users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values(),

                // FullCalendar styling
                'backgroundColor' => $bg,
                'borderColor' => $border,

                // expose for modal/UI
                'is_registered' => $isRegistered,
                'is_active' => $isActive,
                'is_past' => $isPast,

                // creator info
                'creator' => $t->creator ? ['id' => $t->creator->id, 'name' => $t->creator->name] : null,
            ];
        });

        return response()->json($events);
    }
}
