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
            ->orderBy('email')
            ->limit(10)
            ->get(['id', 'name', 'email', 'credits', 'is_admin', 'is_trainer', 'is_reception']);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'credits_to_add' => ['required', 'integer', 'min:1'],
        ]);

        $creditsToAdd = (int) $validated['credits_to_add'];

        DB::transaction(function () use ($validated, $creditsToAdd) {
            $user = User::query()->whereKey($validated['user_id'])->lockForUpdate()->firstOrFail();
            $user->increment('credits', $creditsToAdd);
        });

        return back()->with('status', 'Kredity boli pripísané, počet kreditov: '.$creditsToAdd.'.');
    }
}
