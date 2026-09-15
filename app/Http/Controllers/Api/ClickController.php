<?php

namespace App\Http\Controllers\Api;

use App\Models\LinkClick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClickController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = LinkClick::with('link:id,title,link,type');

        $userId = $this->getUserId($request);
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $this->applyClickFilters($query, $request);

        $total = $query->count();
        $perPage = min((int) $request->input('per_page', 50), 100);
        $clicks = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success($clicks->items(), [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $clicks->currentPage(),
            'last_page' => $clicks->lastPage(),
        ]);
    }
}