<?php
// app/pages/wordpress_manager.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}

require_once __DIR__ . '/../modules/Cpanel.php';

// Fetch all of user's active hosting orders
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("
    SELECT o.domain, p.server_id, s.hostname, s.username as server_username, s.api_token, o.cpanel_username
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN servers s ON p.server_id = s.id
    WHERE o.user_id = ? AND o.status = 'Active' AND p.module = 'Cpanel' AND p.install_wordpress = 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$hosting_accounts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'WordPress Manager';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>WordPress Manager</h2>
    </div>
    <div class="card-body">
        <p>Manage your WordPress installations. Click a button to securely log in to your WordPress admin dashboard.</p>

        <?php if (empty($hosting_accounts)): ?>
            <div class="alert alert-info">
                No active WordPress hosting accounts found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Installation Path</th>
                            <th>Version</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hosting_accounts as $account): ?>
                            <?php
                            // For each account, get its WP installations
                            $module = new Cpanel($account['hostname'], $account['server_username'], $account['api_token']);
                            $cpanel_user = $account['cpanel_username']; // This needs to be stored on the order
                            $installations_result = $module->getWordPressInstallations($cpanel_user);

                            if ($installations_result['success'] && !empty($installations_result['data'])):
                                foreach ($installations_result['data'] as $install):
                            ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($account['domain']); ?></td>
                                        <td><?php echo htmlspecialchars($install['path_rel']); ?></td>
                                        <td><?php echo htmlspecialchars($install['version']); ?></td>
                                        <td>
                                            <?php
                                            $login_result = $module->getWordPressLoginUrl($cpanel_user, $install['id']);
                                            if ($login_result['success']):
                                            ?>
                                                <a href="<?php echo htmlspecialchars($login_result['data']['url']); ?>" target="_blank" class="btn btn-primary btn-sm">One-Click Login</a>
                                            <?php else: ?>
                                                <span class="text-danger">Error getting login URL</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php
                                endforeach;
                            else: ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($account['domain']); ?></td>
                                    <td colspan="3" class="text-muted">Could not retrieve WordPress installations.</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
