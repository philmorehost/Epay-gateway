<?php
// Step 1: Welcome & Requirements Check

$php_version_required = '7.4.0';
$php_version_ok = version_compare(PHP_VERSION, $php_version_required, '>=');

$extensions_required = ['mysqli', 'curl', 'json'];
$extensions_ok = true;
$extension_statuses = [];
foreach ($extensions_required as $ext) {
    $loaded = extension_loaded($ext);
    $extension_statuses[$ext] = $loaded;
    if (!$loaded) {
        $extensions_ok = false;
    }
}

$all_ok = $php_version_ok && $extensions_ok;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['continue'])) {
    if ($all_ok) {
        $_SESSION['installer_step_completed'][1] = true;
        header('Location: index.php?step=2');
        exit;
    }
}

?>

<h2 class="mb-4">Step 1: Welcome & Requirements Check</h2>
<p>Welcome to the Hostbill installer. This wizard will guide you through the setup process. First, let's check if your server meets the minimum requirements.</p>

<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>Requirement</th>
            <th>Current</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>PHP Version >= <?php echo $php_version_required; ?></td>
            <td><?php echo PHP_VERSION; ?></td>
            <td>
                <?php if ($php_version_ok): ?>
                    <span class="badge bg-success">OK</span>
                <?php else: ?>
                    <span class="badge bg-danger">Failed</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php foreach ($extension_statuses as $ext => $status): ?>
        <tr>
            <td>PHP Extension: <?php echo $ext; ?></td>
            <td><?php echo $status ? 'Loaded' : 'Not Loaded'; ?></td>
            <td>
                <?php if ($status): ?>
                    <span class="badge bg-success">OK</span>
                <?php else: ?>
                    <span class="badge bg-danger">Failed</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="mt-4 text-end">
    <?php if ($all_ok): ?>
        <form method="POST">
            <button type="submit" name="continue" class="btn btn-primary">Continue to Step 2</button>
        </form>
    <?php else: ?>
        <p class="text-danger">Your server does not meet the minimum requirements. Please resolve the issues above and click Retry.</p>
        <a href="index.php?step=1" class="btn btn-secondary">Retry</a>
    <?php endif; ?>
</div>
