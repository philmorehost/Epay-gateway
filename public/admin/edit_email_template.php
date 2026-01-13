<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';
$template_id = $_GET['id'] ?? null;
$template = [
    'id' => null, 'name' => '', 'subject' => '', 'body' => '', 'is_system' => 0
];

if ($template_id) {
    $stmt = $db->prepare("SELECT * FROM email_templates WHERE id = ?");
    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $template = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name = $_POST['name'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $is_system = $template['is_system']; // Preserve system status

    if (empty($name) || empty($subject) || empty($body)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } else {
        if ($template_id) {
            // Update existing template
            $stmt = $db->prepare("UPDATE email_templates SET name = ?, subject = ?, body = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $subject, $body, $template_id);
        } else {
            // Insert new template
            $stmt = $db->prepare("INSERT INTO email_templates (name, subject, body, is_system) VALUES (?, ?, ?, 0)");
            $stmt->bind_param('sss', $name, $subject, $body);
        }

        if ($stmt->execute()) {
            if (!$template_id) $template_id = $stmt->insert_id;
            header('Location: email_templates.php?success=1');
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Failed to save template.</div>";
        }
    }
}

$page_title = $template_id ? "Edit Template: " . htmlspecialchars($template['name']) : "Add New Email Template";
?>

<h1><?php echo $page_title; ?></h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Template Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($template['name']); ?>" <?php if ($template['is_system']) echo 'readonly'; ?>>
                <?php if ($template['is_system']): ?><small class="form-text text-muted">System template names cannot be changed.</small><?php endif; ?>
            </div>
             <div class="mb-3">
                <label for="subject" class="form-label">Email Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($template['subject']); ?>">
            </div>
             <div class="mb-3">
                <label for="body" class="form-label">Email Body (HTML)</label>
                <textarea class="form-control" id="body" name="body" rows="15"><?php echo htmlspecialchars($template['body']); ?></textarea>
                <small class="form-text text-muted">You can use placeholders like {client_name}, {invoice_id}, etc. These will be replaced dynamically.</small>
            </div>

            <div class="d-flex justify-content-end">
                <a href="email_templates.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Template</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'footer.php';
?>
