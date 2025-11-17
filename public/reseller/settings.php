<?php
// Reseller Portal - Settings
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if user is logged in and is an approved reseller
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}
$stmt = $db->prepare("SELECT is_reseller FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
if (!$user || $user['is_reseller'] != 1) {
    header('Location: /index.php?page=dashboard');
    exit;
}

// Fetch existing settings
$settings_stmt = $db->prepare("SELECT * FROM reseller_settings WHERE reseller_id = ?");
$settings_stmt->bind_param("i", $_SESSION['user_id']);
$settings_stmt->execute();
$settings_result = $settings_stmt->get_result();
$settings = $settings_result->fetch_assoc();
$settings_stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = $_POST['company_name'] ?? '';
    $logo_url = $_POST['logo_url'] ?? '';
    $retail_markup_percent = $_POST['retail_markup_percent'] ?? 0.00;

    if ($settings) { // Update
        $stmt = $db->prepare("UPDATE reseller_settings SET company_name = ?, logo_url = ?, retail_markup_percent = ? WHERE reseller_id = ?");
        $stmt->bind_param("ssdi", $company_name, $logo_url, $retail_markup_percent, $_SESSION['user_id']);
    } else { // Insert
        $stmt = $db->prepare("INSERT INTO reseller_settings (reseller_id, company_name, logo_url, retail_markup_percent) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $_SESSION['user_id'], $company_name, $logo_url, $retail_markup_percent);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: settings.php?success=true');
    exit;
}

$page_title = 'Settings';
include __DIR__ . '/../../app/includes/reseller_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reseller Settings</h1>
</div>

<div class="card">
    <div class="card-body">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Settings saved successfully.</div>
        <?php endif; ?>
        <form action="settings.php" method="post">
            <h4>White-Label Settings</h4>
            <div class="mb-3">
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="logo_url" class="form-label">Logo URL</label>
                <input type="text" class="form-control" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>">
            </div>
            <hr>
            <h4>Pricing Settings</h4>
            <div class="mb-3">
                <label for="retail_markup_percent" class="form-label">Retail Price Markup (%)</label>
                <input type="number" step="0.01" class="form-control" id="retail_markup_percent" name="retail_markup_percent" value="<?php echo htmlspecialchars($settings['retail_markup_percent'] ?? '0.00'); ?>">
                <small class="form-text text-muted">Set the percentage to increase the wholesale price by for your customers.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/reseller_footer.php';
