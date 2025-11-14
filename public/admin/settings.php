<?php
require_once 'templates/header.php';

// Handle form submission
$error = $success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->begin_transaction();

        // Settings to update
        $settings_to_update = [
            'smtp_host' => $_POST['smtp_host'],
            'smtp_port' => $_POST['smtp_port'],
            'smtp_username' => $_POST['smtp_username'],
            'smtp_password' => $_POST['smtp_password'],
            'smtp_encryption' => $_POST['smtp_encryption'],
            'system_email' => $_POST['system_email'],
        ];

        $stmt = $db->prepare("UPDATE settings SET value = ? WHERE setting = ?");

        foreach ($settings_to_update as $key => $value) {
            $stmt->bind_param('ss', $value, $key);
            $stmt->execute();
        }

        $db->commit();
        $success = "Settings updated successfully.";
    } catch (Exception $e) {
        $db->rollback();
        $error = "Failed to update settings: " . $e->getMessage();
    }
}


// Fetch current settings
$settings_result = $db->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting']] = $row['value'];
}
?>

<h1>System Settings</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="card">
    <div class="card-header">SMTP Configuration</div>
    <div class="card-body">
        <form action="settings.php" method="post">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="smtp_host" class="form-label">SMTP Host</label>
                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="smtp_port" class="form-label">SMTP Port</label>
                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="smtp_username" class="form-label">SMTP Username</label>
                    <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="smtp_password" class="form-label">SMTP Password</label>
                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>">
                </div>
            </div>
             <div class="mb-3">
                <label for="smtp_encryption" class="form-label">Encryption</label>
                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                    <option value="tls" <?php echo ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                    <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="system_email" class="form-label">System Email Address</label>
                <input type="email" class="form-control" id="system_email" name="system_email" value="<?php echo htmlspecialchars($settings['system_email'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
