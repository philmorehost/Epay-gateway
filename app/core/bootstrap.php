<?php
// app/core/bootstrap.php

session_start();

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
