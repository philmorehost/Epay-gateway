<?php
require_once 'templates/header.php';
require_permission('manage_settings');

// Handle form submission
$error = $success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->begin_transaction();

        // Settings to update
        $settings_to_update = [];
        if (isset($_POST['smtp_host'])) { // Check if the SMTP form was submitted
            $settings_to_update = [
                'smtp_host' => $_POST['smtp_host'],
                'smtp_port' => $_POST['smtp_port'],
                'smtp_username' => $_POST['smtp_username'],
                'smtp_password' => $_POST['smtp_password'],
                'smtp_encryption' => $_POST['smtp_encryption'],
                'system_email' => $_POST['system_email'],
            ];
        } elseif (isset($_POST['whm_host'])) { // Check if the WHM form was submitted
            $settings_to_update = [
                'whm_host' => $_POST['whm_host'],
                'whm_user' => $_POST['whm_user'],
                'whm_api_token' => $_POST['whm_api_token'],
            ];
        } elseif (isset($_POST['connectreseller_api_key'])) {
            $settings_to_update = [
                'connectreseller_api_key' => $_POST['connectreseller_api_key'],
                'connectreseller_reseller_id' => $_POST['connectreseller_reseller_id'],
            ];
        } elseif (isset($_POST['tax_rate'])) { // Check if the Financial form was submitted
            $settings_to_update = [
                'tax_rate' => $_POST['tax_rate'],
                'base_currency' => $_POST['base_currency'],
                'secondary_currency' => $_POST['secondary_currency'],
                'usd_conversion_rate' => $_POST['usd_conversion_rate'],
            ];
        }

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

<div class="card mt-4">
    <div class="card-header">ConnectReseller API Settings</div>
    <div class="card-body">
        <form action="settings.php" method="post">
            <div class="mb-3">
                <label for="connectreseller_api_key" class="form-label">API Key</label>
                <input type="password" class="form-control" id="connectreseller_api_key" name="connectreseller_api_key" value="<?php echo htmlspecialchars($settings['connectreseller_api_key'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="connectreseller_reseller_id" class="form-label">Reseller ID</label>
                <input type="text" class="form-control" id="connectreseller_reseller_id" name="connectreseller_reseller_id" value="<?php echo htmlspecialchars($settings['connectreseller_reseller_id'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save ConnectReseller Settings</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">WHM/cPanel Server Configuration</div>
    <div class="card-body">
        <form action="settings.php" method="post">
             <div class="mb-3">
                <label for="whm_host" class="form-label">WHM Host</label>
                <input type="text" class="form-control" id="whm_host" name="whm_host" value="<?php echo htmlspecialchars($settings['whm_host'] ?? ''); ?>" placeholder="e.g., https://your-server.com:2087">
            </div>
            <div class="mb-3">
                <label for="whm_user" class="form-label">WHM Username</label>
                <input type="text" class="form-control" id="whm_user" name="whm_user" value="<?php echo htmlspecialchars($settings['whm_user'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="whm_api_token" class="form-label">WHM API Token</label>
                <input type="password" class="form-control" id="whm_api_token" name="whm_api_token" value="<?php echo htmlspecialchars($settings['whm_api_token'] ?? ''); ?>">
                <small class="form-text text-muted">Create a token in WHM > Development > Manage API Tokens.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save WHM Settings</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Financial Settings</div>
    <div class="card-body">
        <form action="settings.php" method="post">
            <div class="mb-3">
                <label for="tax_rate" class="form-label">Global Tax Rate (%)</label>
                <input type="number" step="0.01" class="form-control" id="tax_rate" name="tax_rate" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0.00'); ?>">
            </div>
            <hr>
            <h5>Multi-Currency Settings</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="base_currency" class="form-label">Base Currency</label>
                    <input type="text" class="form-control" id="base_currency" name="base_currency" value="<?php echo htmlspecialchars($settings['base_currency'] ?? 'NGN'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="secondary_currency" class="form-label">Secondary Currency</label>
                    <input type="text" class="form-control" id="secondary_currency" name="secondary_currency" value="<?php echo htmlspecialchars($settings['secondary_currency'] ?? 'USD'); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="usd_conversion_rate" class="form-label">Secondary Currency Conversion Rate (to 1 Base Currency)</label>
                <input type="number" step="0.01" class="form-control" id="usd_conversion_rate" name="usd_conversion_rate" value="<?php echo htmlspecialchars($settings['usd_conversion_rate'] ?? '1.00'); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save Financial Settings</button>
        </form>
    </div>
</div>


<?php require_once 'templates/footer.php'; ?>
