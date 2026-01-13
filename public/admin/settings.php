<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $db->begin_transaction();
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token') {
                $stmt = $db->prepare("UPDATE settings SET value = ? WHERE setting = ?");
                $stmt->bind_param('ss', $value, $key);
                $stmt->execute();
            }
        }
        $db->commit();
        $message = "<div class='alert alert-success'>Settings updated successfully.</div>";
    } catch (Exception $e) {
        $db->rollback();
        $message = "<div class='alert alert-danger'>Failed to update settings.</div>";
    }
}

$settings_result = $db->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting']] = $row['value'];
}
?>

<h1 class="mb-4">System Settings</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-general-tab" data-bs-toggle="tab" data-bs-target="#nav-general" type="button">General</button>
                    <button class="nav-link" id="nav-api-tab" data-bs-toggle="tab" data-bs-target="#nav-api" type="button">API Keys</button>
                    <button class="nav-link" id="nav-smtp-tab" data-bs-toggle="tab" data-bs-target="#nav-smtp" type="button">SMTP</button>
                </div>
            </nav>

            <div class="tab-content pt-4">
                <div class="tab-pane fade show active" id="nav-general">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="company_name" class="form-label">Company Name</label><input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($settings['company_name']); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="system_url" class="form-label">System URL</label><input type="text" class="form-control" id="system_url" name="system_url" value="<?php echo htmlspecialchars($settings['system_url']); ?>"></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-api">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="paystack_public_key" class="form-label">Paystack Public Key</label><input type="text" class="form-control" id="paystack_public_key" name="paystack_public_key" value="<?php echo htmlspecialchars($settings['paystack_public_key']); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="paystack_secret_key" class="form-label">Paystack Secret Key</label><input type="password" class="form-control" id="paystack_secret_key" name="paystack_secret_key" value="<?php echo htmlspecialchars($settings['paystack_secret_key']); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="connectreseller_api_key" class="form-label">ConnectReseller API Key</label><input type="password" class="form-control" id="connectreseller_api_key" name="connectreseller_api_key" value="<?php echo htmlspecialchars($settings['connectreseller_api_key']); ?>"></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-smtp">
                     <div class="row">
                        <div class="col-md-6 mb-3"><label for="smtp_host" class="form-label">SMTP Host</label><input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="smtp_port" class="form-label">SMTP Port</label><input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port']); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="smtp_username" class="form-label">SMTP Username</label><input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username']); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="smtp_password" class="form-label">SMTP Password</label><input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password']); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="smtp_encryption" class="form-label">SMTP Encryption</label>
                            <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                <option value="tls" <?php if($settings['smtp_encryption'] == 'tls') echo 'selected'; ?>>TLS</option>
                                <option value="ssl" <?php if($settings['smtp_encryption'] == 'ssl') echo 'selected'; ?>>SSL</option>
                                <option value="" <?php if($settings['smtp_encryption'] == '') echo 'selected'; ?>>None</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'footer.php';
?>
