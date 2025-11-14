<?php
require_once '../app/core/bootstrap.php';
require_once '../app/core/reseller_bootstrap.php';

$current_host = $_SERVER['HTTP_HOST'];
$base_url_setting = $db->query("SELECT value FROM settings WHERE setting = 'base_url'")->fetch_assoc();
$base_host = parse_url($base_url_setting['value'], PHP_URL_HOST);

// Check if we are on a reseller domain
if ($current_host !== $base_host) {
    // --- RESELLER'S TIER 2 CUSTOMER VIEW ---
    $reseller_env = initialize_reseller_environment();
    $reseller_settings = $reseller_env['reseller_settings'];
    $products = $reseller_env['products'];

    // Display the white-labeled product page
    require '../app/views/reseller_product_page.php';

} else {
    // --- MAIN CLIENT AREA DASHBOARD ---
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Fetch user details
    $stmt = $db->prepare("SELECT name, credit_balance, is_reseller FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Display the main dashboard
    require '../app/views/client_dashboard.php';
}
