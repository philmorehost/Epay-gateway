<?php
$page_title = 'Reseller Portal';
require_once 'header.php'; // We will create this next
require_once '../../app/core/bootstrap.php'; // Auth is handled by header.php

$reseller_id = $_SESSION['user_id'];
$message = '';

// Fetch reseller settings
$stmt = $db->prepare("SELECT * FROM reseller_settings WHERE reseller_id = ?");
$stmt->bind_param('i', $reseller_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $markup = filter_input(INPUT_POST, 'markup', FILTER_VALIDATE_FLOAT);
    $domain = trim($_POST['domain'] ?? '');

    if ($markup === false || $markup < 0) {
        $message = "<div class='alert alert-danger'>Invalid markup percentage.</div>";
    } else {
        // Validate domain if provided
        if (!empty($domain) && !filter_var('http://' . $domain, FILTER_VALIDATE_URL)) {
            $message = "<div class='alert alert-danger'>The custom domain is not a valid domain name.</div>";
        } else {
            $update_stmt = $db->prepare("UPDATE reseller_settings SET retail_markup_percent = ?, custom_domain = ? WHERE reseller_id = ?");
            $update_stmt->bind_param('dsi', $markup, $domain, $reseller_id);
            if ($update_stmt->execute()) {
                $message = "<div class='alert alert-success'>Settings updated successfully.</div>";
                // Refresh settings
                $settings['retail_markup_percent'] = $markup;
                $settings['custom_domain'] = $domain;
            } else {
                 $message = "<div class='alert alert-danger'>Error updating settings. If using a custom domain, ensure it is not already in use.</div>";
            }
        }
    }
}

?>

<h1 class="mb-4">Reseller Dashboard</h1>
<?php echo $message; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Storefront Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label for="markup" class="form-label">Retail Price Markup (%)</label>
                        <p class="form-text">Set the percentage by which you want to increase the wholesale price to determine your final retail price.</p>
                        <div class="input-group">
                             <input type="number" step="0.01" min="0" class="form-control" id="markup" name="markup" value="<?php echo htmlspecialchars($settings['retail_markup_percent'] ?? '10.00'); ?>">
                             <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                         <label for="domain" class="form-label">Custom Storefront Domain</label>
                         <p class="form-text">
                             To use your own domain (e.g., billing.yourdomain.com), create a CNAME record in your DNS settings pointing to <strong><?php echo $_SERVER['HTTP_HOST']; ?></strong>. Then, enter your domain here.
                         </p>
                         <div class="input-group">
                              <span class="input-group-text">https://</span>
                             <input type="text" class="form-control" id="domain" name="domain" placeholder="billing.yourdomain.com" value="<?php echo htmlspecialchars($settings['custom_domain'] ?? ''); ?>">
                         </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
             <div class="card-header">
                <h5>Quick Stats</h5>
            </div>
            <div class="card-body">
                <p>Your storefront is currently accessed via:</p>
                <?php if (!empty($settings['custom_domain'])): ?>
                    <a href="https://<?php echo htmlspecialchars($settings['custom_domain']); ?>" target="_blank">https://<?php echo htmlspecialchars($settings['custom_domain']); ?></a>
                <?php else: ?>
                    <span class="text-muted">No custom domain set.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
$db->close();
require_once 'footer.php';
?>
