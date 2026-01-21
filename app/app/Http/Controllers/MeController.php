<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MeController extends Controller
{
    public function credits(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        if (! $user->isRegularUser()) {
            abort(404);
        }

        return response()->json([
            'credits' => (int) ($user->credits ?? 0),
        ]);
    }
}

