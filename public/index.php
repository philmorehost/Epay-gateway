<?php
$page_title = 'Client Dashboard';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $db->prepare("SELECT first_name, last_name, credit_balance FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch active hosting services for this user
$services_stmt = $db->prepare("SELECT o.id, o.domain, p.name as product_name
                               FROM orders o
                               JOIN products p ON o.product_id = p.id
                               WHERE o.user_id = ? AND o.status = 'Active' AND p.module = 'Cpanel'");
$services_stmt->bind_param('i', $user_id);
$services_stmt->execute();
$services = $services_stmt->get_result();

?>

<h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-hdd-stack"></i> My Services</h5>
            </div>
            <div class="card-body">
                <?php if ($services->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product/Service</th>
                                <th>Domain</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($service = $services->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($service['domain']); ?></td>
                                <td class="text-end">
                                    <a href="manage_wordpress.php?order_id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm">Manage WordPress</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center text-muted">You have no active hosting services with WordPress management.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
$db->close();
require_once '../app/includes/footer.php';
?>
