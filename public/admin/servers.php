<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';

// Handle form submission for adding a new server
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_server'])) {
    verify_csrf_token();

    $name = $_POST['name'];
    $hostname = $_POST['hostname'];
    $api_key_1 = $_POST['api_key_1'];
    $module = $_POST['module'];

    if (empty($name) || empty($hostname) || empty($module)) {
        $message = "<div class='alert alert-danger'>Name, Hostname, and Module are required.</div>";
    } else {
        $stmt = $db->prepare("INSERT INTO servers (name, hostname, api_key_1, module) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $name, $hostname, $api_key_1, $module);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Server added successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to add server.</div>";
        }
    }
}

// Fetch all servers
$servers_result = $db->query("SELECT * FROM servers ORDER BY name ASC");
?>

<h1 class="mb-4">Server Management</h1>
<?php echo $message; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
             <div class="card-header"><h5>Existing Servers</h5></div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Hostname</th>
                            <th>Module</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($server = $servers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $server['id']; ?></td>
                            <td><?php echo htmlspecialchars($server['name']); ?></td>
                            <td><?php echo htmlspecialchars($server['hostname']); ?></td>
                            <td><?php echo htmlspecialchars($server['module']); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-info">Edit</a>
                                <a href="#" class="btn btn-sm btn-warning">Test Connection</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>Add New Server</h5></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Server Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                     <div class="mb-3">
                        <label for="hostname" class="form-label">Hostname or IP Address</label>
                        <input type="text" class="form-control" name="hostname" id="hostname" required>
                    </div>
                     <div class="mb-3">
                        <label for="api_key_1" class="form-label">API Token / Key 1</label>
                        <input type="password" class="form-control" name="api_key_1" id="api_key_1">
                    </div>
                     <div class="mb-3">
                        <label for="module" class="form-label">Module</label>
                        <select name="module" id="module" class="form-select" required>
                            <option value="Cpanel">cPanel/WHM</option>
                            <!-- Add other modules here as they are created -->
                        </select>
                    </div>
                    <button type="submit" name="add_server" class="btn btn-primary">Add Server</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
require_once 'footer.php';
?>
