<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_template'])) {
    verify_csrf_token();
    $template_id = (int)$_POST['template_id'];
    // Prevent deletion of system templates
    $stmt = $db->prepare("DELETE FROM email_templates WHERE id = ? AND is_system = 0");
    $stmt->bind_param('i', $template_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Template deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to delete template.</div>";
    }
}


$templates_result = $db->query("SELECT * FROM email_templates ORDER BY name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Email Templates</h1>
    <a href="edit_email_template.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Template</a>
</div>

<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($template = $templates_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($template['name']); ?></td>
                    <td><?php echo htmlspecialchars($template['subject']); ?></td>
                    <td>
                        <?php if($template['is_system']): ?>
                            <span class="badge bg-info">System</span>
                        <?php else: ?>
                             <span class="badge bg-secondary">Custom</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_email_template.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <?php if(!$template['is_system']): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                            <button type="submit" name="delete_template" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>


<?php
require_once 'footer.php';
?>
