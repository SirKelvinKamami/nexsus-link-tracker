<?php

namespace App\Services;

use App\Models\Link;
use App\Models\LinkClick;
use App\Support\UserAgentParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClickTracker
{
    public static function record(Link $link, ?Request $request = null): ?LinkClick
    {
        $request = $request ?? request();
        $ua = $request->userAgent() ?? '';
        $parsed = UserAgentParser::parse($ua);
        $ip = $request->ip();

        try {
            $click = LinkClick::create([
                'link_id' => $link->id,
                'user_id' => $link->user_id,
                'project_id' => $link->project_id,
                'session_id' => class_exists('Illuminate\Support\Facades\Session') && app()->bound('session.store')
                    ? Str::limit(session()->getId(), 100)
                    : null,
                'ip_hash' => $ip ? sha1($ip) : null,
                'referrer' => Str::limit($request->headers->get('referer') ?? $request->headers->get('referrer'), 2048),
                'utm_source' => $request->query('utm_source'),
                'utm_medium' => $request->query('utm_medium'),
                'utm_campaign' => $request->query('utm_campaign'),
                'utm_term' => $request->query('utm_term'),
                'utm_content' => $request->query('utm_content'),
                'user_agent' => Str::limit($ua, 512),
                'device_type' => $parsed['device_type'],
                'browser' => $parsed['browser'],
                'browser_version' => $parsed['browser_version'],
                'os' => $parsed['os'],
                'country' => null,
                'language' => $request->getPreferredLanguage() ? Str::limit($request->getPreferredLanguage(), 8) : null,
            ]);

            WebhookService::dispatch('click', [
                'click_id' => $click->id,
                'link_id' => $link->id,
                'link_title' => $link->title,
                'link_url' => $link->link,
                'device_type' => $click->device_type,
                'browser' => $click->browser,
                'os' => $click->os,
                'referrer' => $click->referrer,
                'utm_source' => $click->utm_source,
                'utm_medium' => $click->utm_medium,
                'utm_campaign' => $click->utm_campaign,
            ], $link->user_id);

            return $click;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}