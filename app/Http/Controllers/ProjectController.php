<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Project;

class ProjectController extends Controller
{
    /**
     * List all projects for the authenticated user.
     */
    public function index()
    {
        $userId = Auth::id();
        $projects = Project::where('user_id', $userId)
            ->withCount(['links', 'forms', 'landingPages'])
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();

        $currentProjectId = session('current_project_id');

        return view('studio.projects.index', compact('projects', 'currentProjectId'));
    }

    /**
     * Show form for creating a new project.
     */
    public function create()
    {
        return view('studio.projects.create');
    }

    /**
     * Store a new project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();
        $slug = Str::slug($request->name);
        
        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $project = Project::create([
            'user_id' => $userId,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description ?? '',
            'settings' => [
                'ga4_id' => '',
                'gtm_id' => '',
                'primary_color' => '#667eea',
                'favicon' => '',
            ],
            'is_default' => false,
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'Project created.');
    }

    /**
     * Show project settings.
     */
    public function edit($id)
    {
        $userId = Auth::id();
        $project = Project::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        return view('studio.projects.edit', compact('project'));
    }

    /**
     * Update project settings.
     */
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $project = Project::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'ga4_id' => 'nullable|string|max:255',
            'gtm_id' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
        ]);

        $settings = $project->settings ?? [];
        $settings['ga4_id'] = $request->ga4_id ?? $settings['ga4_id'] ?? '';
        $settings['gtm_id'] = $request->gtm_id ?? $settings['gtm_id'] ?? '';
        $settings['primary_color'] = $request->primary_color ?? $settings['primary_color'] ?? '#667eea';

        $project->update([
            'name' => $request->name,
            'description' => $request->description ?? '',
            'settings' => $settings,
        ]);

        return redirect()->route('projects.edit', $project->id)
            ->with('success', 'Project updated.');
    }

    /**
     * Delete a project.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $project = Project::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($project->is_default) {
            return redirect()->route('projects.index')
                ->with('error', 'Cannot delete the default project.');
        }

        // Move links to default project
        $defaultProject = Project::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        if ($defaultProject) {
            $project->links()->update(['project_id' => $defaultProject->id]);
            $project->forms()->update(['project_id' => $defaultProject->id]);
            $project->landingPages()->update(['project_id' => $defaultProject->id]);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    /**
     * Switch active project.
     */
    public function switch($id)
    {
        $userId = Auth::id();
        $project = Project::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        session(['current_project_id' => $project->id]);

        return redirect()->back()
            ->with('success', "Switched to project: {$project->name}");
    }

    /**
     * Set project as default.
     */
    public function setDefault($id)
    {
        $userId = Auth::id();
        $project = Project::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Unset other defaults
        Project::where('user_id', $userId)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $project->update(['is_default' => true]);

        return redirect()->route('projects.index')
            ->with('success', "{$project->name} is now the default project.");
    }
}
