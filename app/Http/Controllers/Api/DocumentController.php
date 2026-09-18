<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Link;

class DocumentController extends BaseController
{
    /**
     * List all documents for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $this->getUserId($request);
        $perPage = $request->input('per_page', 50);

        $query = Link::where('user_id', $userId)
            ->where('type', 'document');

        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $documents = $query->orderByDesc('created_at')
            ->paginate($perPage);

        $documents->getCollection()->transform(function ($doc) {
            $typeParams = json_decode($doc->type_params, true) ?? [];
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'description' => $typeParams['description'] ?? '',
                'original_name' => $typeParams['original_name'] ?? '',
                'file_size' => $typeParams['file_size'] ?? 0,
                'file_size_formatted' => $this->formatFileSize($typeParams['file_size'] ?? 0),
                'mime_type' => $typeParams['mime_type'] ?? '',
                'extension' => $typeParams['extension'] ?? '',
                'download_count' => $doc->click_number,
                'download_url' => url("/download/{$doc->id}"),
                'view_url' => url("/document/{$doc->id}"),
                'created_at' => $doc->created_at->toISOString(),
                'updated_at' => $doc->updated_at->toISOString(),
            ];
        });

        return $this->success($documents);
    }

    /**
     * Get a single document by ID.
     */
    public function show(Request $request, $id)
    {
        $userId = $this->getUserId($request);

        $doc = Link::where('id', $id)
            ->where('user_id', $userId)
            ->where('type', 'document')
            ->first();

        if (!$doc) {
            return $this->error('Document not found', 404);
        }

        $typeParams = json_decode($doc->type_params, true) ?? [];

        $data = [
            'id' => $doc->id,
            'title' => $doc->title,
            'description' => $typeParams['description'] ?? '',
            'original_name' => $typeParams['original_name'] ?? '',
            'file_size' => $typeParams['file_size'] ?? 0,
            'file_size_formatted' => $this->formatFileSize($typeParams['file_size'] ?? 0),
            'mime_type' => $typeParams['mime_type'] ?? '',
            'extension' => $typeParams['extension'] ?? '',
            'download_count' => $doc->click_number,
            'download_url' => url("/download/{$doc->id}"),
            'view_url' => url("/document/{$doc->id}"),
            'created_at' => $doc->created_at->toISOString(),
            'updated_at' => $doc->updated_at->toISOString(),
        ];

        return $this->success($data);
    }

    /**
     * Get download statistics for a document.
     */
    public function stats(Request $request, $id)
    {
        $userId = $this->getUserId($request);

        $doc = Link::where('id', $id)
            ->where('user_id', $userId)
            ->where('type', 'document')
            ->first();

        if (!$doc) {
            return $this->error('Document not found', 404);
        }

        $clicks = \App\Models\LinkClick::where('link_id', $id);

        $totalDownloads = (clone $clicks)->count();
        $todayDownloads = (clone $clicks)->whereDate('created_at', now()->toDateString())->count();
        $weekDownloads = (clone $clicks)->where('created_at', '>=', now()->subDays(7))->count();
        $monthDownloads = (clone $clicks)->where('created_at', '>=', now()->subDays(30))->count();

        $start = now()->subDays(29)->startOfDay();
        $dailyRows = (clone $clicks)->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $daily[$d] = (int) ($dailyRows[$d] ?? 0);
        }

        $topReferrers = (clone $clicks)
            ->selectRaw('referrer, COUNT(*) as total')
            ->whereNotNull('referrer')
            ->where('referrer', '<>', '')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $devices = (clone $clicks)
            ->selectRaw('device_type, COUNT(*) as total')
            ->whereNotNull('device_type')
            ->where('device_type', '<>', '')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        $data = [
            'document_id' => $doc->id,
            'title' => $doc->title,
            'total_downloads' => $totalDownloads,
            'today_downloads' => $todayDownloads,
            'week_downloads' => $weekDownloads,
            'month_downloads' => $monthDownloads,
            'daily' => $daily,
            'top_referrers' => $topReferrers,
            'devices' => $devices,
        ];

        return $this->success($data);
    }

    /**
     * Delete a document.
     */
    public function destroy(Request $request, $id)
    {
        $userId = $this->getUserId($request);

        $doc = Link::where('id', $id)
            ->where('user_id', $userId)
            ->where('type', 'document')
            ->first();

        if (!$doc) {
            return $this->error('Document not found', 404);
        }

        $filePath = $doc->link;
        $disk = config('documents.storage_disk', 'local');

        if ($disk === 'local') {
            $fullPath = storage_path('app/' . $filePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        } else {
            Storage::disk($disk)->delete($filePath);
        }

        $doc->delete();

        return $this->success(['deleted' => true]);
    }

    /**
     * Format file size for display.
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 1);
        $i = (int) floor(log($bytes) / log(1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
    }
}
