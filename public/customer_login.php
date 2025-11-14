<?php
require_once '../app/core/bootstrap.php';
require_once '../app/core/reseller_bootstrap.php';

// This page should only be accessed via a reseller's custom domain
$reseller_env = initialize_reseller_environment();
$reseller_settings = $reseller_env['reseller_settings'];

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    // In a real application, you would have a password field and proper authentication

    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ? AND reseller_user_id = ?");
    $stmt->bind_param('si', $email, $reseller_settings['user_id']);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();

    if ($customer) {
        $_SESSION['customer_id'] = $customer['id'];
        header('Location: index.php'); // Redirect to the reseller's product page
        exit;
    } else {
        $error = "Invalid login details.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - <?php echo htmlspecialchars($reseller_settings['company_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
         <?php if (!empty($reseller_settings['logo_url'])): ?>
            <img src="<?php echo htmlspecialchars($reseller_settings['logo_url']); ?>" alt="<?php echo htmlspecialchars($reseller_settings['company_name']); ?>" style="max-height: 80px; margin-bottom: 20px;">
        <?php endif; ?>
        <h1>Customer Login</h1>
    </div>

    <div class="card" style="max-width: 500px; margin: auto;">
        <div class="card-body">
            <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form action="customer_login.php" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <!-- Password field would go here -->
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    <div class="text-center mt-3">
        <a href="index.php">Back to Products</a>
    </div>
</div>
</body>
</html>
