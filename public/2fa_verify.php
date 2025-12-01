<?php
require_once '../app/core/bootstrap.php';
use PragmaRX\Google2FA\Google2FA;

$error = null;
$user_id_to_verify = $_SESSION['2fa_user_id'] ?? null;

if (!$user_id_to_verify) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['2fa_code'];

    $stmt = $db->prepare("SELECT 2fa_secret FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id_to_verify);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && !empty($user['2fa_secret'])) {
        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($user['2fa_secret'], $code);

        if ($isValid) {
            // 2FA successful, complete the login
            unset($_SESSION['2fa_user_id']);
            $_SESSION['user_id'] = $user_id_to_verify;

            // Redirect to the intended page or the dashboard
            $return_to = $_SESSION['return_to'] ?? 'client_dashboard.php'; // Default to dashboard
            unset($_SESSION['return_to']);
            header("Location: $return_to");
            exit;
        } else {
            $error = "Invalid or expired code.";
        }
    } else {
        // This case should ideally not happen if 2FA is enforced correctly
        $error = "An unexpected error occurred. 2FA secret not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Two-Factor Authentication</h1>
        <p>Enter the code from your authenticator app.</p>
    </div>

    <div class="card" style="max-width: 500px; margin: auto;">
        <div class="card-body">
            <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form action="2fa_verify.php" method="post">
                <div class="mb-3">
                    <label for="2fa_code" class="form-label">Verification Code</label>
                    <input type="text" class="form-control" id="2fa_code" name="2fa_code" required maxlength="6" pattern="\d{6}" title="Enter a 6-digit code">
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
