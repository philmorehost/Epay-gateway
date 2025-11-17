<?php
// Client Logout
// Note: bootstrap.php is included by the router (public/index.php)

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to home page
header('Location: /index.php?page=home');
exit;
