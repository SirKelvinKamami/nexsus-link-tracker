<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ApiToken;

class TokenController extends BaseController
{
    /**
     * List all tokens for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $this->getUserId();
        $perPage = $request->input('per_page', 50);

        $tokens = ApiToken::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $tokens->getCollection()->transform(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'prefix' => $token->prefix,
                'scopes' => $token->scopes,
                'is_active' => $token->is_active,
                'usage_count' => $token->usage_count,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'expires_at' => $token->expires_at?->toISOString(),
                'created_at' => $token->created_at->toISOString(),
            ];
        });

        return $this->success($tokens);
    }

    /**
     * Create a new token.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'required|array',
            'scopes.*' => 'string|in:read,write,admin',
            'expires_in' => 'nullable|integer|min:1|max:365',
        ]);

        $userId = $this->getUserId();
        $result = ApiToken::createToken($request->name, $request->scopes, $userId);

        if ($request->expires_in) {
            ApiToken::where('id', $result['id'])->update([
                'expires_at' => now()->addDays($request->expires_in),
            ]);
        }

        return $this->success([
            'id' => $result['id'],
            'name' => $result['name'],
            'prefix' => $result['prefix'],
            'token' => $result['token'],
            'message' => 'Copy this token now. It will not be shown again.',
        ], 201);
    }

    /**
     * Delete a token.
     */
    public function destroy($id)
    {
        $userId = $this->getUserId();

        $token = ApiToken::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$token) {
            return $this->error('Token not found', '', 404);
        }

        $token->delete();

        return $this->success(['deleted' => true]);
    }

    /**
     * Revoke a token.
     */
    public function revoke($id)
    {
        $userId = $this->getUserId();

        $token = ApiToken::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$token) {
            return $this->error('Token not found', '', 404);
        }

        $token->update(['is_active' => false]);

        return $this->success(['revoked' => true]);
    }

    /**
     * Activate a token.
     */
    public function activate($id)
    {
        $userId = $this->getUserId();

        $token = ApiToken::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$token) {
            return $this->error('Token not found', '', 404);
        }

        $token->update(['is_active' => true]);

        return $this->success(['activated' => true]);
    }
}
