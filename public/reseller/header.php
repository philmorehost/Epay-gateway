<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Reseller Portal'; ?> - Hostbill</title>
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
            background-color: #16a085; /* A different color for reseller area */
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            padding-top: 20px;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #1abc9c;
            color: white;
            border-left-color: #f1c40f;
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
    <h4 class="text-center">Reseller Portal</h4>
    <nav class="nav flex-column">
        <a class="nav-link active" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link" href="customers.php"><i class="bi bi-people"></i> My Customers</a>
        <a class="nav-link" href="products.php"><i class="bi bi-box-seam"></i> My Products</a>
        <hr>
        <a class="nav-link" href="../logout_client.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="container-fluid">
        <!-- Content goes here -->
