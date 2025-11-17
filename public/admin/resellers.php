<?php
// Admin - Reseller Management
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle approval/denial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action']; // 'approve' or 'deny'

    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE users SET is_reseller = 1, reseller_status = 2 WHERE id = ?");
    } else { // Deny
        $stmt = $db->prepare("UPDATE users SET is_reseller = 0, reseller_status = 0 WHERE id = ?");
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    header('Location: resellers.php');
    exit;
}

// Fetch all pending reseller requests
$result = $db->query("SELECT id, name, email, created_at FROM users WHERE reseller_status = 1 ORDER BY created_at ASC");
$requests = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Reseller Requests';
include __DIR__ . '/../../app/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Pending Reseller Requests</h2>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Request Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No pending reseller requests.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['name']); ?></td>
                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                            <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                            <td>
                                <form action="resellers.php" method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form action="resellers.php" method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" name="action" value="deny" class="btn btn-sm btn-danger">Deny</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/footer.php';
