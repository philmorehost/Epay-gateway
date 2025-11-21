<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    // Loop through the POST data and update settings
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

// Fetch all settings from the database
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

            <h5 class="mb-3">General Settings</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($settings['company_name']); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="system_url" class="form-label">System URL</label>
                    <input type="text" class="form-control" id="system_url" name="system_url" value="<?php echo htmlspecialchars($settings['system_url']); ?>">
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Currency Settings</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="currency_code_primary" class="form-label">Primary Currency (NGN)</label>
                    <input type="text" class="form-control" id="currency_code_primary" name="currency_code_primary" value="<?php echo htmlspecialchars($settings['currency_code_primary']); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="currency_code_secondary" class="form-label">Secondary Currency (USD)</label>
                    <input type="text" class="form-control" id="currency_code_secondary" name="currency_code_secondary" value="<?php echo htmlspecialchars($settings['currency_code_secondary']); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="usd_conversion_rate" class="form-label">USD Conversion Rate</label>
                    <input type="number" step="0.01" class="form-control" id="usd_conversion_rate" name="usd_conversion_rate" value="<?php echo htmlspecialchars($settings['usd_conversion_rate']); ?>">
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">API Keys</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="paystack_public_key" class="form-label">Paystack Public Key</label>
                    <input type="text" class="form-control" id="paystack_public_key" name="paystack_public_key" value="<?php echo htmlspecialchars($settings['paystack_public_key']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="paystack_secret_key" class="form-label">Paystack Secret Key</label>
                    <input type="password" class="form-control" id="paystack_secret_key" name="paystack_secret_key" value="<?php echo htmlspecialchars($settings['paystack_secret_key']); ?>">
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="connectreseller_api_key" class="form-label">ConnectReseller API Key</label>
                    <input type="password" class="form-control" id="connectreseller_api_key" name="connectreseller_api_key" value="<?php echo htmlspecialchars($settings['connectreseller_api_key']); ?>">
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
