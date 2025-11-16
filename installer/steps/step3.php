<?php
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_SESSION['db_host'] ?? '';
    $db_name = $_SESSION['db_name'] ?? '';
    $db_user = $_SESSION['db_user'] ?? '';
    $db_pass = $_SESSION['db_pass'] ?? '';

    $admin_name = $_POST['admin_name'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';
    $base_url = $_POST['base_url'] ?? '';
    $system_email = $_POST['system_email'] ?? '';

    // Connect to the database
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($mysqli->connect_error) {
        $error = "Database connection failed: " . $mysqli->connect_error;
    } else {
        // Create admin user
        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $admin_name, $admin_email, $hashed_password);

        if ($stmt->execute()) {
            // Write config file
            $config_content = "<?php\n\n";
            $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
            $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
            $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
            $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n\n";
            $config_content .= "define('BASE_URL', '" . addslashes($base_url) . "');\n";
            $config_content .= "define('SYSTEM_EMAIL', '" . addslashes($system_email) . "');\n";

            if (file_put_contents('../config/config.php', $config_content)) {
                header('Location: index.php?step=4');
                exit;
            } else {
                $error = "Could not write config file. Please check permissions.";
            }
        } else {
            $error = "Error creating admin user: " . $stmt->error;
        }
        $stmt->close();
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Admin & System Setup</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger mt-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="index.php?step=3" method="post">
            <div class="mb-3">
                <label for="admin_name" class="form-label">Admin Name</label>
                <input type="text" class="form-control" id="admin_name" name="admin_name" required>
            </div>
            <div class="mb-3">
                <label for="admin_email" class="form-label">Admin Email</label>
                <input type="email" class="form-control" id="admin_email" name="admin_email" required>
            </div>
            <div class="mb-3">
                <label for="admin_pass" class="form-label">Admin Password</label>
                <input type="password" class="form-control" id="admin_pass" name="admin_pass" required>
            </div>
            <hr>
            <div class="mb-3">
                <label for="base_url" class="form-label">Base URL</label>
                <input type="text" class="form-control" id="base_url" name="base_url" required>
            </div>
            <div class="mb-3">
                <label for="system_email" class="form-label">System Email Address</label>
                <input type="email" class="form-control" id="system_email" name="system_email" required>
            </div>
            <button type="submit" class="btn btn-primary">Finish Installation</button>
        </form>
    </div>
</body>
</html>
