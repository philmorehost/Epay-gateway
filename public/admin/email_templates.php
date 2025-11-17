<?php
// Admin - Email Template Manager
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all email templates
$result = $db->query("SELECT * FROM email_templates ORDER BY name ASC");
$templates = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Email Templates';
include __DIR__ . '/../../app/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Email Template Manager</h2>
    </div>
    <div class="card-body">
        <a href="email_template_edit.php" class="btn btn-primary mb-3">Create New Template</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No email templates found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $template): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($template['name']); ?></td>
                            <td><?php echo htmlspecialchars($template['subject']); ?></td>
                            <td>
                                <a href="email_template_edit.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
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
