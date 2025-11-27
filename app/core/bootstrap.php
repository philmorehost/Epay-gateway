<?php
// app/core/bootstrap.php

session_start();

// Autoload dependencies
require_once __DIR__ . '/../../vendor/autoload.php';

// Check if the application is installed
if (!file_exists(__DIR__ . '/../../config/config.php')) {
    header('Location: ../installer/index.php');
    exit;
}

// Include the database configuration
require_once __DIR__ . '/../../config/config.php';

// Create a database connection
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        // In a real application, you'd want to handle this more gracefully
        die("Database connection failed: " . $db->connect_error);
    }
} catch (Exception $e) {
    die("An error occurred while connecting to the database.");
}

// --- IP Blacklist/Whitelist ---
$settings_result = $db->query("SELECT `setting`, `value` FROM `settings` WHERE `setting` IN ('ip_blacklist', 'ip_whitelist')");
$security_settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $security_settings[$row['setting']] = $row['value'];
}

$user_ip = $_SERVER['REMOTE_ADDR'];

// Check Blacklist
$blacklist = !empty($security_settings['ip_blacklist']) ? array_map('trim', explode("\n", $security_settings['ip_blacklist'])) : [];
if (in_array($user_ip, $blacklist)) {
    http_response_code(403);
    die('Your IP address has been blocked.');
}

// Check Whitelist (if it's not empty)
$whitelist = !empty($security_settings['ip_whitelist']) ? array_map('trim', explode("\n", $security_settings['ip_whitelist'])) : [];
if (!empty($whitelist) && !in_array($user_ip, $whitelist)) {
    http_response_code(403);
    die('Access from your IP address is not allowed.');
}


// Include the core email sending system
require_once __DIR__ . '/email.php';

// Verify the host domain
require_once __DIR__ . '/host_check.php';
verify_host();

// Include the Access Control List system
require_once __DIR__ . '/acl.php';

// --- Security Settings ---
// define('MAX_LOGIN_ATTEMPTS', 5); // This will be fetched from the DB
define('LOGIN_BLOCK_TIME', 300); // Block duration in seconds (5 minutes)
