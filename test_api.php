<?php
// Quick API verification after branding changes
$base = 'http://127.0.0.1:8000';

function test($name, $method, $url, $auth = null, $data = null) {
    $header = "Accept: application/json\r\n";
    if ($auth) {
        $header .= "Authorization: $auth\r\n";
    }
    if ($data !== null) {
        $header .= "Content-Type: application/json\r\n";
    }
    $opts = [
        'http' => [
            'method' => $method,
            'header' => $header,
            'ignore_errors' => true,
        ]
    ];
    if ($data !== null) {
        $opts['http']['content'] = $data;
    }
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    $httpCode = $http_response_header[0] ?? 'Unknown';
    echo "$method $url: $httpCode\n";
    return $httpCode;
}

$token = 'C492508D9854B7E260CDFE204A792115F6B7E4A88B5D31E6182E7F577CBCB2BADEA845A800C0314';
$authHeader = "Bearer $token";

echo "=== API Verification After Branding Changes ===\n";

test('Health', 'GET', "$base/api/v1/health");
test('GET Tasks', 'GET', "$base/api/v1/tasks", $authHeader);
test('Daily Digest', 'GET', "$base/api/v1/tasks/daily-digest", $authHeader);
test('AI Parse', 'POST', "$base/api/v1/tasks/parse", $authHeader, json_encode(['text' => 'Test']));
test('Analytics Overview', 'GET', "$base/api/v1/tasks/analytics/overview", $authHeader);
test('Schedule', 'GET', "$base/api/v1/tasks/schedule", $authHeader);
test('Create Reminder', 'POST', "$base/api/v1/reminders", $authHeader, json_encode([
    'task_id' => 166171470, 'trigger_type' => 'time', 'trigger_value' => '2026-12-31 23:59:59', 'channel' => 'email',
]));
test('GET Reminders', 'GET', "$base/api/v1/reminders", $authHeader);

echo "\n=== VERIFICATION COMPLETE ===\n";
