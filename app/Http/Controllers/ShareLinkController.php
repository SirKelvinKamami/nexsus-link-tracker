<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ShareLink;

class ShareLinkController extends Controller
{
    /**
     * List all shareable links for the authenticated user.
     */
    public function index()
    {
        $userId = Auth::id();
        $shareLinks = ShareLink::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return view('studio.share-links.index', compact('shareLinks'));
    }

    /**
     * Show form for creating a new shareable link.
     */
    public function create()
    {
        return view('studio.share-links.create');
    }

    /**
     * Store a new shareable link.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:dashboard,analytics,form,landing_page,custom',
            'target_id' => 'nullable|string|max:255',
            'expires_in' => 'nullable|integer|min:1|max:365',
            'password' => 'nullable|string|max:255',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $userId = Auth::id();
        $token = Str::random(32);

        $shareLink = ShareLink::create([
            'user_id' => $userId,
            'name' => $request->name,
            'token' => $token,
            'type' => $request->type,
            'target_id' => $request->target_id,
            'expires_at' => $request->expires_in ? now()->addDays($request->expires_in) : null,
            'password' => $request->password ? bcrypt($request->password) : null,
            'max_uses' => $request->max_uses,
            'use_count' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('share-links.index')
            ->with('success', 'Shareable link created. URL: ' . url("/share/{$token}"));
    }

    /**
     * Access a shared link (public).
     */
    public function access(Request $request, $token)
    {
        $shareLink = ShareLink::where('token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        if ($shareLink->isExpired()) {
            return view('share.expired', ['shareLink' => $shareLink]);
        }

        if ($shareLink->max_uses && $shareLink->use_count >= $shareLink->max_uses) {
            return view('share.limit-reached', ['shareLink' => $shareLink]);
        }

        if ($shareLink->password) {
            if ($request->isMethod('post')) {
                if (!Hash::check($request->password, $shareLink->password)) {
                    return view('share.password', ['shareLink' => $shareLink, 'error' => 'Incorrect password']);
                }
                session(["share_{$token}_auth" => true]);
            } elseif (!session("share_{$token}_auth")) {
                return view('share.password', ['shareLink' => $shareLink]);
            }
        }

        $shareLink->increment('use_count');

        switch ($shareLink->type) {
            case 'dashboard':
                return view('share.dashboard', ['shareLink' => $shareLink]);
            case 'analytics':
                return view('share.analytics', ['shareLink' => $shareLink]);
            case 'form':
                return redirect()->route('forms.show', ['slug' => $shareLink->target_id]);
            case 'landing_page':
                return redirect()->route('landing-pages.show', ['slug' => $shareLink->target_id]);
            default:
                return view('share.custom', ['shareLink' => $shareLink]);
        }
    }

    /**
     * Delete a shareable link.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $shareLink = ShareLink::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $shareLink->delete();

        return redirect()->route('share-links.index')
            ->with('success', 'Shareable link deleted.');
    }

    /**
     * Toggle link active status.
     */
    public function toggle($id)
    {
        $userId = Auth::id();
        $shareLink = ShareLink::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $shareLink->update(['is_active' => !$shareLink->is_active]);

        return redirect()->route('share-links.index')
            ->with('success', $shareLink->is_active ? 'Link activated.' : 'Link deactivated.');
    }
}
