<?php
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';

    // Test the connection
    @$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($mysqli->connect_error) {
        $error = "Connection failed: " . $mysqli->connect_error;
    } else {
        // Store credentials in session
        $_SESSION['db_host'] = $db_host;
        $_SESSION['db_name'] = $db_name;
        $_SESSION['db_user'] = $db_user;
        $_SESSION['db_pass'] = $db_pass;

        // Execute the schema
        $sql = file_get_contents('schema.sql');
        if ($mysqli->multi_query($sql)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            // Redirect to the next step
            header('Location: index.php?step=3');
            exit;
        } else {
            $error = "Error creating tables: " . $mysqli->error;
        }
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Database Configuration</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger mt-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="index.php?step=2" method="post">
            <div class="mb-3">
                <label for="db_host" class="form-label">Database Host</label>
                <input type="text" class="form-control" id="db_host" name="db_host" required>
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
            <button type="submit" class="btn btn-primary">Next</button>
        </form>
    </div>
</body>
</html>
