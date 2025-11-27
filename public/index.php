<?php
// public/index.php

// Check if the installer needs to be run.
// The bootstrap file will redirect to the installer if config.php is not found.
require_once '../app/core/bootstrap.php';

// If the user is logged in, redirect them to the client dashboard.
// The landing page is for guests.
if (isset($_SESSION['user_id'])) {
    header('Location: client_dashboard.php');
    exit;
}

// --- Display New Landing Page ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to HostBill-1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="css/landing.css">
</head>
<body>

    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand logo" href="#">HostBill-1</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="#plans">Hosting</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Domains</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Support</a></li>
                        <li class="nav-item"><a href="login.php" class="client-login-btn">Client Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section with Domain Search -->
    <section class="hero">
        <div class="container">
            <h1>Powerful Web Hosting</h1>
            <p>Starting at $2.95/month. Get your website online in minutes.</p>

            <form action="domain_checker.php" method="post" class="domain-search-form">
                <input type="text" name="domain" placeholder="Find your new domain name" required>
                <button type="submit">Search</button>
            </form>

            <div class="tlds">
                <span class="tld">.com <span class="tld-price">$9.25</span></span>
                <span class="tld">.net <span class="tld-price">$11.25</span></span>
                <span class="tld">.org <span class="tld-price">$10.95</span></span>
                <span class="tld">.io <span class="tld-price">$14.50</span></span>
            </div>
        </div>
    </section>

    <!-- Hosting Plans -->
    <section id="plans" class="hosting-plans">
        <div class="container">
            <h2 class="section-title">Choose Your Web Hosting Plan</h2>
            <div class="row">
                <!-- Plan 1 -->
                <div class="col-md-4 mb-4">
                    <div class="plan-card">
                        <h3 class="plan-title">Starter</h3>
                        <p class="plan-price">$2.95 <span class="term">/mo</span></p>
                        <ul class="plan-features">
                            <li>1 Website</li>
                            <li>50 GB SSD Storage</li>
                            <li>Unmetered Bandwidth</li>
                            <li>Free SSL Certificate</li>
                        </ul>
                        <a href="#" class="order-now-btn">Order Now</a>
                    </div>
                </div>
                <!-- Plan 2 -->
                <div class="col-md-4 mb-4">
                    <div class="plan-card">
                        <h3 class="plan-title">Business</h3>
                        <p class="plan-price">$5.45 <span class="term">/mo</span></p>
                        <ul class="plan-features">
                            <li>100 Websites</li>
                            <li>100 GB SSD Storage</li>
                            <li>Unmetered Bandwidth</li>
                            <li>Free SSL & Domain Name</li>
                        </ul>
                        <a href="#" class="order-now-btn">Order Now</a>
                    </div>
                </div>
                <!-- Plan 3 -->
                <div class="col-md-4 mb-4">
                    <div class="plan-card">
                        <h3 class="plan-title">Pro</h3>
                        <p class="plan-price">$12.95 <span class="term">/mo</span></p>
                        <ul class="plan-features">
                            <li>Unlimited Websites</li>
                            <li>200 GB SSD Storage</li>
                            <li>Unmetered Bandwidth</li>
                            <li>Free SSL, Domain & CDN</li>
                        </ul>
                        <a href="#" class="order-now-btn">Order Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> HostBill-1. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
