<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['sometimes', 'boolean'],
            'is_trainer' => ['sometimes', 'boolean'],
            'is_reception' => ['sometimes', 'boolean'],
            'credits' => ['nullable', 'integer', 'min:0'],
        ]);

        $isAdmin = $request->boolean('is_admin');
        $isTrainer = $request->boolean('is_trainer');
        $isReception = $request->boolean('is_reception');

        // Keep roles mutually exclusive
        if ($isAdmin) {
            $isTrainer = false;
            $isReception = false;
        }
        if ($isTrainer) {
            $isReception = false;
        }

        $credits = 0;
        if (! $isAdmin && ! $isTrainer && ! $isReception) {
            $credits = (int) ($validated['credits'] ?? 0);
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $isAdmin,
            'is_trainer' => $isTrainer,
            'is_reception' => $isReception,
            'credits' => $credits,
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Používateľ bol vytvorený.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['sometimes', 'boolean'],
            'is_trainer' => ['sometimes', 'boolean'],
            'is_reception' => ['sometimes', 'boolean'],
            'credits' => ['nullable', 'integer', 'min:0'],
        ]);

        $isAdmin = $request->boolean('is_admin');
        $isTrainer = $request->boolean('is_trainer');
        $isReception = $request->boolean('is_reception');

        // Keep roles mutually exclusive
        if ($isAdmin) {
            $isTrainer = false;
            $isReception = false;
        }
        if ($isTrainer) {
            $isReception = false;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_admin = $isAdmin;
        $user->is_trainer = $isTrainer;
        $user->is_reception = $isReception;

        if ($user->isAdmin() || $user->isTrainer() || $user->isReception()) {
            $user->credits = 0;
        } else {
            // regular user: allow admin to set credits
            $user->credits = (int) ($validated['credits'] ?? $user->credits ?? 0);
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Používateľ bol upravený.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('status', 'Nemôžeš zmazať sám seba.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Používateľ bol vymazaný.');
    }
}
