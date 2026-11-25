<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexsus Tasks — React</title>
    <script>
        window.NEXSUS_API_BASE = '/api/v1';
        window.NEXSUS_API_TOKEN = '{{ session("api_token", "") ?: config("app.api_token", "") }}';
    </script>
    @php
        // Fall back to the Render env token if session token is empty
        if (empty(session('api_token'))) {
            echo '<script>window.NEXSUS_API_TOKEN = "' . addslashes(env('API_TOKEN', '')) . '";</script>';
        }
    @endphp
    <link rel="stylesheet" href="{{ asset('tasks-app/assets/index-Ded3ldfS.css') }}">
</head>
<body>
    <div id="root"></div>
    <script type="module" src="{{ asset('tasks-app/assets/index-Cf8hDKO7.js') }}"></script>
</body>
</html>