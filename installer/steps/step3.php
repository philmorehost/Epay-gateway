<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Step 3: Admin & System Setup</h1>
        <form action="index.php?step=4" method="post">
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
            <div class="mb-3">
                <label for="base_url" class="form-label">Base URL</label>
                <input type="text" class="form-control" id="base_url" name="base_url" required>
            </div>
            <div class="mb-3">
                <label for="system_email" class="form-label">System Email Address</label>
                <input type="email" class="form-control" id="system_email" name="system_email" required>
            </div>
            <button type="submit" class="btn btn-primary">Next</button>
        </form>
    </div>
</body>
</html>
