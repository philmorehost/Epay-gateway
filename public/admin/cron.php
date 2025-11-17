<?php
// public/admin/cron.php
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

// Handle manual trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_cron'])) {
    // This provides a way to manually trigger the cron script for testing.
    // In a real environment, you'd want to be careful with output buffering and execution time.
    $output = shell_exec('php ' . __DIR__ . '/../../app/core/cron.php');
    $success = "Cron job runner executed manually. Output:<pre>" . htmlspecialchars($output) . "</pre>";
}

// Fetch all cron jobs to display their status
$cron_jobs = $db->query("SELECT * FROM cron_jobs ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Cron Job Status';
include __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Cron Job Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <form action="cron.php" method="post" class="d-inline">
            <button type="submit" name="run_cron" value="1" class="btn btn-sm btn-outline-primary">
                Run Cron Manually
            </button>
        </form>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Scheduled Tasks
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Schedule</th>
                        <th>Next Run</th>
                        <th>Last Run</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cron_jobs)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No cron jobs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cron_jobs as $job): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['name']); ?></td>
                                <td>Runs every <?php echo htmlspecialchars($job['schedule']); ?></td>
                                <td><?php echo htmlspecialchars($job['next_run']); ?></td>
                                <td><?php echo htmlspecialchars($job['last_run'] ?? 'Never'); ?></td>
                                <td>
                                    <?php if ($job['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <p class="text-muted">
                <strong>Note:</strong> For automated execution, the master cron script located at <code>app/core/cron.php</code> needs to be triggered by your server's cron daemon (e.g., every 5 minutes).
            </p>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/admin_footer.php';
