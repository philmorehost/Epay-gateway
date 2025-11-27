<?php
require_once '../app/core/bootstrap.php';
use PragmaRX\Google2FA\Google2FA;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$google2fa = new Google2FA();
$error = $success = null;
$companyName = 'HostBill-1'; // Or fetch from a setting

// Fetch user's current 2FA status
$stmt = $db->prepare("SELECT email, 2fa_secret, 2fa_enabled FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$qrCodeUrl = null;
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'enable_2fa_start' && !$user['2fa_enabled']) {
        // Generate a new secret and display the QR code
        $secret = $google2fa->generateSecretKey();
        $stmt = $db->prepare("UPDATE users SET 2fa_secret = ? WHERE id = ?");
        $stmt->bind_param('si', $secret, $user_id);
        $stmt->execute();
        $user['2fa_secret'] = $secret; // Update for the current request

        $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $user['email'], $secret);
    }
    elseif ($_POST['action'] === 'enable_2fa_finish' && !empty($user['2fa_secret'])) {
        // Verify the code and enable 2FA
        $code = $_POST['2fa_code'];
        $isValid = $google2fa->verifyKey($user['2fa_secret'], $code);

        if ($isValid) {
            $stmt = $db->prepare("UPDATE users SET 2fa_enabled = 1 WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $success = "2FA has been enabled successfully.";
            $user['2fa_enabled'] = true; // Update for the current request
        } else {
            $error = "Invalid or expired code. Please try again.";
            $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $user['email'], $user['2fa_secret']); // Reshow QR
        }
    }
    elseif ($_POST['action'] === 'disable_2fa') {
        // Disable 2FA
        $stmt = $db->prepare("UPDATE users SET 2fa_enabled = 0, 2fa_secret = NULL WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $success = "2FA has been disabled.";
        $user['2fa_enabled'] = false; // Update for the current request
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Two-Factor Authentication (2FA)</h1>
    </div>

    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="card" style="max-width: 600px; margin: auto;">
        <div class="card-body">
            <?php if ($user['2fa_enabled']): ?>
                <p>Two-factor authentication is currently <strong>enabled</strong> on your account.</p>
                <form action="security.php" method="post">
                    <input type="hidden" name="action" value="disable_2fa">
                    <button type="submit" class="btn btn-danger">Disable 2FA</button>
                </form>
            <?php elseif ($qrCodeUrl): ?>
                <h5>Step 2: Verify the Code</h5>
                <p>Scan the QR code below with your authenticator app, then enter the 6-digit code to complete the setup.</p>
                <div class="text-center my-3">
                    <!-- Note: The PragmaRX library generates a Google Charts URL, which is fine -->
                    <img src="<?php echo $qrCodeUrl; ?>">
                </div>
                <form action="security.php" method="post">
                    <input type="hidden" name="action" value="enable_2fa_finish">
                    <div class="mb-3">
                        <label for="2fa_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control" id="2fa_code" name="2fa_code" required maxlength="6" pattern="\d{6}" title="Enter a 6-digit code">
                    </div>
                    <button type="submit" class="btn btn-primary">Enable 2FA</button>
                </form>
            <?php else: ?>
                <h5>Step 1: Enable 2FA</h5>
                <p>Two-factor authentication adds an extra layer of security to your account. To enable it, click the button below.</p>
                <form action="security.php" method="post">
                    <input type="hidden" name="action" value="enable_2fa_start">
                    <button type="submit" class="btn btn-success">Enable 2FA</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
     <div class="text-center mt-4">
        <a href="client_dashboard.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
