<?php
$prerequisites = [
    'php_version' => [
        'name' => 'PHP Version >= 8.0',
        'check' => version_compare(PHP_VERSION, '8.0.0', '>='),
    ],
    'mysqli' => [
        'name' => 'MySQLi Extension',
        'check' => extension_loaded('mysqli'),
    ],
    'curl' => [
        'name' => 'cURL Extension',
        'check' => extension_loaded('curl'),
    ],
    'json' => [
        'name' => 'JSON Extension',
        'check' => extension_loaded('json'),
    ],
];

$all_ok = true;
foreach ($prerequisites as $prerequisite) {
    if (!$prerequisite['check']) {
        $all_ok = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Welcome & Prerequisites</h1>
        <p>Welcome to the installation process. The following prerequisites will be checked:</p>
        <ul class="list-group">
            <?php foreach ($prerequisites as $prerequisite): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo $prerequisite['name']; ?>
                    <?php if ($prerequisite['check']): ?>
                        <span class="badge bg-success">Pass</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Fail</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($all_ok): ?>
            <a href="index.php?step=2" class="btn btn-primary mt-4">Next</a>
        <?php else: ?>
            <div class="alert alert-danger mt-4">
                Please resolve the issues above before continuing.
            </div>
            <a href="index.php?step=2" class="btn btn-primary mt-4 disabled">Next</a>
        <?php endif; ?>
    </div>
</body>
</html>
