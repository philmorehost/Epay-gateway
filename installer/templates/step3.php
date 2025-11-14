<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin & System Setup - Installer Step 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>Admin & System Setup</h1>
        <p>Create your administrator account and set up your system.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="?step=3" method="post">
        <h5>Admin User Details</h5>
        <div class="mb-3">
            <label for="admin_name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="admin_name" name="admin_name" required>
        </div>
        <div class="mb-3">
            <label for="admin_email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
        </div>
        <div class="mb-3">
            <label for="admin_password" class="form-label">Password</label>
            <input type="password" class="form-control" id="admin_password" name="admin_password" required>
        </div>

        <hr>

        <h5>System Settings</h5>
        <div class="mb-3">
            <label for="base_url" class="form-label">Base URL</label>
            <input type="text" class="form-control" id="base_url" name="base_url" required>
        </div>
        <div class="mb-3">
            <label for="system_email" class="form-label">System Email Address</label>
            <input type="email" class="form-control" id="system_email" name="system_email" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Complete Installation</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
