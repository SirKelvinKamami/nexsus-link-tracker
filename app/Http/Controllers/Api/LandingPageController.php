<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LandingPage;

class LandingPageController extends BaseController
{
    /**
     * List all landing pages for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $this->getUserId();
        $perPage = $request->input('per_page', 50);

        $query = LandingPage::where('user_id', $userId);

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        $pages = $query->orderByDesc('updated_at')
            ->paginate($perPage);

        $pages->getCollection()->transform(function ($page) {
            return [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'description' => $page->description,
                'is_published' => $page->is_published,
                'public_url' => $page->is_published ? url("/lp/{$page->slug}") : null,
                'created_at' => $page->created_at->toISOString(),
                'updated_at' => $page->updated_at->toISOString(),
            ];
        });

        return $this->success($pages);
    }

    /**
     * Get a single landing page with content.
     */
    public function show($id)
    {
        $userId = $this->getUserId();

        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$page) {
            return $this->error('Landing page not found', 404);
        }

        $data = [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'description' => $page->description,
            'content' => $page->content,
            'settings' => $page->settings,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'og_image' => $page->og_image,
            'is_published' => $page->is_published,
            'collect_emails' => $page->collect_emails,
            'form_id' => $page->form_id,
            'public_url' => $page->is_published ? url("/lp/{$page->slug}") : null,
            'created_at' => $page->created_at->toISOString(),
            'updated_at' => $page->updated_at->toISOString(),
        ];

        return $this->success($data);
    }

    /**
     * Get landing page statistics.
     */
    public function stats($id)
    {
        $userId = $this->getUserId();

        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$page) {
            return $this->error('Landing page not found', 404);
        }

        $visits = visits('App\Models\LandingPage', $page->id);

        $data = [
            'page_id' => $page->id,
            'title' => $page->title,
            'total_visits' => $visits->count(),
            'today_visits' => $visits->period('day')->count(),
            'week_visits' => $visits->period('week')->count(),
            'month_visits' => $visits->period('month')->count(),
            'is_published' => $page->is_published,
        ];

        return $this->success($data);
    }
}
