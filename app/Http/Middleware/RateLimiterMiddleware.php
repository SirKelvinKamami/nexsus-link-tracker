<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimiterMiddleware
{
    public function handle(Request $request, Closure $next, string $key = 'api', int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $key = 'rate_limit:' . $key . ':' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'error' => 'Too Many Requests',
                'message' => "Rate limit exceeded. Try again in {$retryAfter} seconds.",
                'retry_after' => $retryAfter,
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => $retryAfter,
            ]);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
        ]);
    }
}
