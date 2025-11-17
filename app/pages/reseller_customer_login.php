<?php
// app/pages/reseller_customer_login.php

if (!defined('IS_RESELLER_STOREFRONT') || !IS_RESELLER_STOREFRONT) {
    header('Location: /index.php?page=login');
    exit;
}

global $db, $reseller_data;
$reseller_id = RESELLER_USER_ID;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $stmt = $db->prepare("SELECT id, name, password FROM customers WHERE email = ? AND reseller_id = ?");
        $stmt->bind_param("si", $email, $reseller_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();
            if (password_verify($password, $customer['password'])) {
                // Password is correct, set up the session
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                $_SESSION['reseller_id_for_customer'] = $reseller_id; // To keep context

                // Redirect to the reseller's dashboard/storefront
                header('Location: /index.php');
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}

$page_title = 'Login - ' . htmlspecialchars($reseller_data['settings']['company_name'] ?? 'Reseller');
include __DIR__ . '/../includes/reseller_storefront_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Login to your <?php echo htmlspecialchars($reseller_data['settings']['company_name'] ?? 'Reseller'); ?> Account</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="/index.php?page=reseller_customer_login" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
            <div class="card-footer text-center">
                Don't have an account? <a href="/index.php?page=reseller_customer_register">Register here</a>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
