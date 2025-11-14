<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration - Installer Step 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>Database Configuration</h1>
        <p>Please enter your database connection details.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="?step=2" method="post">
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
        <button type="submit" class="btn btn-primary w-100">Next</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
