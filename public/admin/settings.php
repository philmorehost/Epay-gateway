<?php
// Admin - System Settings
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$success = null;
$error = null;

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    $settings_to_update = $_POST['settings'];

    $db->begin_transaction();
    try {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($settings_to_update as $key => $value) {
            // Don't save a new password if the field was left blank
            if ($key === 'smtp_pass' && empty($value)) {
                continue;
            }
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        $stmt->close();
        $db->commit();
        $success = "Settings saved successfully.";

    } catch (Exception $e) {
        $db->rollback();
        $error = "Error saving settings: " . $e->getMessage();
    }
}


// Fetch existing settings to populate the form
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$page_title = 'SMTP Settings';
include __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">SMTP Settings</h1>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="settings.php" method="post">
            <h4>SMTP Configuration</h4>
            <div class="mb-3">
                <label for="smtp_host" class="form-label">SMTP Host</label>
                <input type="text" class="form-control" id="smtp_host" name="settings[smtp_host]" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="smtp_port" class="form-label">SMTP Port</label>
                <input type="number" class="form-control" id="smtp_port" name="settings[smtp_port]" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="smtp_user" class="form-label">SMTP Username</label>
                <input type="text" class="form-control" id="smtp_user" name="settings[smtp_user]" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="smtp_pass" class="form-label">SMTP Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="smtp_pass" name="settings[smtp_pass]" value="">
            </div>
            <div class="mb-3">
                <label for="smtp_encryption" class="form-label">Encryption</label>
                <select class="form-select" id="smtp_encryption" name="settings[smtp_encryption]">
                    <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                    <option value="tls" <?php echo ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                </select>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/footer.php';
