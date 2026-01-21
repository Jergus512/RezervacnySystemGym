<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', 'all');

        $usersQuery = User::query();

        if ($q !== '') {
            $usersQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%');
            });
        }

        // Role filter
        switch ($role) {
            case 'admin':
                $usersQuery->where('is_admin', true);
                break;
            case 'trainer':
                $usersQuery->where('is_trainer', true);
                break;
            case 'reception':
                $usersQuery->where('is_reception', true);
                break;
            case 'regular':
                $usersQuery->where('is_admin', false)
                    ->where('is_trainer', false)
                    ->where('is_reception', false);
                break;
            case 'all':
            default:
                // no filter
                $role = 'all';
                break;
        }

        $users = $usersQuery->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'role'));
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

        $credits = null;
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
            $user->credits = null;
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

    public function autocomplete(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', 'all');

        if ($q === '') {
            return response()->json([]);
        }

        $usersQuery = User::query();

        // Role filter (same as index)
        switch ($role) {
            case 'admin':
                $usersQuery->where('is_admin', true);
                break;
            case 'trainer':
                $usersQuery->where('is_trainer', true);
                break;
            case 'reception':
                $usersQuery->where('is_reception', true);
                break;
            case 'regular':
                $usersQuery->where('is_admin', false)
                    ->where('is_trainer', false)
                    ->where('is_reception', false);
                break;
            case 'all':
            default:
                $role = 'all';
                break;
        }

        $users = $usersQuery
            ->where(function ($sub) use ($q) {
                $sub->where('email', 'like', $q.'%')
                    ->orWhere('name', 'like', $q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email', 'is_admin', 'is_trainer', 'is_reception']);

        $payload = $users->map(function (User $u) {
            $type = 'Bežný';
            if ($u->is_admin) {
                $type = 'Admin';
            } elseif ($u->is_trainer) {
                $type = 'Tréner';
            } elseif ($u->is_reception) {
                $type = 'Recepcia';
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'type' => $type,
            ];
        })->values();

        return response()->json($payload);
    }
}
