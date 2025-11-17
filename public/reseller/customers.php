<?php
// public/reseller/customers.php
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}
$stmt = $db->prepare("SELECT is_reseller FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
if (!$user || $user['is_reseller'] != 1) {
    header('Location: /index.php?page=dashboard');
    exit;
}

// Get the logged-in reseller's ID
$reseller_id = $_SESSION['user_id'];

// Fetch all customers belonging to this reseller
$stmt = $db->prepare("SELECT id, name, email, created_at FROM customers WHERE reseller_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $reseller_id);
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'My Customers';
include __DIR__ . '/../../app/includes/reseller_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Customers</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="#" class="btn btn-sm btn-outline-primary disabled">
            Add New Customer (Coming Soon)
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">All Customers</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Registration Date</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="5" class="text-center">You haven't added any customers yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['id']); ?></td>
                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo date('M j, Y, g:i a', strtotime($customer['created_at'])); ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info disabled">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/reseller_footer.php';
