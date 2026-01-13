<?php
// Admin Authentication Check

// Start the session if it's not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the admin is logged in.
// If not, redirect them to the login page.
if (!isset($_SESSION['admin_id'])) {
    // Store the requested URL in the session so we can redirect after login.
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Optional: You could add a check here to re-validate the session against the database
// for higher security, but for now, this is sufficient.
