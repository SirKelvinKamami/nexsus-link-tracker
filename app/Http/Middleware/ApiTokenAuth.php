<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->header('Authorization');
        $provided = $bearer && str_starts_with($bearer, 'Bearer ')
            ? substr($bearer, 7)
            : $request->query('token');

        if (empty($provided)) {
            return $this->unauthorized($request);
        }

        $envToken = env('API_TOKEN');
        if (!empty($envToken) && hash_equals($envToken, $provided)) {
            request()->merge(['_api_user_id' => 1]);
            return $next($request);
        }

        $tokenHash = hash('sha256', $provided);
        $apiToken = ApiToken::where('token', $tokenHash)
            ->where('is_active', true)
            ->first();

        if (!$apiToken) {
            return $this->unauthorized($request);
        }

        if ($apiToken->isExpired()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'API token has expired',
            ], 401);
        }

        $scope = $this->determineScope($request);
        if ($scope && !$apiToken->hasScope($scope)) {
            return response()->json([
                'success' => false,
                'error' => 'Forbidden',
                'message' => "Token missing required scope: {$scope}",
            ], 403);
        }

        $apiToken->recordUsage($request->ip());

        request()->merge(['_api_user_id' => $apiToken->user_id]);

        return $next($request);
    }

    private function determineScope(Request $request): ?string
    {
        $method = $request->method();
        if ($method === 'GET' || $method === 'HEAD') {
            return 'read';
        }
        if ($method === 'DELETE') {
            return 'admin';
        }
        return 'write';
    }

    private function unauthorized(Request $request)
    {
        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API token',
            ], 401);
        }
        return redirect()->route('login');
    }
}
