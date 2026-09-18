<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            abort(401);
        }

        if ($request->user()->isBlocked()) {
            auth()->logout();
            return redirect(url('blocked'));
        }

        if (!empty($roles) && !in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized. Required role: ' . implode(' or ', $roles));
        }

        return $next($request);
    }
}
