<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .checkmark i {
            color: white;
            font-size: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-body text-center py-5">
                <div class="checkmark">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h3 class="mt-4">Thank You!</h3>
                <p class="text-muted">
                    Your response has been recorded successfully.
                </p>
                <p class="text-muted">
                    <small>for {{ $formTitle }}</small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
