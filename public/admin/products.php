<?php
require_once 'templates/header.php';
require_permission('manage_products');

// Handle form submissions for add/edit/delete
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$product_id = $_POST['product_id'] ?? $_GET['id'] ?? null;
$error = $success = null;

try {
    if ($action === 'delete' && $product_id) {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $success = "Product deleted successfully.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price_monthly = $_POST['price_monthly'];
        $price_annually = $_POST['price_annually'];
        $category = $_POST['category'];
        $wholesale_discount_percent = $_POST['wholesale_discount_percent'];
        $server_type = $_POST['server_type'];
        $package_name = $_POST['package_name'];
        $product_type = $_POST['product_type'];
        $tld = $_POST['tld'];
        $server_identifier = $_POST['server_identifier'];

        if ($action === 'edit' && $product_id) {
            $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, product_type = ?, tld = ?, price_monthly = ?, price_annually = ?, category = ?, wholesale_discount_percent = ?, server_type = ?, package_name = ?, server_identifier = ? WHERE id = ?");
            $stmt->bind_param('ssssddsdsssi', $name, $description, $product_type, $tld, $price_monthly, $price_annually, $category, $wholesale_discount_percent, $server_type, $package_name, $server_identifier, $product_id);
            $stmt->execute();
            $success = "Product updated successfully.";
        } elseif ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO products (name, description, product_type, tld, price_monthly, price_annually, category, wholesale_discount_percent, server_type, package_name, server_identifier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssddsdsss', $name, $description, $product_type, $tld, $price_monthly, $price_annually, $category, $wholesale_discount_percent, $server_type, $package_name, $server_identifier);
            $stmt->execute();
            $success = "Product added successfully.";
        }
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}

// Fetch products to display
$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<h1>Product Management</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<!-- Add/Edit Form -->
<?php
$product_to_edit = null;
if ($action === 'edit' && $product_id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product_to_edit = $stmt->get_result()->fetch_assoc();
}

// Fetch base currency for display
$currency_setting = $db->query("SELECT value FROM settings WHERE setting = 'base_currency'")->fetch_assoc();
$base_currency = $currency_setting['value'] ?? '';
?>
<div class="card mb-4">
    <div class="card-header"><?php echo $product_to_edit ? 'Edit Product' : 'Add New Product'; ?></div>
    <div class="card-body">
        <form action="products.php" method="post">
            <input type="hidden" name="action" value="<?php echo $product_to_edit ? 'edit' : 'add'; ?>">
            <?php if ($product_to_edit): ?>
                <input type="hidden" name="product_id" value="<?php echo $product_to_edit['id']; ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $product_to_edit['name'] ?? ''; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="product_type" class="form-label">Product Type</label>
                    <select class="form-select" name="product_type">
                        <option value="hosting" <?php echo ($product_to_edit['product_type'] ?? '') === 'hosting' ? 'selected' : ''; ?>>Hosting</option>
                        <option value="domain" <?php echo ($product_to_edit['product_type'] ?? '') === 'domain' ? 'selected' : ''; ?>>Domain</option>
                    </select>
                </div>
            </div>
             <div class="mb-3">
                <label for="tld" class="form-label">TLD (for domains, e.g., .com)</label>
                <input type="text" class="form-control" id="tld" name="tld" value="<?php echo $product_to_edit['tld'] ?? ''; ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"><?php echo $product_to_edit['description'] ?? ''; ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price_monthly" class="form-label">Monthly Price (<?php echo $base_currency; ?>)</label>
                    <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly" value="<?php echo $product_to_edit['price_monthly'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price_annually" class="form-label">Annual Price (<?php echo $base_currency; ?>)</label>
                    <input type="number" step="0.01" class="form-control" id="price_annually" name="price_annually" value="<?php echo $product_to_edit['price_annually'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control" id="category" name="category" value="<?php echo $product_to_edit['category'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="wholesale_discount_percent" class="form-label">Wholesale Discount (%)</label>
                <input type="number" step="0.01" class="form-control" id="wholesale_discount_percent" name="wholesale_discount_percent" value="<?php echo $product_to_edit['wholesale_discount_percent'] ?? '0.00'; ?>" required>
            </div>
            <hr>
            <h5>Provisioning Settings</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="server_type" class="form-label">Server Type</label>
                    <select class="form-select" id="server_type" name="server_type">
                        <option value="">None</option>
                        <option value="cpanel" <?php echo ($product_to_edit['server_type'] ?? '') === 'cpanel' ? 'selected' : ''; ?>>cPanel/WHM</option>
                        <option value="nocix" <?php echo ($product_to_edit['server_type'] ?? '') === 'nocix' ? 'selected' : ''; ?>>NOCIX.net</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="package_name" class="form-label">Package Name (cPanel)</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="package_name" name="package_name" value="<?php echo $product_to_edit['package_name'] ?? ''; ?>">
                        <button class="btn btn-outline-secondary" type="button" id="fetch-packages-btn">Fetch Packages</button>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="server_identifier" class="form-label">Server Identifier (NOCIX)</label>
                <input type="text" class="form-control" id="server_identifier" name="server_identifier" value="<?php echo $product_to_edit['server_identifier'] ?? ''; ?>" placeholder="e.g., lox-101">
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $product_to_edit ? 'Update Product' : 'Add Product'; ?></button>
            <?php if ($product_to_edit): ?>
                <a href="products.php" class="btn btn-secondary">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>
</div>


<!-- Product List -->
<div class="card">
    <div class="card-header">Existing Products</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Monthly Price</th>
                    <th>Annual Price</th>
                    <th>Wholesale Discount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td><?php echo htmlspecialchars($base_currency); ?> <?php echo number_format($product['price_monthly'], 2); ?></td>
                        <td><?php echo htmlspecialchars($base_currency); ?> <?php echo number_format($product['price_annually'], 2); ?></td>
                        <td><?php echo number_format($product['wholesale_discount_percent'], 2); ?>%</td>
                        <td>
                            <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Packages Modal -->
<div class="modal fade" id="packagesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Available WHM Packages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="packages-list">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('fetch-packages-btn').addEventListener('click', function() {
    var myModal = new bootstrap.Modal(document.getElementById('packagesModal'));
    var packagesList = document.getElementById('packages-list');
    packagesList.innerHTML = 'Loading...';
    myModal.show();

    fetch('ajax_get_packages.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                packagesList.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                let list = '<ul class="list-group">';
                data.packages.forEach(pkg => {
                    list += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                ${pkg}
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectPackage('${pkg}')">Select</button>
                             </li>`;
                });
                list += '</ul>';
                packagesList.innerHTML = list;
            }
        });
});

function selectPackage(packageName) {
    document.getElementById('package_name').value = packageName;
    var myModal = bootstrap.Modal.getInstance(document.getElementById('packagesModal'));
    myModal.hide();
}
</script>

<?php require_once 'templates/footer.php'; ?>
