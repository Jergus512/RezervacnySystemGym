<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Nesprávny email alebo heslo.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        // If user was trying to access a protected page, intended() will still win.
        // Otherwise, send them to their role's start page.
        $default = route('training-calendar.index');
        if ($user?->isAdmin()) {
            $default = route('admin.users.index');
        } elseif ($user?->isTrainer()) {
            $default = route('trainer.trainings.index');
        } elseif ($user?->isReception()) {
            $default = route('reception.calendar');
        }

        return redirect()->intended($default);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
