<?php
// installer/index.php

session_start();

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // --- Step 2: Database Configuration ---
        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];

        $_SESSION['db_host'] = $db_host;
        $_SESSION['db_name'] = $db_name;
        $_SESSION['db_user'] = $db_user;
        $_SESSION['db_pass'] = $db_pass;

        try {
            $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
            if ($mysqli->connect_error) {
                throw new Exception("Connection failed: " . $mysqli->connect_error);
            }

            $sql = file_get_contents('schema.sql');
            if ($sql === false) {
                throw new Exception("Could not read schema.sql file.");
            }

            if (!$mysqli->multi_query($sql)) {
                throw new Exception("Error creating database schema: " . $mysqli->error);
            }

            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->next_result());

            $mysqli->close();

            header('Location: ?step=3');
            exit;

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($step === 3) {
        // --- Step 3: Admin & System Setup ---
        $admin_name = $_POST['admin_name'];
        $admin_email = $_POST['admin_email'];
        $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

        $base_url = $_POST['base_url'];
        $system_email = $_POST['system_email'];

        try {
            $mysqli = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
            if ($mysqli->connect_error) {
                throw new Exception("Database connection failed: " . $mysqli->connect_error);
            }

            // Insert Admin (now Staff)
            $stmt = $mysqli->prepare("INSERT INTO staff (name, email, password, role_id) VALUES (?, ?, ?, 1)");
            $stmt->bind_param('sss', $admin_name, $admin_email, $admin_password);
            $stmt->execute();
            $stmt->close();

            // Insert Settings
            $settings = [
                'base_url' => $base_url,
                'system_email' => $system_email,
            ];
            $stmt = $mysqli->prepare("INSERT INTO settings (setting, value) VALUES (?, ?)");
            foreach ($settings as $key => $value) {
                $stmt->bind_param('ss', $key, $value);
                $stmt->execute();
            }
            $stmt->close();

            // Create config file content
            $config_content = "<?php\n\n" .
                "define('DB_HOST', '" . $_SESSION['db_host'] . "');\n" .
                "define('DB_NAME', '" . $_SESSION['db_name'] . "');\n" .
                "define('DB_USER', '" . $_SESSION['db_user'] . "');\n" .
                "define('DB_PASS', '" . $_SESSION['db_pass'] . "');\n";

            // Check if config directory is writable
            $config_dir = '../config';
            if (is_writable($config_dir)) {
                if (file_put_contents($config_dir . '/config.php', $config_content) === false) {
                    // If writing fails despite being writable, show manual step
                    $_SESSION['config_content'] = $config_content;
                    header('Location: ?step=3_manual');
                    exit;
                }
                header('Location: ?step=4');
            } else {
                // Not writable, so redirect to manual configuration step
                $_SESSION['config_content'] = $config_content;
                header('Location: ?step=3_manual');
            }

            $mysqli->close();
            exit;

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

switch ($step) {
    case 4:
        require_once 'templates/step4.php';
        break;
    case '3_manual':
        require_once 'templates/step3_manual.php';
        break;
    case 3:
        require_once 'templates/step3.php';
        break;
    case 2:
        require_once 'templates/step2.php';
        break;
    case 1:
    default:
        require_once 'templates/step1.php';
        break;
}
