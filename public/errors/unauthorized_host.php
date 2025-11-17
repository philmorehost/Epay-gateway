<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Unauthorized Host</title>
    <!-- Using Bootstrap 5 from a CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Simple inline fintech-style theming */
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }
        .card-body {
            padding: 2.5rem;
            text-align: center;
        }
        .card-title {
            font-weight: 700;
            color: #dc3545; /* Bootstrap's danger color */
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        .card-text {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
    </style>
    <!-- Google Fonts for a more modern look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title">Configuration Error</h1>
                        <p class="card-text">
                            This domain name is not configured to access our services. If you are the administrator,
                            please add this domain in your reseller settings. If you are a customer, please contact
                            the service provider.
                        </p>
                        <a href="<?= htmlspecialchars(BASE_URL) ?>" class="btn btn-primary">Go to Main Site</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
