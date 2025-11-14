<?php
// installer/templates/step1.php

// --- Prerequisite Checks ---
$required_php_version = '8.0';
$required_extensions = ['mysqli', 'curl', 'json'];

$php_version_ok = version_compare(PHP_VERSION, $required_php_version, '>=');
$extensions_ok = [];
foreach ($required_extensions as $extension) {
    $extensions_ok[$extension] = extension_loaded($extension);
}

$all_checks_passed = $php_version_ok && !in_array(false, $extensions_ok, true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Installer Step 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="installer-container">
    <div class="installer-header">
        <h1>Welcome to the Installer</h1>
        <p>This wizard will guide you through the installation process.</p>
    </div>

    <div class="stat-card">
        <h5 class="stat-card-title">System prerequisites</h5>
        <ul class="list-group">
            <!-- PHP Version Check -->
            <li class="list-group-item <?php echo $php_version_ok ? 'success' : 'error'; ?>">
                PHP Version >= <?php echo $required_php_version; ?> (You have <?php echo PHP_VERSION; ?>)
            </li>

            <!-- Extensions Check -->
            <?php foreach ($extensions_ok as $extension => $status): ?>
                <li class="list-group-item <?php echo $status ? 'success' : 'error'; ?>">
                    <?php echo ucfirst($extension); ?> extension is <?php echo $status ? 'enabled' : 'not enabled'; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($all_checks_passed): ?>
        <a href="?step=2" class="btn btn-primary w-100">Start Installation</a>
    <?php else: ?>
        <div class="alert alert-danger" role="alert">
            Please fix the errors above before you can continue.
        </div>
        <button class="btn btn-primary w-100" disabled>Cannot Proceed</button>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
