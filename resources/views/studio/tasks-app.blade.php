@php
    $manifestPath = public_path('tasks-app/.vite/manifest.json');
    $jsPath = 'tasks-app/assets/index_D_3tolSJ.js';
    $cssPath = 'tasks-app/assets/index-Ded3ldfS.css';
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entry = $manifest['index.html'] ?? null;
        if ($entry) {
            $jsPath = 'tasks-app/' . $entry['file'];
            if (!empty($entry['css'])) {
                $cssPath = 'tasks-app/' . $entry['css'][0];
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexsus Tasks — React</title>
    <script>
        window.NEXSUS_API_BASE = '/api/v1';
    </script>
    @php
        $token = session('api_token') ?: env('API_TOKEN', '');
        echo '<script>window.NEXSUS_API_TOKEN = ' . json_encode($token) . ';</script>';
    @endphp
    <link rel="stylesheet" href="{{ asset($cssPath) }}">
</head>
<body>
    <div id="root"></div>
    <script type="module" src="{{ asset($jsPath) }}"></script>
</body>
</html>