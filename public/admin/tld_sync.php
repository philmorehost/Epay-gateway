<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';

// Fetch the ConnectReseller API key from settings
$api_key = $db->query("SELECT value FROM settings WHERE setting = 'connectreseller_api_key'")->fetch_assoc()['value'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    // Handle TLD Sync
    if (isset($_POST['sync_tlds'])) {
        if (empty($api_key)) {
            $message = "<div class='alert alert-danger'>ConnectReseller API key is not configured in settings.</div>";
        } else {
            $url = "https://api.connectreseller.com/ConnectReseller/ESHOP/tldsync/?APIKey=" . urlencode($api_key);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $tlds = json_decode($response, true);

            if (isset($tlds['responseMsg']) && is_array($tlds['responseMsg'])) {
                $sync_count = 0;
                $db->begin_transaction();
                try {
                    foreach ($tlds['responseMsg'] as $tld_data) {
                        $stmt = $db->prepare("INSERT INTO tld_pricing (tld, registration_price, renewal_price, transfer_price) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE registration_price = VALUES(registration_price), renewal_price = VALUES(renewal_price), transfer_price = VALUES(transfer_price)");
                        $stmt->bind_param('sddd', $tld_data['tld'], $tld_data['registrationPrice'], $tld_data['renewalPrice'], $tld_data['transferPrice']);
                        $stmt->execute();
                        $sync_count++;
                    }
                    $db->commit();
                    $message = "<div class='alert alert-success'>Successfully synced {$sync_count} TLDs.</div>";
                } catch (Exception $e) {
                    $db->rollback();
                    $message = "<div class='alert alert-danger'>An error occurred during sync: " . $e->getMessage() . "</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Failed to fetch TLDs from API. Please check your API key.</div>";
            }
        }
    }

    // Handle status update
    if (isset($_POST['update_status'])) {
        $tld_id = (int)$_POST['tld_id'];
        $is_active = (int)$_POST['is_active'];
        $stmt = $db->prepare("UPDATE tld_pricing SET is_active = ? WHERE id = ?");
        $stmt->bind_param('ii', $is_active, $tld_id);
        $stmt->execute();
    }
}

// Fetch all TLDs from our local database
$local_tlds = $db->query("SELECT * FROM tld_pricing ORDER BY tld ASC");

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Domain Pricing (TLDs)</h1>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <button type="submit" name="sync_tlds" class="btn btn-primary" <?php if(empty($api_key)) echo 'disabled'; ?>>
            <i class="bi bi-arrow-repeat"></i> Sync with Registrar
        </button>
    </form>
</div>

<?php echo $message; ?>
<?php if(empty($api_key)) echo "<div class='alert alert-warning'>API key is missing. Please configure it in the settings to enable sync.</div>"; ?>

<div class="card">
    <div class="card-body">
        <p>This table lists all available TLDs and their pricing. Use the sync button to fetch the latest prices from the registrar. You can then activate or deactivate TLDs to control which ones are available for sale to clients.</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>TLD</th>
                    <th>Registration</th>
                    <th>Renewal</th>
                    <th>Transfer</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($tld = $local_tlds->fetch_assoc()): ?>
                <tr>
                    <td><strong>.<?php echo htmlspecialchars($tld['tld']); ?></strong></td>
                    <td>NGN <?php echo number_format($tld['registration_price'], 2); ?></td>
                    <td>NGN <?php echo number_format($tld['renewal_price'], 2); ?></td>
                    <td>NGN <?php echo number_format($tld['transfer_price'], 2); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="tld_id" value="<?php echo $tld['id']; ?>">
                            <input type="hidden" name="is_active" value="<?php echo $tld['is_active'] ? '0' : '1'; ?>">
                            <button type="submit" name="update_status" class="btn btn-sm <?php echo $tld['is_active'] ? 'btn-success' : 'btn-secondary'; ?>">
                                <?php echo $tld['is_active'] ? 'Active' : 'Inactive'; ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'footer.php';
?>
