<?php
// Step 2: Database Configuration

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';

    // --- Connection Test ---
    try {
        @$db = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($db->connect_error) {
            throw new Exception("Connection failed: " . $db->connect_error);
        }

        // --- Check and create config directory ---
        $config_dir = __DIR__ . '/../config';
        if (!is_dir($config_dir)) {
            if (!@mkdir($config_dir, 0755, true)) {
                throw new Exception("Could not create config directory. Please check permissions.");
            }
        }

        // --- Write config.php file ---
        $config_content = "<?php\n\n";
        $config_content .= "// -- Database Configuration --\n";
        $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
        $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
        $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
        $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n\n";

        $config_path = $config_dir . '/config.php';

        if (!@file_put_contents($config_path, $config_content)) {
             throw new Exception("Could not write to config file. Please check permissions on the /config directory.");
        }

        // --- Import the database schema ---
        $schema_sql = file_get_contents('schema.sql');
        if ($schema_sql === false) {
            throw new Exception("Could not read schema.sql file.");
        }

        if (!$db->multi_query($schema_sql)) {
            // Clean up the failed config file
            unlink($config_path);
            throw new Exception("Failed to import database schema: " . $db->error);
        }

        // Clear results from multi_query
        while ($db->more_results() && $db->next_result()) {;}


        // --- If all successful, proceed ---
        $_SESSION['installer_step_completed'][2] = true;
        $db->close();
        header('Location: index.php?step=3');
        exit;

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<h2 class="mb-4">Step 2: Database Configuration</h2>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<p>Please enter your database connection details below. The installer will attempt to create the necessary tables.</p>

<form method="POST">
    <div class="mb-3">
        <label for="db_host" class="form-label">Database Host</label>
        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
    </div>
    <div class="mb-3">
        <label for="db_name" class="form-label">Database Name</label>
        <input type="text" class="form-control" id="db_name" name="db_name" required>
    </div>
    <div class="mb-3">
        <label for="db_user" class="form-label">Database Username</label>
        <input type="text" class="form-control" id="db_user" name="db_user" required>
    </div>
    <div class="mb-3">
        <label for="db_pass" class="form-label">Database Password</label>
        <input type="password" class="form-control" id="db_pass" name="db_pass">
    </div>
    <div class="mt-4 d-flex justify-content-between">
         <a href="index.php?step=1" class="btn btn-secondary">Back</a>
         <button type="submit" class="btn btn-primary">Save & Continue</button>
    </div>
</form>
