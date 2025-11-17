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
include __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Email Templates</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="email_template_edit.php" class="btn btn-sm btn-outline-primary">
            Create New Template
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
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
