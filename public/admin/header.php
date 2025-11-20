<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hostbill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f0f2f5;
        }
        .sidebar {
            width: 280px;
            background-color: #2c3e50;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            padding-top: 20px;
            transition: all 0.3s;
        }
        .sidebar a {
            color: #bdc3c7;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #34495e;
            color: white;
            border-left-color: #3498db;
        }
        .sidebar h4 {
            padding: 0 20px;
            margin-bottom: 20px;
        }
        .main-content {
            margin-left: 280px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center">Hostbill Admin</h4>
    <nav class="nav flex-column">
        <a class="nav-link active" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link" href="products.php"><i class="bi bi-box-seam"></i> Products</a>
        <a class="nav-link" href="users.php"><i class="bi bi-people"></i> Clients</a>
        <a class="nav-link" href="resellers.php"><i class="bi bi-briefcase"></i> Resellers</a>
        <a class="nav-link" href="invoices.php"><i class="bi bi-receipt"></i> Invoices</a>
        <a class="nav-link" href="orders.php"><i class="bi bi-cart"></i> Orders</a>
        <a class="nav-link" href="servers.php"><i class="bi bi-server"></i> Servers</a>
        <a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Settings</a>
        <hr>
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="container-fluid">
        <!-- Content goes here -->
