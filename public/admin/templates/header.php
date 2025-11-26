<?php
require_once '../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-wrapper">
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3 class="text-center">Admin Panel</h3>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="index.php">Dashboard</a>
            </li>
            <?php if (has_permission('manage_products')): ?>
            <li class="nav-item">
                <a class="nav-link" href="products.php">Products</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="accounts.php">Accounts</a>
            </li>
            <?php if (has_permission('view_reports')): ?>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">Reports</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="invoices.php">Invoices</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="domains.php">Domains</a>
            </li>
            <?php if (has_permission('manage_servers')): ?>
            <li class="nav-item">
                <a class="nav-link" href="manage_servers.php">Manage Servers</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="payouts.php">Payouts</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manual_payments.php">Manual Payments</a>
            </li>
             <?php if (has_permission('manage_coupons')): ?>
            <li class="nav-item">
                <a class="nav-link" href="coupons.php">Coupons</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="#">Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Resellers</a>
            </li>
            <?php if (has_permission('manage_settings')): ?>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="email_templates.php">Email Templates</a>
            </li>
            <?php endif; ?>
            <?php if (has_permission('manage_staff')): ?>
            <li class="nav-item">
                <a class="nav-link" href="roles.php">Staff Roles</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="staff.php">Staff Manager</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>
    <main class="main-content">
