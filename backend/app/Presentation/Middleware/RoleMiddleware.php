<?php

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Simple mock for role check
        // if (!$request->user()->hasRole($role)) {
        //     abort(403, "Unauthorized.");
        // }

        return $next($request);
    }
}
