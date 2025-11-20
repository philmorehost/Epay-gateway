<?php
// Core Host & Reseller Check

// This script runs on every request to determine if the site is being accessed
// via a custom reseller domain. If it is, it loads the reseller's settings
// and adjusts pricing and branding.

// --- Define Constants ---
// IS_RESELLER_STOREFRONT will be true if a valid reseller domain is detected.
define('IS_RESELLER_STOREFRONT', false);

// Get the HTTP_HOST, sanitizing it to prevent injection attacks.
$http_host = filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

// Get the base system URL from the settings to compare against.
$system_url_setting = $db->query("SELECT value FROM settings WHERE setting = 'system_url'")->fetch_assoc()['value'] ?? '';
$system_host = parse_url($system_url_setting, PHP_URL_HOST);

// If the current host is the same as the main system host, do nothing.
if (empty($http_host) || strtolower($http_host) === strtolower($system_host)) {
    return; // Not a reseller domain, exit the script.
}


// --- It's not the main domain, so check if it's an approved reseller domain ---
$stmt = $db->prepare("SELECT * FROM reseller_settings WHERE custom_domain = ?");
$stmt->bind_param('s', $http_host);
$stmt->execute();
$reseller_settings = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($reseller_settings) {
    // --- Valid Reseller Domain Found ---

    // Redefine the constant to indicate a reseller context.
    define('IS_RESELLER_STOREFRONT', true);

    // Fetch the reseller's user data
    $reseller_id = $reseller_settings['reseller_id'];
    $reseller_user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $reseller_user_stmt->bind_param('i', $reseller_id);
    $reseller_user_stmt->execute();
    $reseller_user = $reseller_user_stmt->get_result()->fetch_assoc();

    // Store all relevant reseller data in a global variable for easy access
    // throughout the application.
    $GLOBALS['reseller_data'] = [
        'user_info' => $reseller_user,
        'settings' => $reseller_settings
    ];

    // --- Price Adjustment Logic ---
    // Here, we would modify the product prices based on the reseller's markup.
    // This is a complex step that will be fleshed out later. For now, we have the data.
    // e.g., We could override the $GLOBALS['products'] array after it's fetched.

} else {
    // --- Unauthorized Host ---
    // The domain points to our server but is not in the reseller_settings table.
    // We must serve a themed error page and exit to prevent access.

    // Set a 404 Not Found header for SEO and security.
    header("HTTP/1.0 404 Not Found");

    // Include the themed error page.
    include_once __DIR__ . '/../../public/errors/unauthorized_host.php';

    // Stop all further script execution.
    exit;
}
