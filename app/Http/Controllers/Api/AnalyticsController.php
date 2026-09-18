<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnalyticsController extends BaseController
{
    public function overview(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $query = LinkClick::query();
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyDateFilters($query, $request);

        $total = (clone $query)->count();
        $today = (clone $query)->whereDate('created_at', now()->toDateString())->count();
        $week = (clone $query)->where('created_at', '>=', now()->subDays(7))->count();
        $month = (clone $query)->where('created_at', '>=', now()->subDays(30))->count();

        return $this->success([
            'total' => $total,
            'today' => $today,
            'week' => $week,
            'month' => $month,
        ]);
    }

    public function topLinks(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);

        $query = Link::query();
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->leftJoinSub(
            LinkClick::selectRaw('link_id, COUNT(*) as clicks, MAX(created_at) as last_clicked_at')
                ->groupBy('link_id'),
            'stats', 'links.id', '=', 'stats.link_id'
        )
        ->selectRaw('links.id, links.title, links.link, links.type, COALESCE(stats.clicks, 0) as clicks, stats.last_clicked_at')
        ->whereNotNull('links.link')
        ->where('links.link', '<>', '');

        $top = $query->orderByDesc('stats.clicks')->take(10)->get();

        return $this->success($top);
    }

    public function byDevice(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $query = LinkClick::query()->whereNotNull('device_type')->where('device_type', '<>', '');
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyDateFilters($query, $request);

        $data = $query->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        return $this->success($data);
    }

    public function byBrowser(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $query = LinkClick::query()->whereNotNull('browser')->where('browser', '<>', '');
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyDateFilters($query, $request);

        $data = $query->selectRaw('browser, COUNT(*) as total')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return $this->success($data);
    }

    public function byOs(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $query = LinkClick::query()->whereNotNull('os')->where('os', '<>', '');
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyDateFilters($query, $request);

        $data = $query->selectRaw('os, COUNT(*) as total')
            ->groupBy('os')
            ->orderByDesc('total')
            ->get();

        return $this->success($data);
    }

    public function byReferrer(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $query = LinkClick::query()->whereNotNull('referrer')->where('referrer', '<>', '');
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyDateFilters($query, $request);

        $data = $query->selectRaw('referrer, COUNT(*) as total')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return $this->success($data);
    }

    public function byUtm(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);

        $base = LinkClick::query();
        if ($userId) {
            $base->where('user_id', $userId);
        }

        $sources = (clone $base)->whereNotNull('utm_source')->where('utm_source', '<>', '')
            ->selectRaw('utm_source as label, COUNT(*) as total')
            ->groupBy('utm_source')->orderByDesc('total')->take(10)->get();

        $mediums = (clone $base)->whereNotNull('utm_medium')->where('utm_medium', '<>', '')
            ->selectRaw('utm_medium as label, COUNT(*) as total')
            ->groupBy('utm_medium')->orderByDesc('total')->take(10)->get();

        $campaigns = (clone $base)->whereNotNull('utm_campaign')->where('utm_campaign', '<>', '')
            ->selectRaw('utm_campaign as label, COUNT(*) as total')
            ->groupBy('utm_campaign')->orderByDesc('total')->take(10)->get();

        return $this->success([
            'sources' => $sources,
            'mediums' => $mediums,
            'campaigns' => $campaigns,
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $days = min((int) $request->input('days', 30), 90);
        $start = now()->subDays($days - 1)->startOfDay();

        $query = LinkClick::query()->where('created_at', '>=', $start);
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyDateFilters($query, $request);

        $rows = $query->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $series[] = ['date' => $d, 'clicks' => (int) ($rows[$d] ?? 0)];
        }

        return $this->success($series);
    }

    public function pageViews(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $handle = $userId
            ? \App\Models\User::where('id', $userId)->value('handle')
            : null;

        if (!$handle) {
            return $this->success(['all' => 0, 'day' => 0, 'week' => 0, 'month' => 0, 'year' => 0]);
        }

        try {
            $data = [
                'all' => visits('App\Models\User', $handle)->count(),
                'day' => visits('App\Models\User', $handle)->period('day')->count(),
                'week' => visits('App\Models\User', $handle)->period('week')->count(),
                'month' => visits('App\Models\User', $handle)->period('month')->count(),
                'year' => visits('App\Models\User', $handle)->period('year')->count(),
            ];
        } catch (\Throwable $e) {
            $data = ['all' => 0, 'day' => 0, 'week' => 0, 'month' => 0, 'year' => 0];
        }

        return $this->success($data);
    }

    public function exportClicks(Request $request): Response
    {
        $userId = $this->getUserId($request);
        $query = LinkClick::with('link:id,title,link')
            ->whereNotNull('link_id');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyDateFilters($query, $request);

        $clicks = $query->orderByDesc('created_at')->limit(10000)->get();

        $csv = "ID,Link ID,Link Title,URL,Device,Browser,OS,Referrer,UTM Source,UTM Medium,UTM Campaign,Country,Created At\n";

        foreach ($clicks as $click) {
            $link = $click->link;
            $csv .= implode(',', [
                $click->id,
                $click->link_id,
                '"' . str_replace('"', '""', $link->title ?? '') . '"',
                '"' . str_replace('"', '""', $link->link ?? '') . '"',
                $click->device_type,
                $click->browser,
                $click->os,
                '"' . str_replace('"', '""', $click->referrer ?? '') . '"',
                $click->utm_source,
                $click->utm_medium,
                $click->utm_campaign,
                $click->country,
                $click->created_at->toIso8601String(),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clicks-export.csv"',
        ]);
    }

    public function exportLinks(Request $request): Response
    {
        $userId = $this->getUserId($request);
        $query = Link::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $links = $query->orderByDesc('created_at')->limit(5000)->get();

        $csv = "ID,Title,URL,Type,Click Count,Last Clicked,Created At\n";

        foreach ($links as $link) {
            $clickCount = LinkClick::where('link_id', $link->id)->count();
            $lastClicked = LinkClick::where('link_id', $link->id)->max('created_at');

            $csv .= implode(',', [
                $link->id,
                '"' . str_replace('"', '""', $link->title ?? '') . '"',
                '"' . str_replace('"', '""', $link->link ?? '') . '"',
                $link->type,
                $clickCount,
                $lastClicked ? $lastClicked : '',
                $link->created_at->toIso8601String(),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="links-export.csv"',
        ]);
    }
}
