<?php
// Admin - Edit Email Template
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$template_id = $_GET['id'] ?? null;
$template = [
    'id' => '',
    'name' => '',
    'subject' => '',
    'body' => '',
];

if ($template_id) {
    // Fetch existing template
    $stmt = $db->prepare("SELECT * FROM email_templates WHERE id = ?");
    $stmt->bind_param("i", $template_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $template = $result->fetch_assoc();
    $stmt->close();

    if (!$template) {
        die("Template not found.");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $body = $_POST['body'] ?? '';

    if (empty($name) || empty($subject) || empty($body)) {
        $error = "All fields are required.";
    } else {
        if ($template_id) {
            // Update existing template
            $stmt = $db->prepare("UPDATE email_templates SET name = ?, subject = ?, body = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $subject, $body, $template_id);
        } else {
            // Create new template
            $stmt = $db->prepare("INSERT INTO email_templates (name, subject, body) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $subject, $body);
        }

        if ($stmt->execute()) {
            header('Location: email_templates.php');
            exit;
        } else {
            $error = "Error saving template.";
        }
        $stmt->close();
    }
}


$page_title = $template_id ? 'Edit Template' : 'Create Template';
include __DIR__ . '/../../app/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $page_title; ?></h2>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="email_template_edit.php<?php echo $template_id ? '?id=' . $template_id : ''; ?>" method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Template Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($template['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Email Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($template['subject']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="body" class="form-label">Email Body (HTML)</label>
                <textarea class="form-control" id="body" name="body" rows="15" required><?php echo htmlspecialchars($template['body']); ?></textarea>
                <small class="form-text text-muted">You can use placeholders like {name} and {email}.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Template</button>
            <a href="email_templates.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/footer.php';
