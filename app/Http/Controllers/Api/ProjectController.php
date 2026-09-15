<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends BaseController
{
    /**
     * List all projects for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $this->getUserId();
        $perPage = $request->input('per_page', 50);

        $projects = Project::where('user_id', $userId)
            ->withCount(['links', 'forms', 'landingPages'])
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $projects->getCollection()->transform(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'description' => $project->description,
                'is_default' => $project->is_default,
                'settings' => $project->settings,
                'links_count' => $project->links_count,
                'forms_count' => $project->forms_count,
                'landing_pages_count' => $project->landing_pages_count,
                'created_at' => $project->created_at->toISOString(),
                'updated_at' => $project->updated_at->toISOString(),
            ];
        });

        return $this->success($projects);
    }

    /**
     * Get a single project with stats.
     */
    public function show($id)
    {
        $userId = $this->getUserId();

        $project = Project::where('id', $id)
            ->where('user_id', $userId)
            ->withCount(['links', 'forms', 'landingPages'])
            ->first();

        if (!$project) {
            return $this->error('Project not found', 404);
        }

        $data = [
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'description' => $project->description,
            'is_default' => $project->is_default,
            'settings' => $project->settings,
            'ga4_id' => $project->getGa4Id(),
            'gtm_id' => $project->getGtmId(),
            'links_count' => $project->links_count,
            'forms_count' => $project->forms_count,
            'landing_pages_count' => $project->landing_pages_count,
            'created_at' => $project->created_at->toISOString(),
            'updated_at' => $project->updated_at->toISOString(),
        ];

        return $this->success($data);
    }

    /**
     * Get project analytics summary.
     */
    public function stats($id)
    {
        $userId = $this->getUserId();

        $project = Project::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$project) {
            return $this->error('Project not found', 404);
        }

        $clicks = $project->clicks();

        $totalClicks = (clone $clicks)->count();
        $todayClicks = (clone $clicks)->whereDate('created_at', now()->toDateString())->count();
        $weekClicks = (clone $clicks)->where('created_at', '>=', now()->subDays(7))->count();
        $monthClicks = (clone $clicks)->where('created_at', '>=', now()->subDays(30))->count();

        $data = [
            'project_id' => $project->id,
            'name' => $project->name,
            'total_clicks' => $totalClicks,
            'today_clicks' => $todayClicks,
            'week_clicks' => $weekClicks,
            'month_clicks' => $monthClicks,
            'links_count' => $project->links()->count(),
            'forms_count' => $project->forms()->count(),
            'landing_pages_count' => $project->landingPages()->count(),
        ];

        return $this->success($data);
    }
}
