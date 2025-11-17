<?php
// public/admin/servers.php
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$error = null;
$success = null;

// Handle form submission for adding a new server
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $hostname = $_POST['hostname'] ?? '';
    $username = $_POST['username'] ?? '';
    $api_token = $_POST['api_token'] ?? '';
    $module = $_POST['module'] ?? '';

    if (empty($name) || empty($hostname) || empty($username) || empty($api_token) || empty($module)) {
        $error = "All fields are required.";
    } else {
        $stmt = $db->prepare("INSERT INTO servers (name, hostname, username, api_token, module) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $hostname, $username, $api_token, $module);

        if ($stmt->execute()) {
            $success = "Server added successfully.";
        } else {
            $error = "Failed to add server. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch all existing servers
$servers = $db->query("SELECT * FROM servers ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manage Servers';
include __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Servers</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                Add New Server
            </div>
            <div class="card-body">
                <form action="servers.php" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Server Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <small class="form-text text-muted">A friendly name for the server (e.g., "US cPanel Server 1").</small>
                    </div>
                    <div class="mb-3">
                        <label for="hostname" class="form-label">Hostname or IP Address</label>
                        <input type="text" class="form-control" id="hostname" name="hostname" required>
                        <small class="form-text text-muted">e.g., "server1.myhost.com"</small>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">WHM Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="api_token" class="form-label">WHM API Token</label>
                        <textarea class="form-control" id="api_token" name="api_token" rows="3" required></textarea>
                        <small class="form-text text-muted">Generate this in WHM's "Manage API Tokens" area.</small>
                    </div>
                    <div class="mb-3">
                        <label for="module" class="form-label">Module</label>
                        <select class="form-select" id="module" name="module" required>
                            <option value="">Select a module...</option>
                            <option value="Cpanel">cPanel</option>
                            <!-- Add other modules here as they are created -->
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Server</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Existing Servers
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Hostname</th>
                                <th>Module</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($servers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No servers have been added yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($servers as $server): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($server['name']); ?></td>
                                        <td><?php echo htmlspecialchars($server['hostname']); ?></td>
                                        <td><?php echo htmlspecialchars($server['module']); ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-info disabled">Edit</a>
                                            <a href="#" class="btn btn-sm btn-danger disabled">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/admin_footer.php';
