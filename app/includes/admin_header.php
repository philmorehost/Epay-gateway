<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo $page_title ?? 'Dashboard'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">View Main Site</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/index.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/products.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/servers.php">Servers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/resellers.php">Resellers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/manual_payments.php">Manual Payments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/settings.php">SMTP Settings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/email_templates.php">Email Templates</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/cron.php">Cron Jobs</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="main-content">
