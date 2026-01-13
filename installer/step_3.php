<?php
// Step 3: Admin User Setup

$error_message = '';

// The config file must exist to proceed
if (!file_exists(__DIR__ . '/../config/config.php')) {
    $error_message = "Configuration file not found. Please complete Step 2 first.";
} else {
    require_once __DIR__ . '/../config/config.php';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error_message) {
    $admin_user = $_POST['admin_user'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';
    $admin_pass_confirm = $_POST['admin_pass_confirm'] ?? '';

    // --- Validation ---
    if (empty($admin_user) || empty($admin_email) || empty($admin_pass)) {
        $error_message = "All fields are required.";
    } elseif ($admin_pass !== $admin_pass_confirm) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($admin_pass) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address.";
    } else {
        try {
            // --- Connect to DB ---
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($db->connect_error) {
                throw new Exception("Database connection failed: " . $db->connect_error);
            }

            // --- Hash Password ---
            $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);

            // --- Insert Admin User ---
            $stmt = $db->prepare("INSERT INTO `admins` (`username`, `email`, `password`) VALUES (?, ?, ?)");
            if ($stmt === false) {
                 throw new Exception("Failed to prepare statement: " . $db->error);
            }

            $stmt->bind_param('sss', $admin_user, $admin_email, $hashed_password);

            if (!$stmt->execute()) {
                 throw new Exception("Failed to create admin user: " . $stmt->error);
            }

            $stmt->close();
            $db->close();

            // --- If successful, proceed ---
            $_SESSION['installer_step_completed'][3] = true;
            header('Location: index.php?step=4');
            exit;

        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}
?>

<h2 class="mb-4">Step 3: Admin User Setup</h2>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<p>Create your primary administrator account. You will use this to log in to the admin panel.</p>

<form method="POST">
    <div class="mb-3">
        <label for="admin_user" class="form-label">Admin Username</label>
        <input type="text" class="form-control" id="admin_user" name="admin_user" required>
    </div>
    <div class="mb-3">
        <label for="admin_email" class="form-label">Admin Email</label>
        <input type="email" class="form-control" id="admin_email" name="admin_email" required>
    </div>
    <div class="mb-3">
        <label for="admin_pass" class="form-label">Password</label>
        <input type="password" class="form-control" id="admin_pass" name="admin_pass" required>
    </div>
    <div class="mb-3">
        <label for="admin_pass_confirm" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="admin_pass_confirm" name="admin_pass_confirm" required>
    </div>
    <div class="mt-4 d-flex justify-content-between">
         <a href="index.php?step=2" class="btn btn-secondary">Back</a>
         <button type="submit" class="btn btn-primary" <?php if (!file_exists(__DIR__ . '/../config/config.php')) echo 'disabled'; ?>>Create Admin & Continue</button>
    </div>
</form>
