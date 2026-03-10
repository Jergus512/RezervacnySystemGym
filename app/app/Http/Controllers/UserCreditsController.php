<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserCreditsController extends Controller
{
    /**
     * Show authenticated user's credit change history.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        return view('user.credits-history', [
            'user' => $user,
        ]);
    }

    /**
     * Show authenticated user's completed trainings overview.
     */
    public function trainings(Request $request): View
    {
        $user = Auth::user();

        return view('user.training-history', [
            'user' => $user,
        ]);
    }
}

