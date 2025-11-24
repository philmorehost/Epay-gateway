<?php
require_once 'templates/header.php';
require_permission('manage_staff');

$error = $success = null;

// Handle form submissions
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$staff_id = $_POST['staff_id'] ?? $_GET['id'] ?? null;

try {
    if ($action === 'delete' && $staff_id != $_SESSION['staff_id']) { // Prevent self-deletion
        $stmt = $db->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $success = "Staff member deleted successfully.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role_id = $_POST['role_id'];

        if ($action === 'edit' && $staff_id) {
            $sql = "UPDATE staff SET name = ?, email = ?, role_id = ?";
            $params = ['ssi', $name, $email, $role_id];
            if (!empty($password)) {
                $sql .= ", password = ?";
                $params[0] .= 's';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id = ?";
            $params[0] .= 'i';
            $params[] = $staff_id;

            $stmt = $db->prepare($sql);
            $stmt->bind_param(...$params);
            $stmt->execute();
            $success = "Staff member updated successfully.";
        } elseif ($action === 'add') {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO staff (name, email, password, role_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $name, $email, $hashed_password, $role_id);
            $stmt->execute();
            $success = "Staff member added successfully.";
        }
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Fetch staff and roles
$staff_members = $db->query("SELECT s.*, sr.name as role_name FROM staff s LEFT JOIN staff_roles sr ON s.role_id = sr.id ORDER BY s.name ASC")->fetch_all(MYSQLI_ASSOC);
$roles = $db->query("SELECT * FROM staff_roles ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$staff_to_edit = null;
if ($action === 'edit' && $staff_id) {
    $stmt = $db->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->bind_param('i', $staff_id);
    $stmt->execute();
    $staff_to_edit = $stmt->get_result()->fetch_assoc();
}
?>

<h1>Staff Management</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<!-- Add/Edit Form -->
<div class="card mb-4">
    <div class="card-header"><?php echo $staff_to_edit ? 'Edit Staff Member' : 'Add New Staff Member'; ?></div>
    <div class="card-body">
        <form action="staff.php" method="post">
            <input type="hidden" name="action" value="<?php echo $staff_to_edit ? 'edit' : 'add'; ?>">
            <?php if ($staff_to_edit) echo "<input type='hidden' name='staff_id' value='{$staff_to_edit['id']}'>"; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo $staff_to_edit['name'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo $staff_to_edit['email'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="row">
                 <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" <?php echo $staff_to_edit ? '' : 'required'; ?>>
                    <?php if ($staff_to_edit) echo '<small class="form-text text-muted">Leave blank to keep current password.</small>'; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="role_id" class="form-label">Role</label>
                    <select name="role_id" class="form-select" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo ($staff_to_edit['role_id'] ?? '') == $role['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $staff_to_edit ? 'Update Staff' : 'Add Staff'; ?></button>
        </form>
    </div>
</div>

<!-- Staff List -->
<div class="card">
    <div class="card-header">Existing Staff</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($staff_members as $staff): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($staff['name']); ?></td>
                        <td><?php echo htmlspecialchars($staff['email']); ?></td>
                        <td><?php echo htmlspecialchars($staff['role_name'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <?php if ($staff['id'] != $_SESSION['staff_id']): ?>
                                <a href="?action=delete&id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
