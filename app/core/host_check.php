<?php
// Core Host & Reseller Check

$is_reseller_storefront = false; // Use a variable to track state.

// Get the HTTP_HOST, sanitizing it.
$http_host = filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

// Get the base system URL from the settings to compare against.
$system_url_setting = $db->query("SELECT value FROM settings WHERE setting = 'system_url'")->fetch_assoc()['value'] ?? '';
$system_host = parse_url($system_url_setting, PHP_URL_HOST);

// If the current host is different from the main system host, perform checks.
if (!empty($http_host) && strtolower($http_host) !== strtolower($system_host)) {

    // Check if it's an approved reseller domain
    $stmt = $db->prepare("SELECT * FROM reseller_settings WHERE custom_domain = ?");
    $stmt->bind_param('s', $http_host);
    $stmt->execute();
    $reseller_settings = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($reseller_settings) {
        // --- Valid Reseller Domain Found ---
        $is_reseller_storefront = true;

        // Fetch the reseller's user data
        $reseller_id = $reseller_settings['reseller_id'];
        $reseller_user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $reseller_user_stmt->bind_param('i', $reseller_id);
        $reseller_user_stmt->execute();
        $reseller_user = $reseller_user_stmt->get_result()->fetch_assoc();

        // Store all relevant reseller data in a global variable
        $GLOBALS['reseller_data'] = [
            'user_info' => $reseller_user,
            'settings' => $reseller_settings
        ];

    } else {
        // --- Unauthorized Host ---
        header("HTTP/1.0 404 Not Found");
        include_once __DIR__ . '/../../public/errors/unauthorized_host.php';
        exit;
    }
}

// --- Define the constant once, based on the final state ---
define('IS_RESELLER_STOREFRONT', $is_reseller_storefront);
