<?php
// Reseller Application Page
// Note: bootstrap.php is included by the router (public/index.php)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

// Handle application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE users SET reseller_status = 1 WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header('Location: /index.php?page=dashboard');
    exit;
}

$page_title = 'Become a Reseller';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Reseller Program</h2>
    </div>
    <div class="card-body">
        <p>Join our reseller program to start selling our products under your own brand.</p>
        <p>Click the button below to submit your application. Our team will review it and get back to you shortly.</p>

        <form action="/index.php?page=reseller_apply" method="post">
            <button type="submit" class="btn btn-primary">Apply Now</button>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
