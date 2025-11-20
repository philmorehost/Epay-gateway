<?php
// Hostbill - A Multi-Tier Billing & Automation Portal

// The single entry point for all web requests.

// Check if the installer exists and redirect if it does
if (file_exists('../installer/index.php')) {
    header('Location: ../installer');
    exit;
}

// Load the core bootstrap file
require_once '../app/core/bootstrap.php';

// Route the request
// (Routing logic will be added here later)
echo "Welcome to Hostbill!";
