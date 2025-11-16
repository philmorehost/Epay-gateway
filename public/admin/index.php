<?php
// Admin Dashboard
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Admin Dashboard';
include __DIR__ . '/../../app/includes/header.php';

?>

<div class="card">
    <div class="card-header">
        Admin Dashboard
    </div>
    <div class="card-body">
        <p>Welcome to the admin dashboard!</p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>


<?php
include __DIR__ . '/../../app/includes/footer.php';
