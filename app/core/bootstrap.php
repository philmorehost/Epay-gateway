<?php
// app/core/bootstrap.php

// 1. Load the configuration file first, as it's needed for the DB connection.
if (file_exists(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
} else {
    // If the config file doesn't exist, redirect to the installer.
    header('Location: ../installer/index.php');
    exit;
}

// 2. Establish the database connection.
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
} catch (Exception $e) {
    // If the database connection fails, it's likely the installer was not run.
    die("Database connection error. Please run the installer or check your configuration.");
}

// 3. CRITICAL: Check the incoming host. This script now has access to the $db object.
// It will exit if the host is not authorized.
require_once __DIR__ . '/host_check.php';

// 4. Start the session
session_start();

// If this is a reseller storefront, load the reseller-specific bootstrap
if (defined('IS_RESELLER_STOREFRONT') && IS_RESELLER_STOREFRONT === true) {
    require_once __DIR__ . '/reseller_bootstrap.php';
}

// Include the core functions file
require_once __DIR__ . '/functions.php';

// You can add more core functions here, such as:
// - User authentication functions
// - Template rendering functions
// - etc.
