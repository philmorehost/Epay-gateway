<?php
// app/core/host_check.php

function verify_host() {
    global $db;

    $current_host = $_SERVER['HTTP_HOST'];

    // 1. Get the base URL from settings (this is the main domain)
    $base_url_setting = $db->query("SELECT value FROM settings WHERE setting = 'base_url'")->fetch_assoc();
    $base_host = parse_url($base_url_setting['value'], PHP_URL_HOST);

    // 2. Check if the current host matches the main domain
    if ($current_host === $base_host) {
        return; // Host is valid
    }

    // 3. Check if the current host is an approved reseller domain
    $stmt = $db->prepare("SELECT user_id FROM reseller_settings WHERE custom_domain = ?");
    $stmt->bind_param('s', $current_host);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return; // Host is a valid reseller domain
    }

    // 4. If no match, display a themed error page
    http_response_code(404); // Not Found is appropriate

    // Themed Error Page
    $safe_current_host = htmlspecialchars($current_host);
    $safe_base_url = htmlspecialchars($base_url_setting['value']);

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error: Domain Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css"> <!-- Assuming a root-level css directory -->
</head>
<body>
<div class="installer-container">
    <div class="installer-header text-center">
        <h1>Domain Not Found</h1>
        <p class="lead">The domain <strong>$safe_current_host</strong> is not configured to be used with this service.</p>
    </div>
    <div class="alert alert-danger">
        If you are a customer, please contact the person who provided you with this link.
    </div>
    <div class="text-center mt-4">
        <p>If you are trying to access our main site, please <a href="$safe_base_url">click here</a>.</p>
    </div>
</div>
</body>
</html>
HTML;
    exit;
}
