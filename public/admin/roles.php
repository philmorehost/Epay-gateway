<?php
require_once 'templates/header.php';
require_permission('manage_staff');

$error = $success = null;

// Define available permissions
$available_permissions = [
    'manage_products' => 'Manage Products',
    'manage_coupons' => 'Manage Coupons',
    'manage_staff' => 'Manage Staff',
    'view_reports' => 'View Reports',
    'manage_settings' => 'Manage Settings',
    'manage_servers' => 'Manage Servers',
];

// Handle form submissions
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$role_id = $_POST['role_id'] ?? $_GET['id'] ?? null;

try {
    if ($action === 'delete' && $role_id != 1) { // Prevent deleting Super Admin
        $stmt = $db->prepare("DELETE FROM staff_roles WHERE id = ?");
        $stmt->bind_param('i', $role_id);
        $stmt->execute();
        $success = "Role deleted successfully.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $permissions = $_POST['permissions'] ?? [];

        if ($action === 'edit' && $role_id) {
            $stmt = $db->prepare("UPDATE staff_roles SET name = ? WHERE id = ?");
            $stmt->bind_param('si', $name, $role_id);
            $stmt->execute();

            // Update permissions
            $db->query("DELETE FROM staff_permissions WHERE role_id = $role_id");
            $stmt = $db->prepare("INSERT INTO staff_permissions (role_id, permission) VALUES (?, ?)");
            foreach ($permissions as $permission) {
                $stmt->bind_param('is', $role_id, $permission);
                $stmt->execute();
            }
            $success = "Role updated successfully.";
        } elseif ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO staff_roles (name) VALUES (?)");
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $new_role_id = $db->insert_id;

            // Add permissions
            $stmt = $db->prepare("INSERT INTO staff_permissions (role_id, permission) VALUES (?, ?)");
            foreach ($permissions as $permission) {
                $stmt->bind_param('is', $new_role_id, $permission);
                $stmt->execute();
            }
            $success = "Role added successfully.";
        }
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Fetch roles and their permissions
$roles = $db->query("SELECT * FROM staff_roles ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$permissions_by_role = [];
$permissions_result = $db->query("SELECT * FROM staff_permissions");
while ($row = $permissions_result->fetch_assoc()) {
    $permissions_by_role[$row['role_id']][] = $row['permission'];
}

$role_to_edit = null;
if ($action === 'edit' && $role_id) {
    foreach ($roles as $role) {
        if ($role['id'] == $role_id) $role_to_edit = $role;
    }
}
?>

<h1>Staff Role Management</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<!-- Add/Edit Form -->
<div class="card mb-4">
    <div class="card-header"><?php echo $role_to_edit ? 'Edit Role' : 'Add New Role'; ?></div>
    <div class="card-body">
        <form action="roles.php" method="post">
            <input type="hidden" name="action" value="<?php echo $role_to_edit ? 'edit' : 'add'; ?>">
            <?php if ($role_to_edit) echo "<input type='hidden' name='role_id' value='{$role_to_edit['id']}'>"; ?>

            <div class="mb-3">
                <label for="name" class="form-label">Role Name</label>
                <input type="text" class="form-control" name="name" value="<?php echo $role_to_edit['name'] ?? ''; ?>" required <?php if ($role_to_edit && $role_to_edit['id'] == 1) echo 'readonly'; ?>>
            </div>

            <h5>Permissions</h5>
            <?php foreach ($available_permissions as $perm_key => $perm_name): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $perm_key; ?>" id="perm_<?php echo $perm_key; ?>"
                        <?php if ($role_to_edit && $role_to_edit['id'] == 1) echo 'checked disabled'; ?>
                        <?php if ($role_to_edit && isset($permissions_by_role[$role_to_edit['id']]) && in_array($perm_key, $permissions_by_role[$role_to_edit['id']])) echo 'checked'; ?>
                    >
                    <label class="form-check-label" for="perm_<?php echo $perm_key; ?>"><?php echo $perm_name; ?></label>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary mt-3"><?php echo $role_to_edit ? 'Update Role' : 'Add Role'; ?></button>
        </form>
    </div>
</div>

<!-- Role List -->
<div class="card">
    <div class="card-header">Existing Roles</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Role Name</th><th>Permissions</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role['name']); ?></td>
                        <td>
                            <?php
                            $role_perms = $permissions_by_role[$role['id']] ?? [];
                            if ($role['id'] == 1) {
                                echo '<em>All Permissions</em>';
                            } else {
                                echo implode(', ', array_map(function($p) use ($available_permissions) {
                                    return $available_permissions[$p] ?? $p;
                                }, $role_perms));
                            }
                            ?>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?php echo $role['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <?php if ($role['id'] != 1): // Prevent deleting Super Admin ?>
                                <a href="?action=delete&id=<?php echo $role['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
