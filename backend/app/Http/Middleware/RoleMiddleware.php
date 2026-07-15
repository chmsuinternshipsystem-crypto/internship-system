<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        // If there is no authenticated user, forbid access.
        if (! $user) {
            abort(403);
        }

        // If specific roles are required, ensure the user's role matches one of them.
        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
