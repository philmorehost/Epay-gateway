<?php
// Destroy the session to clean up installation data
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Installation Complete!</h1>
        <div class="alert alert-success" role="alert">
            Congratulations! The application has been installed successfully.
        </div>
        <div class="alert alert-warning" role="alert">
            <strong>Security Warning:</strong> For security reasons, you must now delete the <strong>installer</strong> directory from your server.
        </div>
        <p>Once you have deleted the installer directory, you can log in to the admin panel using the credentials you provided.</p>
        <a href="../public/admin/" class="btn btn-primary">Go to Admin Panel</a>
    </div>
</body>
</html>
