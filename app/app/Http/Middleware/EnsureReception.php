<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureReception
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! ($user->is_reception ?? false)) {
            abort(403);
        }

        return $next($request);
    }
}

