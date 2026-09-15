<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinkController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);

        $query = Link::select('links.*')
            ->leftJoinSub(
                LinkClick::selectRaw('link_id, COUNT(*) as click_count, MAX(created_at) as last_clicked_at')
                    ->groupBy('link_id'),
                'stats', 'links.id', '=', 'stats.link_id'
            )
            ->selectRaw('links.*, COALESCE(stats.click_count, 0) as click_count, stats.last_clicked_at');

        if ($userId) {
            $query->where('links.user_id', $userId);
        }

        $query->whereNotNull('links.link')->where('links.link', '<>', '');

        $total = $query->count();
        $perPage = min((int) $request->input('per_page', 50), 100);
        $links = $query->orderByDesc('stats.click_count')->paginate($perPage);

        return $this->success($links->items(), [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $links->currentPage(),
            'last_page' => $links->lastPage(),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $link = Link::leftJoinSub(
            LinkClick::selectRaw('link_id, COUNT(*) as click_count, MAX(created_at) as last_clicked_at')
                ->groupBy('link_id'),
            'stats', 'links.id', '=', 'stats.link_id'
        )
        ->selectRaw('links.*, COALESCE(stats.click_count, 0) as click_count, stats.last_clicked_at')
        ->where('links.id', $id)
        ->first();

        if (!$link) {
            return $this->error('Not found', 'Link not found', 404);
        }

        $dailyClicks = LinkClick::where('link_id', $id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('total', 'day');

        $topReferrers = LinkClick::where('link_id', $id)
            ->whereNotNull('referrer')
            ->where('referrer', '<>', '')
            ->selectRaw('referrer, COUNT(*) as total')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $devices = LinkClick::where('link_id', $id)
            ->whereNotNull('device_type')
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        return $this->success([
            'link' => $link,
            'daily_clicks' => $dailyClicks,
            'top_referrers' => $topReferrers,
            'devices' => $devices,
        ]);
    }
}