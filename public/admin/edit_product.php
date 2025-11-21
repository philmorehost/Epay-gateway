<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$error_message = '';
$success_message = '';
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Fetch servers for the dropdown
$servers = $db->query("SELECT id, name FROM servers ORDER BY name ASC");

// Fetch the product details
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token(); // CSRF check

    if (empty($_POST['name']) || empty($_POST['category'])) {
        $error_message = "Please fill in all required fields.";
    } else {
        $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, category = ?, price_monthly = ?, price_annually = ?, wholesale_discount_percent = ?, module = ?, server_id = ?, package_name = ? WHERE id = ?");

        $name = $_POST['name'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $price_monthly = (float)($_POST['price_monthly'] ?? 0);
        $price_annually = (float)($_POST['price_annually'] ?? 0);
        $wholesale_discount_percent = (float)($_POST['wholesale_discount_percent'] ?? 0);
        $module = $_POST['module'];
        $server_id = !empty($_POST['server_id']) ? (int)$_POST['server_id'] : null;
        $package_name = $_POST['package_name'];

        $stmt->bind_param('sssdddsisi', $name, $description, $category, $price_monthly, $price_annually, $wholesale_discount_percent, $module, $server_id, $package_name, $product_id);

        if ($stmt->execute()) {
            $success_message = "Product updated successfully! <a href='products.php'>Return to Products</a>";
            // Re-fetch product data to show updated values
            $product = array_merge($product, $_POST);
        } else {
            $error_message = "Failed to update product: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<h1>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h1>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>


<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="Shared Hosting" <?php echo $product['category'] == 'Shared Hosting' ? 'selected' : ''; ?>>Shared Hosting</option>
                        <option value="Dedicated Servers" <?php echo $product['category'] == 'Dedicated Servers' ? 'selected' : ''; ?>>Dedicated Servers</option>
                        <option value="VPS Hosting" <?php echo $product['category'] == 'VPS Hosting' ? 'selected' : ''; ?>>VPS Hosting</option>
                        <option value="Domains" <?php echo $product['category'] == 'Domains' ? 'selected' : ''; ?>>Domains</option>
                        <option value="Other" <?php echo $product['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="wholesale_discount_percent" class="form-label">Wholesale Discount (%)</label>
                    <input type="number" step="0.01" class="form-control" id="wholesale_discount_percent" name="wholesale_discount_percent" value="<?php echo $product['wholesale_discount_percent']; ?>">
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price_monthly" class="form-label">Monthly Price (NGN)</label>
                    <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly" value="<?php echo $product['price_monthly']; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price_annually" class="form-label">Annually Price (NGN)</label>
                    <input type="number" step="0.01" class="form-control" id="price_annually" name="price_annually" value="<?php echo $product['price_annually']; ?>">
                </div>
            </div>

            <hr class="my-4">
            <h5>Provisioning Settings</h5>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="module" class="form-label">Module</label>
                     <select class="form-select" id="module" name="module">
                        <option value="" <?php echo empty($product['module']) ? 'selected' : ''; ?>>None</option>
                        <option value="Cpanel" <?php echo $product['module'] == 'Cpanel' ? 'selected' : ''; ?>>cPanel/WHM</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="server_id" class="form-label">Server</label>
                     <select class="form-select" id="server_id" name="server_id">
                        <option value="">None</option>
                        <?php while($server = $servers->fetch_assoc()): ?>
                        <option value="<?php echo $server['id']; ?>" <?php echo $product['server_id'] == $server['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($server['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                 <div class="col-md-4 mb-3">
                    <label for="package_name" class="form-label">Package Name</label>
                    <input type="text" class="form-control" id="package_name" name="package_name" placeholder="e.g., starter_plan" value="<?php echo htmlspecialchars($product['package_name']); ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="products.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<?php
require_once 'footer.php';
?>
