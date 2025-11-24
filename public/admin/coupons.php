<?php
require_once 'templates/header.php';
require_permission('manage_coupons');

$error = $success = null;

// Handle add/edit/delete actions
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$coupon_id = $_POST['coupon_id'] ?? $_GET['id'] ?? null;

try {
    if ($action === 'delete' && $coupon_id) {
        $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param('i', $coupon_id);
        $stmt->execute();
        $success = "Coupon deleted successfully.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $code = $_POST['code'];
        $type = $_POST['type'];
        $value = $_POST['value'];
        $max_uses = $_POST['max_uses'];
        $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

        if ($action === 'edit' && $coupon_id) {
            $stmt = $db->prepare("UPDATE coupons SET code = ?, type = ?, value = ?, max_uses = ?, expires_at = ? WHERE id = ?");
            $stmt->bind_param('ssdisi', $code, $type, $value, $max_uses, $expires_at, $coupon_id);
            $stmt->execute();
            $success = "Coupon updated successfully.";
        } elseif ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO coupons (code, type, value, max_uses, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdis', $code, $type, $value, $max_uses, $expires_at);
            $stmt->execute();
            $success = "Coupon added successfully.";
        }
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Fetch all coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$coupon_to_edit = null;
if ($action === 'edit' && $coupon_id) {
    $stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->bind_param('i', $coupon_id);
    $stmt->execute();
    $coupon_to_edit = $stmt->get_result()->fetch_assoc();
}
?>

<h1>Coupon Management</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<!-- Add/Edit Form -->
<div class="card mb-4">
    <div class="card-header"><?php echo $coupon_to_edit ? 'Edit Coupon' : 'Add New Coupon'; ?></div>
    <div class="card-body">
        <form action="coupons.php" method="post">
            <input type="hidden" name="action" value="<?php echo $coupon_to_edit ? 'edit' : 'add'; ?>">
            <?php if ($coupon_to_edit) echo "<input type='hidden' name='coupon_id' value='{$coupon_to_edit['id']}'>"; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Coupon Code</label>
                    <input type="text" class="form-control" name="code" value="<?php echo $coupon_to_edit['code'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="percentage" <?php echo ($coupon_to_edit['type'] ?? '') === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                        <option value="fixed" <?php echo ($coupon_to_edit['type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="value" class="form-label">Value</label>
                    <input type="number" step="0.01" class="form-control" name="value" value="<?php echo $coupon_to_edit['value'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="max_uses" class="form-label">Max Uses (0 for unlimited)</label>
                    <input type="number" class="form-control" name="max_uses" value="<?php echo $coupon_to_edit['max_uses'] ?? '0'; ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="expires_at" class="form-label">Expires At (optional)</label>
                <input type="date" class="form-control" name="expires_at" value="<?php echo $coupon_to_edit['expires_at'] ?? ''; ?>">
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $coupon_to_edit ? 'Update Coupon' : 'Add Coupon'; ?></button>
        </form>
    </div>
</div>

<!-- Coupon List -->
<div class="card">
    <div class="card-header">Existing Coupons</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Code</th><th>Type</th><th>Value</th><th>Uses</th><th>Expires</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                        <td><?php echo ucfirst($coupon['type']); ?></td>
                        <td><?php echo $coupon['type'] === 'percentage' ? "{$coupon['value']}%" : "\${$coupon['value']}"; ?></td>
                        <td><?php echo "{$coupon['uses']} / {$coupon['max_uses']}"; ?></td>
                        <td><?php echo $coupon['expires_at'] ? date('M j, Y', strtotime($coupon['expires_at'])) : 'Never'; ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?action=delete&id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
