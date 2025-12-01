<?php
// installer/index.php

session_start();

$step = isset($_GET['step']) ? $_GET['step'] : 1;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        // --- Step 2: Database Configuration ---
        $_SESSION['db_host'] = $_POST['db_host'];
        $_SESSION['db_name'] = $_POST['db_name'];
        $_SESSION['db_user'] = $_POST['db_user'];
        $_SESSION['db_pass'] = $_POST['db_pass'];

        try {
            $mysqli = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
            if ($mysqli->connect_error) {
                throw new Exception("Connection failed: " . $mysqli->connect_error);
            }

            $sql = file_get_contents('schema.sql');
            if ($sql === false) throw new Exception("Could not read schema.sql file.");

            if (!$mysqli->multi_query($sql)) {
                throw new Exception("Error creating database schema: " . $mysqli->error);
            }

            do { if ($result = $mysqli->store_result()) { $result->free(); } } while ($mysqli->next_result());
            $mysqli->close();

            header('Location: ?step=3');
            exit;

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($step == 3) {
        // --- Step 3: Admin & System Setup ---
        // Save form data to session immediately
        $_SESSION['admin_name'] = $_POST['admin_name'];
        $_SESSION['admin_email'] = $_POST['admin_email'];
        $_SESSION['admin_password'] = $_POST['admin_password']; // Store plaintext temporarily
        $_SESSION['base_url'] = $_POST['base_url'];
        $_SESSION['system_email'] = $_POST['system_email'];

        // Create config file content
        $config_content = "<?php\n\n" .
            "define('DB_HOST', '" . $_SESSION['db_host'] . "');\n" .
            "define('DB_NAME', '" . $_SESSION['db_name'] . "');\n" .
            "define('DB_USER', '" . $_SESSION['db_user'] . "');\n" .
            "define('DB_PASS', '" . $_SESSION['db_pass'] . "');\n";

        $config_dir = '../config';
        if (is_writable($config_dir) && file_put_contents($config_dir . '/config.php', $config_content) !== false) {
            // Config written, now insert data
            try {
                $mysqli = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
                if ($mysqli->connect_error) throw new Exception("Database connection failed again. Please check your config.");

                $admin_password_hash = password_hash($_SESSION['admin_password'], PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("INSERT INTO staff (name, email, password, role_id) VALUES (?, ?, ?, 1)");
                $stmt->bind_param('sss', $_SESSION['admin_name'], $_SESSION['admin_email'], $admin_password_hash);
                $stmt->execute();
                $stmt->close();

                $settings = ['base_url' => $_SESSION['base_url'], 'system_email' => $_SESSION['system_email']];
                $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE setting = ?");
                foreach ($settings as $key => $value) {
                    $stmt->bind_param('ss', $value, $key);
                    $stmt->execute();
                }
                $stmt->close();
                $mysqli->close();

                header('Location: ?step=4');
                exit;
            } catch (Exception $e) {
                $error = "Config file was written, but failed to save admin details: " . $e->getMessage();
            }
        } else {
            // Not writable, go to manual step
            $_SESSION['config_content'] = $config_content;
            header('Location: ?step=3_manual');
            exit;
        }
    } elseif ($step === '3_manual' && isset($_POST['verify_config'])) {
        if (file_exists('../config/config.php')) {
            // Config file exists, now try to insert the admin data from session
            try {
                $mysqli = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
                if ($mysqli->connect_error) throw new Exception("Database connection failed. Please check the details in your manually created config.php.");

                $admin_password_hash = password_hash($_SESSION['admin_password'], PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("INSERT INTO staff (name, email, password, role_id) VALUES (?, ?, ?, 1)");
                $stmt->bind_param('sss', $_SESSION['admin_name'], $_SESSION['admin_email'], $admin_password_hash);
                $stmt->execute();
                $stmt->close();

                $settings = ['base_url' => $_SESSION['base_url'], 'system_email' => $_SESSION['system_email']];
                $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE setting = ?");
                foreach ($settings as $key => $value) {
                    $stmt->bind_param('ss', $value, $key);
                    $stmt->execute();
                }
                $stmt->close();
                $mysqli->close();

                header('Location: ?step=4');
                exit;
            } catch (Exception $e) {
                $error = "Your config.php is correct, but failed to save admin details: " . $e->getMessage();
            }
        } else {
            $error = "The config.php file has not been created yet. Please follow the instructions below.";
        }
    }
}

switch ($step) {
    case 4:
        require_once 'templates/step4.php';
        break;
    case '3_manual':
        require_once 'templates/step3_manual_view.php';
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
