<?php
// Start the session
session_start();

// Load the configuration file
if (file_exists(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
} else {
    // If the config file doesn't exist, redirect to the installer
    // This is a failsafe in case the installer is not deleted
    header('Location: ../installer/index.php');
    exit;
}

// Database connection
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
} catch (Exception $e) {
    // If the database connection fails, it's likely that the installer was not run
    // or the config file is incorrect.
    die("Database connection error. Please run the installer or check your configuration.");
}

// Include the core functions file
require_once __DIR__ . '/functions.php';

// You can add more core functions here, such as:
// - User authentication functions
// - Template rendering functions
// - etc.
