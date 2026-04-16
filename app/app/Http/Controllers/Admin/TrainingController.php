<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    protected function requireAdmin(Request $request): void
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->requireAdmin($request);

        $q = trim((string) $request->query('q', ''));
        $now = now();

        $trainings = Training::query()
            ->with('creator:id,name')
            ->where('start_at', '>=', $now)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', '%'.$q.'%')
                        ->orWhere('description', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('start_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.trainings.index', compact('trainings', 'q'));
    }

    public function archive(Request $request)
    {
        $this->requireAdmin($request);

        $q = trim((string) $request->query('q', ''));
        $now = now();

        $trainings = Training::query()
            ->with('creator:id,name')
            ->where(function ($query) use ($now) {
                // Tréningy v archíve sú: tie, ktoré majú start_at v minulosti ALEBO sú explicitne deaktivované
                $query->where('start_at', '<', $now)
                      ->orWhere('is_active', false);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', '%'.$q.'%')
                        ->orWhere('description', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('start_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.trainings.archive', compact('trainings', 'q'));
    }

    public function edit(Request $request, Training $training)
    {
        $this->requireAdmin($request);

        $trainers = User::query()
            ->where('is_trainer', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $trainingTypes = TrainingType::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.trainings.edit', compact('training', 'trainers', 'trainingTypes'));
    }

    public function update(Request $request, Training $training): RedirectResponse
    {
        $this->requireAdmin($request);

        $validated = $request->validate([
            'training_type_id' => ['nullable', 'integer', 'exists:training_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'created_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'cancel_training' => ['sometimes', 'boolean'],
        ]);

        // Ak je požiadavka na zrušenie tréningu
        if ($request->boolean('cancel_training')) {
            if (!$training->is_active) {
                return redirect()->back()->with('error', 'Len aktívne tréningy je možné zrušiť.');
            }

            try {
                $training->cancelTraining();
                return redirect()->route('admin.trainings.index')
                    ->with('status', 'Tréning bol úspešne zrušený a používatelia dostali notifikáciu.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        // Normálna aktualizácia tréningu
        $training->update([
            'training_type_id' => $validated['training_type_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'capacity' => (int) $validated['capacity'],
            'price' => (int) $validated['price'],
            'is_active' => $request->boolean('is_active'),
            'created_by_user_id' => $validated['created_by_user_id'] ?? null,
        ]);

        return redirect()->route('admin.trainings.index')
            ->with('status', 'Tréning bol upravený (admin).');
    }

    public function destroy(Request $request, Training $training): RedirectResponse
    {
        $this->requireAdmin($request);

        $training->delete();

        return redirect()->route('training-calendar.index')
            ->with('status', 'Tréning bol odstránený (admin).');
    }

    public function cancel(Request $request, Training $training): RedirectResponse
    {
        $this->requireAdmin($request);

        if (! $training->is_active) {
            return redirect()->back()->with('error', 'Training is already inactive.');
        }

        $training->cancelTraining();

        return redirect()->route('admin.trainings.index')->with('success', 'Training has been cancelled successfully.');
    }
}
