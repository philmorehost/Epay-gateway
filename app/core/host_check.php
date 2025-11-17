<?php
// app/core/host_check.php

/**
 * This script checks the HTTP_HOST of incoming requests to determine if it's the main application,
 * an approved reseller's custom domain, or an unauthorized domain.
 *
 * - If it's a valid reseller domain, it sets a global flag and loads reseller-specific settings.
 * - If it's an unauthorized domain, it halts execution and displays a themed error page.
 * - If it's the main application domain, it simply allows the regular bootstrap to continue.
 */

// The global $db object is expected to be available from bootstrap.php
if (!isset($db)) {
    die("Database connection is not available for host check.");
}

// Get the current hostname
$current_host = $_SERVER['HTTP_HOST'];

// Get the base URL from the config, and parse it to get the host
$config_host = parse_url(BASE_URL, PHP_URL_HOST);

// Define a global variable to hold reseller info if found
$reseller_info = null;
define('IS_RESELLER_STOREFRONT', false);

// 1. Check if the current host matches the main application host
if ($current_host === $config_host) {
    // It's the main site, do nothing and let the application proceed normally.
    return;
}

// 2. If it's not the main site, check if it's a valid reseller custom domain
$stmt = $db->prepare("SELECT user_id, custom_domain FROM reseller_settings WHERE custom_domain = ? AND custom_domain IS NOT NULL AND custom_domain != ''");
$stmt->bind_param('s', $current_host);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // --- Valid Reseller Domain Found ---
    $reseller_info = $result->fetch_assoc();

    // Define a constant to be used throughout the application
    define('IS_RESELLER_STOREFRONT', true);
    define('RESELLER_USER_ID', $reseller_info['user_id']);

    // The bootstrap process will now need to load the reseller-specific bootstrap
    // We will handle that logic in the main bootstrap file.

} else {
    // --- Unauthorized Domain ---
    // The domain points to our server but is not the main site or an approved reseller domain.
    // We must stop all execution and show a themed error page.

    http_response_code(400); // Bad Request

    // We can't use the standard header/footer includes here easily without causing conflicts.
    // So, we'll create a standalone, themed error page.
    $error_page_path = __DIR__ . '/../../public/errors/unauthorized_host.php';

    // To pass the base URL for styling, we'll define it here if not already defined.
    if (!defined('BASE_URL')) {
        // A reasonable guess for the base URL to load assets
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        define('BASE_URL', $protocol . $config_host . '/');
    }

    // We'll create this file in the next step. For now, let's just include it.
    if (file_exists($error_page_path)) {
        include $error_page_path;
    } else {
        // Fallback if the file doesn't exist for some reason
        echo "<h1>Error: Unauthorized Host</h1><p>This domain is not configured to use this service.</p>";
    }

    exit; // Halt execution
}

$stmt->close();
// We don't close the DB connection here, as it will be used by the rest of the application.

?>
