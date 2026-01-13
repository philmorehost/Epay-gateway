<?php
$page_title = 'Become a Reseller';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch current user's reseller status
$stmt = $db->prepare("SELECT reseller_status FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$reseller_status = $user['reseller_status'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    if (isset($_POST['apply_reseller']) && $reseller_status == 0) {
        // Update user status to 'Pending'
        $update_stmt = $db->prepare("UPDATE users SET reseller_status = 1 WHERE id = ?");
        $update_stmt->bind_param('i', $user_id);
        if ($update_stmt->execute()) {
            $message = "<div class='alert alert-success'>Your application has been submitted successfully! You will be notified once it has been reviewed by an administrator.</div>";
            $reseller_status = 1; // Update status for the current page view
        } else {
            $message = "<div class='alert alert-danger'>There was an error submitting your application. Please try again.</div>";
        }
        $update_stmt->close();
    }
}

?>

<h1 class="mb-4">Reseller Program</h1>

<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Join Our Reseller Network</h5>
        <p class="card-text">
            Our reseller program allows you to start your own hosting and domain business. We provide you with discounted wholesale pricing, and you set your own retail prices for your customers. Manage your clients, billing, and support all through our white-labeled reseller portal.
        </p>

        <hr>

        <h5 class="mt-4">Your Application Status</h5>

        <?php if ($reseller_status == 0): ?>
            <p>You are not currently a reseller. Click the button below to submit your application for review.</p>
            <form method="POST">
                 <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <button type="submit" name="apply_reseller" class="btn btn-primary">Apply Now</button>
            </form>
        <?php elseif ($reseller_status == 1): ?>
            <div class="alert alert-info">
                <strong>Your application is pending review.</strong> We will notify you by email once a decision has been made.
            </div>
        <?php elseif ($reseller_status == 2): ?>
             <div class="alert alert-success">
                <strong>Congratulations! Your reseller application has been approved.</strong> You can now access the <a href="reseller/">Reseller Portal</a>.
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
