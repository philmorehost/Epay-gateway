<?php
// Core Bootstrap File

// This file is responsible for loading the application's core components.

// --- 1. Load Configuration ---
// Check if the config file exists. If not, the application has not been installed.
if (file_exists(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
} else {
    // If the installer is also gone, this is a critical error.
    if (!file_exists(__DIR__ . '/../../installer/index.php')) {
        die("FATAL ERROR: Configuration file not found and installer is missing. Please reinstall the application.");
    }
    // If the installer exists, the index.php should handle the redirect.
    // This is a fallback for direct script access.
    die("Configuration not found. Please complete the installation.");
}

// --- 2. Establish Database Connection ---
// The config file should define DB_HOST, DB_USER, DB_PASS, and DB_NAME.
// We will use mysqli for database operations.
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors.
if ($db->connect_error) {
    // In a real application, you'd want to handle this more gracefully.
    die("Database connection failed: " . $db->connect_error);
}

// --- 3. Start Session ---
// Sessions are used for user authentication and flash messages.
session_start();

// --- 4. Load Core Functions ---
// (We will create this file later)
// require_once 'functions.php';

// --- 5. Host Check (for Reseller System) ---
// This is a critical security and white-labeling check.
// (We will create this file later)
// require_once 'host_check.php';


// --- Autoloading for modules can be added here later ---

// Bootstrap complete. The application can now proceed with routing.
