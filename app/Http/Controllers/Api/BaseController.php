<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function success($data = null, array $meta = []): JsonResponse
    {
        $response = ['success' => true, 'data' => $data];
        if ($meta) {
            $response['meta'] = $meta;
        }
        return response()->json($response);
    }

    protected function error(string $error, string $message = '', int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $error,
            'message' => $message,
        ], $status);
    }

    protected function applyDateFilters($query, Request $request)
    {
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to') . ' 23:59:59');
        }
    }

    protected function applyClickFilters($query, Request $request)
    {
        $this->applyDateFilters($query, $request);

        $filters = ['link_id', 'device_type', 'browser', 'os', 'country', 'utm_source', 'utm_medium', 'utm_campaign'];
        foreach ($filters as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
    }

    protected function getUserId(Request $request): ?int
    {
        if ($request->user()) {
            return $request->user()->id;
        }
        return \App\Models\User::where('role', 'admin')->value('id');
    }
}