<?php
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Destroy the session and redirect to the login page
session_destroy();
header('Location: login.php');
exit;
