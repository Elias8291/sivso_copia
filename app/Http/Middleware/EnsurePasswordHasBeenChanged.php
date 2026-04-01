<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordHasBeenChanged
{
    /**
     * must_change_password === 0: el usuario aún debe definir su contraseña (primera vez o asignada por admin).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs(
            'password.force-change',
            'password.update',
            'logout',
            'verification.notice',
            'verification.verify',
            'verification.send',
        )) {
            return $next($request);
        }

        return redirect()->route('password.force-change');
    }
}
