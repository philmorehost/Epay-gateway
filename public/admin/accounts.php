<?php
require_once 'templates/header.php';
require_once '../../app/modules/Cpanel.php';

$error = $success = null;
$cpanel = new Cpanel();

// Handle suspend/unsuspend/terminate actions
$action = $_GET['action'] ?? null;
$account_id = $_GET['id'] ?? null;
$username_to_action = $_GET['username'] ?? null;

if ($action && $account_id && $username_to_action) {
    try {
        $new_status = '';
        if ($action === 'suspend') {
            $cpanel->suspend_account($username_to_action, 'Billing Issue');
            $new_status = 'Suspended';
            $success = "Account suspended successfully.";
        } elseif ($action === 'unsuspend') {
            $cpanel->unsuspend_account($username_to_action);
            $new_status = 'Active';
            $success = "Account unsuspended successfully.";
        } elseif ($action === 'terminate') {
            $cpanel->terminate_account($username_to_action);
            // We'll just delete the record from our side
            $stmt = $db->prepare("DELETE FROM hosting_accounts WHERE id = ?");
            $stmt->bind_param('i', $account_id);
            $stmt->execute();
            $success = "Account terminated successfully.";
        }

        if ($new_status) {
            $stmt = $db->prepare("UPDATE hosting_accounts SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $new_status, $account_id);
            $stmt->execute();
        }
    } catch (Exception $e) {
        $error = "API Error: " . $e->getMessage();
    }
}

// Fetch all hosting accounts
$query = "SELECT ha.id, ha.domain, ha.username, ha.status, ha.created_at, u.name as client_name
          FROM hosting_accounts ha
          JOIN orders o ON ha.order_id = o.id
          JOIN users u ON o.user_id = u.id
          ORDER BY ha.created_at DESC";
$accounts = $db->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<h1>Hosting Accounts</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="card">
    <div class="card-header">Provisioned Accounts</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Client</th><th>Domain</th><th>Username</th><th>Created</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($account['domain']); ?></td>
                        <td><?php echo htmlspecialchars($account['username']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($account['created_at'])); ?></td>
                        <td>
                             <span class="badge bg-<?php echo $account['status'] === 'Active' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars($account['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if ($account['status'] === 'Active'): ?>
                                    <a href="?action=suspend&id=<?php echo $account['id']; ?>&username=<?php echo $account['username']; ?>" class="btn btn-sm btn-warning">Suspend</a>
                                <?php else: ?>
                                    <a href="?action=unsuspend&id=<?php echo $account['id']; ?>&username=<?php echo $account['username']; ?>" class="btn btn-sm btn-success">Unsuspend</a>
                                <?php endif; ?>
                                <a href="?action=terminate&id=<?php echo $account['id']; ?>&username=<?php echo $account['username']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to terminate this account? This cannot be undone.')">Terminate</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
