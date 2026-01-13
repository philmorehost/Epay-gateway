<?php
// Step 4: Finish Installation

$self_destruct_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish_installation'])) {
    // This is the action to "self-destruct" the installer directory

    // Let's first define the path to the installer directory
    $installer_dir = __DIR__;

    // We'll try to recursively delete the directory and its contents
    function delete_dir($dir_path) {
        if (!is_dir($dir_path)) {
            return;
        }
        $files = array_diff(scandir($dir_path), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir_path/$file")) ? delete_dir("$dir_path/$file") : unlink("$dir_path/$file");
        }
        return rmdir($dir_path);
    }

    if (delete_dir($installer_dir)) {
        // If deletion is successful, redirect to the admin login page
        // The location will be something like /public/admin/login.php
        // For now, let's just redirect to the main public page
        session_destroy();
        header('Location: ../public');
        exit;
    } else {
        $self_destruct_error = "Could not automatically delete the installer directory. Please remove it manually for security reasons.";
    }
}


// Clear the session data
session_destroy();
?>

<h2 class="mb-4">Step 4: Installation Complete!</h2>

<?php if ($self_destruct_error): ?>
    <div class="alert alert-danger"><?php echo $self_destruct_error; ?></div>
<?php endif; ?>

<div class="alert alert-success">
    <strong>Congratulations!</strong> Hostbill has been successfully installed.
</div>

<div class="alert alert-warning">
    <strong>Security Warning:</strong> For your security, the installer directory must be removed. Please click the "Finish & Secure Installation" button below to attempt automatic removal. If this fails, you must delete the <strong>/installer</strong> directory from your server manually.
</div>

<p>You can now log in to your admin panel with the credentials you created in the previous step.</p>

<div class="mt-4 text-end">
    <form method="POST">
        <button type="submit" name="finish_installation" class="btn btn-danger">Finish & Secure Installation</button>
    </form>
</div>
