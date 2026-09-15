<?php

namespace App\Support;

/**
 * Lightweight user-agent parser for device type, browser, version and OS.
 * Kept dependency-free (regex based) and intentionally conservative.
 */
class UserAgentParser
{
    public const BOT_PATTERNS = [
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baidubot', 'yandexbot',
        'facebookexternalhit', 'twitterbot', 'linkedinbot', 'whatsapp', 'telegrambot',
        'discordbot', 'redditbot', 'pinterest', 'rogerbot', 'applebot', 'semrushbot',
        'ahrefsbot', 'mj12bot', 'dotbot', 'petalbot', 'bytespider', 'sogou', 'exabot',
        'ia_archiver', 'google-inspectiontool', 'bingpreview', 'uptimerobot', 'monitoring',
        'puppeteer', 'headlesschrome', 'python-requests', 'curl/', 'wget', 'postmanruntime',
    ];

    public static function parse(?string $userAgent): array
    {
        $ua = $userAgent ?? '';
        $uaLower = strtolower($ua);

        $isBot = $ua === '' || self::matchesBot($uaLower);

        $deviceType = self::deviceType($uaLower, $isBot);
        $os = self::operatingSystem($uaLower);
        [$browser, $browserVersion] = self::browser($uaLower);

        return [
            'device_type' => $isBot ? 'bot' : $deviceType,
            'browser' => $isBot ? 'bot' : $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
            'is_bot' => $isBot,
        ];
    }

    protected static function matchesBot(string $uaLower): bool
    {
        foreach (self::BOT_PATTERNS as $token) {
            if (strpos($uaLower, $token) !== false) {
                return true;
            }
        }
        return false;
    }

    protected static function deviceType(string $uaLower, bool $isBot): string
    {
        if ($isBot) {
            return 'bot';
        }
        if (preg_match('/ipad|tablet|tab|kindle|silk|playbook/i', $uaLower)) {
            return 'tablet';
        }
        if (preg_match('/(?=.*mobile)android/i', $uaLower) || preg_match('/iphone|ipod|windows phone|wpdesktop|blackberry|opera mini|mobile/i', $uaLower)) {
            return 'mobile';
        }
        if (preg_match('/android/i', $uaLower)) {
            return 'tablet';
        }
        return 'desktop';
    }

    protected static function operatingSystem(string $uaLower): string
    {
        if (strpos($uaLower, 'iphone') !== false || strpos($uaLower, 'ipod') !== false) {
            return 'iOS';
        }
        if (strpos($uaLower, 'ipad') !== false) {
            return 'iPadOS';
        }
        if (strpos($uaLower, 'android') !== false) {
            return 'Android';
        }
        if (strpos($uaLower, 'windows phone') !== false) {
            return 'Windows Phone';
        }
        if (strpos($uaLower, 'windows') !== false || strpos($uaLower, 'win32') !== false) {
            if (preg_match('/windows nt (\d+\.\d+)/i', $uaLower, $m)) {
                $versions = ['10.0' => 'Windows 10/11', '6.3' => 'Windows 8.1', '6.2' => 'Windows 8', '6.1' => 'Windows 7', '6.0' => 'Windows Vista'];
                return $versions[$m[1]] ?? 'Windows';
            }
            return 'Windows';
        }
        if (strpos($uaLower, 'mac os x') !== false || strpos($uaLower, 'macintosh') !== false) {
            return 'macOS';
        }
        if (strpos($uaLower, 'linux') !== false) {
            return 'Linux';
        }
        if (strpos($uaLower, 'ubuntu') !== false) {
            return 'Ubuntu';
        }
        if (strpos($uaLower, 'chrome os') !== false) {
            return 'ChromeOS';
        }
        return 'Unknown';
    }

    protected static function browser(string $uaLower): array
    {
        $detect = function (array $patterns, string $name) use ($uaLower) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $uaLower, $m)) {
                    return [$name, $m[1] ?? ''];
                }
            }
            return null;
        };

        $candidates = [
            [["/edg(?:a|ios|)\/([\d\.]+)/i", "/edge\/([\d\.]+)/i"], 'Edge'],
            [["/opr\/([\d\.]+)/i", "/opera\/([\d\.]+)/i"], 'Opera'],
            [["/samsungbrowser\/([\d\.]+)/i"], 'Samsung Internet'],
            [["/ucbrowser\/([\d\.]+)/i"], 'UC Browser'],
            [["/brave\/([\d\.]+)/i"], 'Brave'],
            [["/vivaldi\/([\d\.]+)/i"], 'Vivaldi'],
            [["/firefox\/([\d\.]+)/i", "/fxios\/([\d\.]+)/i"], 'Firefox'],
            [["/msie ([\d\.]+)/i", "/trident.*rv:([\d\.]+)/i"], 'Internet Explorer'],
            [["/chrome\/([\d\.]+)/i", "/crios\/([\d\.]+)/i"], 'Chrome'],
            [["/safari\/([\d\.]+)/i", "/version\/([\d\.]+).*safari/i"], 'Safari'],
        ];

        foreach ($candidates as [$patterns, $name]) {
            $result = $detect($patterns, $name);
            if ($result) {
                return $result;
            }
        }

        if (strpos($uaLower, 'bot') !== false || strpos($uaLower, 'crawler') !== false || strpos($uaLower, 'spider') !== false) {
            return ['bot', ''];
        }

        return ['Other', ''];
    }
}