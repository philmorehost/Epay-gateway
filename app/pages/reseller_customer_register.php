<?php
// app/pages/reseller_customer_register.php

// This page must only be accessible from a reseller storefront.
if (!defined('IS_RESELLER_STOREFRONT') || !IS_RESELLER_STOREFRONT) {
    // Redirect to the main site's registration page or show an error
    header('Location: /index.php?page=register');
    exit;
}

global $db, $reseller_data;
$reseller_id = RESELLER_USER_ID; // This constant is defined in host_check.php
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email is already in use for this reseller
        $stmt = $db->prepare("SELECT id FROM customers WHERE email = ? AND reseller_id = ?");
        $stmt->bind_param("si", $email, $reseller_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "This email address is already registered.";
        } else {
            // Create the new customer account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $db->prepare("INSERT INTO customers (reseller_id, name, email, password) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("isss", $reseller_id, $name, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                $success = "Registration successful! You can now log in.";
                // Optionally, you could auto-login the user here.
            } else {
                $error = "An error occurred during registration. Please try again.";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}

$page_title = 'Register - ' . htmlspecialchars($reseller_data['settings']['company_name'] ?? 'Reseller');
include __DIR__ . '/../includes/reseller_storefront_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Register for an account with <?php echo htmlspecialchars($reseller_data['settings']['company_name'] ?? 'us'); ?></h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <a href="/index.php?page=reseller_customer_login">Click here to login</a>
                <?php else: ?>
                    <form action="/index.php?page=reseller_customer_register" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
