<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = env('API_TOKEN');

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'error' => 'Server misconfiguration',
                'message' => 'API_TOKEN not set in .env',
            ], 500);
        }

        $bearer = $request->header('Authorization');
        $provided = $bearer && str_starts_with($bearer, 'Bearer ')
            ? substr($bearer, 7)
            : $request->query('token');

        if (empty($provided) || !hash_equals($token, $provided)) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API token',
            ], 401);
        }

        return $next($request);
    }
}