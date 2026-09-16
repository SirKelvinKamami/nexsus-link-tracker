<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ApiToken;

class ApiTokenController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $tokens = ApiToken::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return view('studio.api-tokens.index', compact('tokens'));
    }

    public function create()
    {
        return view('studio.api-tokens.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'required|array',
            'scopes.*' => 'string|in:read,write,admin',
            'expires_in' => 'nullable|integer|min:1|max:365',
        ]);

        $result = ApiToken::createToken(
            $request->name,
            $request->scopes,
            Auth::id()
        );

        if ($request->expires_in) {
            ApiToken::where('id', $result['id'])->update([
                'expires_at' => now()->addDays($request->expires_in),
            ]);
        }

        return redirect()->route('api-tokens.index')
            ->with('success', "Token created. Copy it now — it won't be shown again: {$result['token']}");
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $token = ApiToken::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $token->delete();

        return redirect()->route('api-tokens.index')
            ->with('success', 'Token deleted.');
    }

    public function revoke($id)
    {
        $userId = Auth::id();
        $token = ApiToken::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $token->update(['is_active' => false]);

        return redirect()->route('api-tokens.index')
            ->with('success', 'Token revoked.');
    }

    public function activate($id)
    {
        $userId = Auth::id();
        $token = ApiToken::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $token->update(['is_active' => true]);

        return redirect()->route('api-tokens.index')
            ->with('success', 'Token activated.');
    }
}
