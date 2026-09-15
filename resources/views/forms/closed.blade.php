<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Closed — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-body text-center py-5">
                <i class="bi bi-lock display-1 text-muted"></i>
                <h3 class="mt-4">Form Closed</h3>
                <p class="text-muted">
                    This form is no longer accepting responses.
                </p>
                @if($form->closed_at)
                <p class="text-muted">
                    <small>Closed on {{ $form->closed_at->format('F j, Y') }}</small>
                </p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
