<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = $success = null;

// Handle affiliate activation & payout requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'activate_affiliate') {
        try {
            $referral_code = 'REF-' . $user_id . substr(md5(time()), 0, 4);
            $stmt = $db->prepare("INSERT INTO affiliates (user_id, referral_code) VALUES (?, ?)");
            $stmt->bind_param('is', $user_id, $referral_code);
            $stmt->execute();
            $success = "Your affiliate account has been activated!";
        } catch (Exception $e) {
            $error = "An error occurred. Please try again.";
        }
    } elseif ($_POST['action'] === 'request_payout') {
        $stmt = $db->prepare("SELECT commission_balance FROM affiliates WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $balance = $stmt->get_result()->fetch_assoc()['commission_balance'] ?? 0;

        $min_payout = (float)($settings['affiliate_min_payout'] ?? 50);

        if ($balance >= $min_payout) {
            try {
                $db->begin_transaction();
                $stmt = $db->prepare("INSERT INTO affiliate_payouts (affiliate_user_id, amount) VALUES (?, ?)");
                $stmt->bind_param('id', $user_id, $balance);
                $stmt->execute();

                $stmt = $db->prepare("UPDATE affiliates SET commission_balance = 0 WHERE user_id = ?");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();

                $db->commit();
                $success = "Your payout request has been submitted.";
            } catch (Exception $e) {
                $db->rollback();
                $error = "An error occurred. Please try again.";
            }
        } else {
            $error = "You do not have enough balance to request a payout.";
        }
    }
}

// Check affiliate status and fetch data
$stmt = $db->prepare("SELECT * FROM affiliates WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$affiliate = $stmt->get_result()->fetch_assoc();

if ($affiliate) {
    // Fetch stats
    $stmt = $db->prepare("SELECT COUNT(id) as clicks FROM affiliate_clicks WHERE affiliate_user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $clicks = $stmt->get_result()->fetch_assoc()['clicks'] ?? 0;

    $stmt = $db->prepare("SELECT COUNT(referred_user_id) as signups FROM affiliate_referrals WHERE affiliate_user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $signups = $stmt->get_result()->fetch_assoc()['signups'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Program - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Affiliate Program</h1>
    </div>

    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <?php if ($affiliate): ?>
        <!-- Affiliate Dashboard -->
        <div class="card mb-4">
            <div class="card-header">Your Referral Link</div>
            <div class="card-body">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($settings['base_url'] . '/?ref=' . $affiliate['referral_code']); ?>" readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4"><div class="stat-card"><h5 class="stat-card-title">Clicks</h5><p class="stat-card-value"><?php echo $clicks; ?></p></div></div>
            <div class="col-md-4"><div class="stat-card"><h5 class="stat-card-title">Signups</h5><p class="stat-card-value"><?php echo $signups; ?></p></div></div>
            <div class="col-md-4"><div class="stat-card"><h5 class="stat-card-title">Commission Balance</h5><p class="stat-card-value">$<?php echo number_format($affiliate['commission_balance'], 2); ?></p></div></div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Request Payout</div>
            <div class="card-body">
                <?php
                $min_payout = (float)($settings['affiliate_min_payout'] ?? 50);
                if ($affiliate['commission_balance'] >= $min_payout):
                ?>
                    <form action="affiliates.php" method="post">
                        <input type="hidden" name="action" value="request_payout">
                        <p>You can request a payout of your current balance.</p>
                        <button type="submit" class="btn btn-success">Request Payout ($<?php echo number_format($affiliate['commission_balance'], 2); ?>)</button>
                    </form>
                <?php else: ?>
                    <p>You need a minimum balance of $<?php echo number_format($min_payout, 2); ?> to request a payout.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Activation Page -->
        <div class="card text-center">
            <div class="card-body">
                <h4>Join Our Affiliate Program</h4>
                <p>Earn commissions by referring new customers to us. Activate your affiliate account today!</p>
                <form action="affiliates.php" method="post">
                    <input type="hidden" name="action" value="activate_affiliate">
                    <button type="submit" class="btn btn-primary btn-lg">Activate Now</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

     <div class="text-center mt-4">
        <a href="index.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
