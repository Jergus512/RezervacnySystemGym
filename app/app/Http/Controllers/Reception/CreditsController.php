<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditsController extends Controller
{
    public function create()
    {
        return view('reception.credits.create');
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $users = User::query()
            ->where('email', 'like', $q.'%')
            ->where('is_admin', false)
            ->where('is_trainer', false)
            ->where('is_reception', false)
            ->orderBy('email')
            ->limit(10)
            ->get(['id', 'name', 'email', 'credits']);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'credits_to_add' => ['required', 'integer', 'min:1'],
        ]);

        $creditsToAdd = (int) $validated['credits_to_add'];

        /** @var \App\Models\User|null $updatedUser */
        $updatedUser = null;

        DB::transaction(function () use ($validated, $creditsToAdd, &$updatedUser) {
            $user = User::query()->whereKey($validated['user_id'])->lockForUpdate()->firstOrFail();

            if (! $user->isRegularUser()) {
                abort(422, 'Kredity sa dajú pripisovať iba bežným používateľom.');
            }

            if ($user->credits === null) {
                $user->credits = 0;
                $user->save();
            }

            $user->increment('credits', $creditsToAdd);

            $updatedUser = $user->refresh();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Kredity boli pripísané.',
                'user' => [
                    'id' => $updatedUser?->id,
                    'name' => $updatedUser?->name,
                    'email' => $updatedUser?->email,
                    'credits' => $updatedUser?->credits,
                ],
            ]);
        }

        return back()->with('status', 'Kredity boli pripísané, počet kreditov: '.$creditsToAdd.'.');
    }

    public function credits(Request $request, User $user)
    {
        // Only regular users can have credits.
        if (! $user->isRegularUser()) {
            abort(404);
        }

        return response()->json([
            'id' => $user->id,
            'credits' => (int) ($user->credits ?? 0),
        ]);
    }
}
