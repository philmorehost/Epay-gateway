<?php
require_once 'templates/header.php';

$error = $success = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $company_name = $_POST['company_name'];
        $logo_url = $_POST['logo_url'];
        $support_email = $_POST['support_email'];
        $retail_markup_percent = $_POST['retail_markup_percent'];
        $custom_domain = $_POST['custom_domain'];

        // Validate that the domain is not already in use by another reseller
        if (!empty($custom_domain)) {
            $stmt = $db->prepare("SELECT user_id FROM reseller_settings WHERE custom_domain = ? AND user_id != ?");
            $stmt->bind_param('si', $custom_domain, $user_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("This custom domain is already in use.");
            }
        }

        $stmt = $db->prepare("UPDATE reseller_settings SET company_name = ?, logo_url = ?, support_email = ?, retail_markup_percent = ?, custom_domain = ? WHERE user_id = ?");
        $stmt->bind_param('sssdsi', $company_name, $logo_url, $support_email, $retail_markup_percent, $custom_domain, $user_id);
        $stmt->execute();
        $success = "Settings updated successfully.";

    } catch (Exception $e) {
        $error = "Failed to update settings: " . $e->getMessage();
    }
}

// Fetch current reseller settings
$stmt = $db->prepare("SELECT * FROM reseller_settings WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$reseller_settings = $stmt->get_result()->fetch_assoc();

?>

<h1>Reseller Settings</h1>
<p>Configure your white-labeling and branding options here.</p>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="card">
    <div class="card-header">White-Labeling Settings</div>
    <div class="card-body">
        <form action="settings.php" method="post">
            <div class="mb-3">
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($reseller_settings['company_name'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="logo_url" class="form-label">Logo URL</label>
                <input type="text" class="form-control" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($reseller_settings['logo_url'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="support_email" class="form-label">Support Email</label>
                <input type="email" class="form-control" id="support_email" name="support_email" value="<?php echo htmlspecialchars($reseller_settings['support_email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="custom_domain" class="form-label">Custom Domain</label>
                <input type="text" class="form-control" id="custom_domain" name="custom_domain" value="<?php echo htmlspecialchars($reseller_settings['custom_domain'] ?? ''); ?>">
                <small class="form-text text-muted">e.g., billing.yourdomain.com. You must point a CNAME record to this server.</small>
            </div>
            <hr>
            <div class="mb-3">
                <label for="retail_markup_percent" class="form-label">Global Retail Markup (%)</label>
                <input type="number" step="0.01" class="form-control" id="retail_markup_percent" name="retail_markup_percent" value="<?php echo htmlspecialchars($reseller_settings['retail_markup_percent'] ?? '0.00'); ?>">
                <small class="form-text text-muted">Set the percentage to increase prices for your customers.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
