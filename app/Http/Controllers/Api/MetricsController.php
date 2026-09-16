<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\LinkClick;
use App\Models\Form;
use App\Models\FormResponse;
use App\Models\LandingPage;
use App\Models\Project;
use App\Models\Webhook;

class MetricsController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);

        $metrics = [
            'timestamp' => now()->toISOString(),
            'counts' => [
                'links' => Link::where('user_id', $userId)->count(),
                'clicks_total' => LinkClick::where('user_id', $userId)->count(),
                'clicks_today' => LinkClick::where('user_id', $userId)->whereDate('created_at', now()->toDateString())->count(),
                'forms' => Form::where('user_id', $userId)->count(),
                'form_responses' => FormResponse::whereIn('form_id', Form::where('user_id', $userId)->pluck('id'))->count(),
                'landing_pages' => LandingPage::where('user_id', $userId)->count(),
                'projects' => Project::where('user_id', $userId)->count(),
                'webhooks' => Webhook::where('user_id', $userId)->count(),
            ],
            'performance' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
                'uptime' => $this->getUptime(),
            ],
        ];

        return $this->success($metrics);
    }

    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = shell_exec('uptime -p');
            return $uptime ? trim($uptime) : 'unknown';
        }
        return 'N/A (Windows)';
    }
}
