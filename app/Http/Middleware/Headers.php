<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Headers
{
    public function handle(Request $request, Closure $next)
    {
        // Check if FORCE_HTTPS is set to true
        if (env('FORCE_HTTPS') == 'true') {
            \URL::forceScheme('https'); // Force HTTPS
            header("Content-Security-Policy: upgrade-insecure-requests");
        }

        // Determine whether the CLIENT connection is secure. Behind Render,
        // Cloudflare, or any TLS-terminating proxy, PHP sees plain HTTP and
        // $_SERVER['HTTPS'] is always 'off' — the old check here redirected
        // every request to the very same https:// URL (infinite loop).
        // Trust the forwarded protocol header instead: it says what the
        // client actually used, and matches TrustProxies ('*').
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        $isSecure = $forwardedProto === 'https'
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        // Check if FORCE_ROUTE_HTTPS is set to true — redirect only clients
        // that are genuinely still on plain HTTP.
        if (env('FORCE_ROUTE_HTTPS') == 'true' && !$isSecure) {
            $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url");
            exit();
        }

        return $next($request);
    }
}
