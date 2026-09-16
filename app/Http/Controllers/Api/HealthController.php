<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends BaseController
{
    public function check(): JsonResponse
    {
        $checks = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
        ];

        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Throwable $e) {
            $checks['database'] = 'error';
            $checks['database_error'] = $e->getMessage();
            $checks['status'] = 'degraded';
        }

        try {
            Cache::put('health_check', true, 10);
            if (Cache::get('health_check')) {
                $checks['cache'] = 'ok';
            } else {
                $checks['cache'] = 'error';
                $checks['status'] = 'degraded';
            }
        } catch (\Throwable $e) {
            $checks['cache'] = 'error';
            $checks['cache_error'] = $e->getMessage();
            $checks['status'] = 'degraded';
        }

        try {
            $checks['disk_free'] = round(disk_free_free_space('/') / 1024 / 1024 / 1024, 2) . ' GB';
            $checks['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
        } catch (\Throwable $e) {
            // Ignore
        }

        $status = $checks['status'] === 'healthy' ? 200 : 503;

        return response()->json($checks, $status);
    }

    public function ready(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            return response()->json(['status' => 'ready'], 200);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'not ready'], 503);
        }
    }

    public function live(): JsonResponse
    {
        return response()->json(['status' => 'alive'], 200);
    }
}
