<?php
// public/ref.php

if (isset($_GET['ref'])) {
    $referral_code = $_GET['ref'];

    // Set a cookie that lasts for 30 days
    setcookie('affiliate_ref', $referral_code, time() + (86400 * 30), "/");

    // Log the click
    require_once '../app/core/bootstrap.php';
    $stmt = $db->prepare("SELECT user_id FROM affiliates WHERE referral_code = ?");
    $stmt->bind_param('s', $referral_code);
    $stmt->execute();
    $affiliate = $stmt->get_result()->fetch_assoc();

    if ($affiliate) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $stmt = $db->prepare("INSERT INTO affiliate_clicks (affiliate_user_id, ip_address) VALUES (?, ?)");
        $stmt->bind_param('is', $affiliate['user_id'], $ip_address);
        $stmt->execute();
    }
}

// Redirect to the homepage
header('Location: /');
exit;
