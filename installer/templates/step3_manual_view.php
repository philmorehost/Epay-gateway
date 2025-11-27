<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostBill-1 Installer - Manual Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Manual Configuration Required</h1>
        <p>Step 3 of 4</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="alert alert-warning">
        The installer was unable to write the configuration file automatically, likely due to file permissions.
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Please complete the following steps:</h5>
            <ol>
                <li>
                    Create a new file named <code>config.php</code> inside the <code>config/</code> directory in your application's root.
                </li>
                <li>
                    Copy the entire content from the text area below and paste it into the <code>config.php</code> file.
                </li>
                <li>
                    Save the file on your server.
                </li>
                <li>
                    Once the file is saved, click the "Verify and Continue" button below.
                </li>
            </ol>

            <div class="mb-3">
                <label for="config_content" class="form-label"><strong>config.php Content:</strong></label>
                <textarea class="form-control" id="config_content" rows="8" readonly><?php echo htmlspecialchars($_SESSION['config_content']); ?></textarea>
            </div>

            <form action="?step=3_manual" method="post">
                <button type="submit" name="verify_config" class="btn btn-primary">Verify and Continue</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
