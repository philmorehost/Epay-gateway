<?php
require_once 'templates/header.php';
require_permission('manage_settings');

$error = $success = null;

// Handle form submission for editing a template
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['template_id'])) {
    try {
        $template_id = $_POST['template_id'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        $stmt = $db->prepare("UPDATE email_templates SET subject = ?, body = ? WHERE id = ?");
        $stmt->bind_param('ssi', $subject, $body, $template_id);
        $stmt->execute();
        $success = "Email template updated successfully.";
    } catch (Exception $e) {
        $error = "Failed to update template: " . $e->getMessage();
    }
}

// Fetch all email templates
$templates = $db->query("SELECT * FROM email_templates ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Get the template to edit (if any)
$edit_template_id = $_GET['edit_id'] ?? ($templates[0]['id'] ?? null);
$template_to_edit = null;
if ($edit_template_id) {
    foreach ($templates as $template) {
        if ($template['id'] == $edit_template_id) {
            $template_to_edit = $template;
            break;
        }
    }
}
?>

<h1>Email Template Manager</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <?php foreach ($templates as $template): ?>
                <a href="?edit_id=<?php echo $template['id']; ?>" class="list-group-item list-group-item-action <?php echo ($template['id'] == $edit_template_id) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($template['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-9">
        <?php if ($template_to_edit): ?>
            <div class="card">
                <div class="card-header">
                    Edit Template: <strong><?php echo htmlspecialchars($template_to_edit['name']); ?></strong>
                </div>
                <div class="card-body">
                    <form action="email_templates.php?edit_id=<?php echo $template_to_edit['id']; ?>" method="post">
                        <input type="hidden" name="template_id" value="<?php echo $template_to_edit['id']; ?>">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($template_to_edit['subject']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">Body (HTML)</label>
                            <textarea class="form-control" id="body" name="body" rows="15" required><?php echo htmlspecialchars($template_to_edit['body']); ?></textarea>
                            <small class="form-text text-muted">Use placeholders like {client_name}, {invoice_id}, etc.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Template</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Select a template to edit.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
