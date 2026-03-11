<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return null; // Mai redirigere, siamo in una API
    }

    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json(['message' => 'Non autenticato.'], 401));
    }
}
