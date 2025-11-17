<?php
// Admin Dashboard
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Dashboard';
include __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Welcome, Admin!</h5>
        <p class="card-text">Use the sidebar to manage your application.</p>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/admin_footer.php';
