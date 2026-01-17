<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingManageController extends Controller
{
    protected function requireTrainer(Request $request): void
    {
        if (! $request->user()?->isTrainer()) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->requireTrainer($request);

        $trainings = $request->user()
            ->createdTrainings()
            ->orderByDesc('start_at')
            ->paginate(15);

        return view('trainer.trainings.index', compact('trainings'));
    }

    public function create(Request $request)
    {
        $this->requireTrainer($request);

        return view('trainer.trainings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireTrainer($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],

            // weekly repeating
            'repeat_weekly' => ['sometimes', 'boolean'],
            'repeat_weeks' => ['nullable', 'integer', 'min:1', 'max:52'],
        ]);

        $repeatWeekly = $request->boolean('repeat_weekly');
        $repeatWeeks = (int) ($validated['repeat_weeks'] ?? 1);
        if (! $repeatWeekly) {
            $repeatWeeks = 1;
        }

        $baseStart = now()->parse($validated['start_at']);
        $baseEnd = now()->parse($validated['end_at']);

        DB::transaction(function () use ($request, $validated, $repeatWeeks, $baseStart, $baseEnd) {
            for ($i = 0; $i < $repeatWeeks; $i++) {
                $startAt = $baseStart->copy()->addWeeks($i);
                $endAt = $baseEnd->copy()->addWeeks($i);

                Training::create([
                    'created_by_user_id' => $request->user()->id,
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'capacity' => (int) $validated['capacity'],
                    'price' => (int) $validated['price'],
                    'is_active' => $request->boolean('is_active'),
                ]);
            }
        });

        $msg = $repeatWeeks > 1
            ? "Tréningy boli vytvorené (počet: {$repeatWeeks})."
            : 'Tréning bol vytvorený.';

        return redirect()->route('trainer.trainings.create')
            ->with('status', $msg);
    }

    public function edit(Request $request, Training $training)
    {
        $this->requireTrainer($request);

        if ((int) $training->created_by_user_id !== (int) $request->user()->id) {
            abort(403);
        }

        return view('trainer.trainings.edit', compact('training'));
    }

    public function update(Request $request, Training $training): RedirectResponse
    {
        $this->requireTrainer($request);

        if ((int) $training->created_by_user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $training->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'capacity' => (int) $validated['capacity'],
            'price' => (int) $validated['price'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('trainer.trainings.index')
            ->with('status', 'Tréning bol upravený.');
    }

    public function destroy(Request $request, Training $training): RedirectResponse
    {
        $this->requireTrainer($request);

        if ((int) $training->created_by_user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $training->delete();

        return redirect()->route('trainer.trainings.index')
            ->with('status', 'Tréning bol odstránený.');
    }
}
