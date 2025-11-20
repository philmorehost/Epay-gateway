<?php
// Reseller Authentication Check

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User must be logged in to access this area.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php');
    exit;
}

// The user must also be an approved reseller.
// We'll check this by querying the database.
require_once '../../app/core/bootstrap.php';

$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT reseller_status FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['reseller_status'] != 2) {
    // Not an approved reseller, redirect to the client area application page.
    header('Location: ../become_reseller.php');
    exit;
}
