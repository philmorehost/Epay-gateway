<?php
// Admin - Product Management
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = null;

// Handle product creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price_monthly = !empty($_POST['price_monthly']) ? $_POST['price_monthly'] : null;
    $price_annually = !empty($_POST['price_annually']) ? $_POST['price_annually'] : null;
    $wholesale_discount_percent = !empty($_POST['wholesale_discount_percent']) ? $_POST['wholesale_discount_percent'] : 0.00;
    $module = !empty($_POST['module']) ? $_POST['module'] : null;
    $server_id = !empty($_POST['server_id']) ? $_POST['server_id'] : null;
    $package_name = !empty($_POST['package_name']) ? $_POST['package_name'] : null;
    $install_wordpress = isset($_POST['install_wordpress']) ? 1 : 0;

    if (empty($name)) {
        $error = "Product name is required.";
    } else {
        $stmt = $db->prepare("INSERT INTO products (name, description, price_monthly, price_annually, wholesale_discount_percent, module, server_id, package_name, install_wordpress) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdddsisi", $name, $description, $price_monthly, $price_annually, $wholesale_discount_percent, $module, $server_id, $package_name, $install_wordpress);

        if ($stmt->execute()) {
            $success = "Product added successfully.";
        } else {
            $error = "Error adding product: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all products to display
$result = $db->query("SELECT * FROM products ORDER BY name ASC");
$products = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all servers for the dropdown
$servers = $db->query("SELECT id, name FROM servers ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);


$page_title = 'Manage Products';
include __DIR__ . '/../../app/includes/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Products</h1>
</div>

<div class="row">
    <!-- Add Product Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                Add New Product
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form action="products.php" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <hr>
                    <h5>Pricing</h5>
                    <div class="mb-3">
                        <label for="price_monthly" class="form-label">Monthly Price</label>
                        <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly">
                    </div>
                    <div class="mb-3">
                        <label for="price_annually" class="form-label">Annual Price</label>
                        <input type="number" step="0.01" class="form-control" id="price_annually" name="price_annually">
                    </div>
                    <div class="mb-3">
                        <label for="wholesale_discount_percent" class="form-label">Wholesale Discount (%)</label>
                        <input type="number" step="0.01" class="form-control" id="wholesale_discount_percent" name="wholesale_discount_percent" value="0.00">
                    </div>
                    <hr>
                    <h5>Provisioning</h5>
                     <div class="mb-3">
                        <label for="module" class="form-label">Module</label>
                        <select class="form-select" id="module" name="module">
                            <option value="">None</option>
                            <option value="Cpanel">cPanel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="server_id" class="form-label">Server</label>
                        <select class="form-select" id="server_id" name="server_id">
                            <option value="">Select a server...</option>
                            <?php foreach ($servers as $server): ?>
                                <option value="<?php echo $server['id']; ?>"><?php echo htmlspecialchars($server['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="package_name" class="form-label">Package Name</label>
                        <input type="text" class="form-control" id="package_name" name="package_name">
                        <small class="form-text text-muted">The name of the package/plan on the server (e.g., "starter_plan").</small>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="install_wordpress" name="install_wordpress">
                        <label class="form-check-label" for="install_wordpress">
                            Automatically Install WordPress
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Product List -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Existing Products
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Monthly Price</th>
                            <th>Annual Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No products found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['price_monthly']); ?></td>
                                    <td><?php echo htmlspecialchars($product['price_annually']); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
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

<?php
include __DIR__ . '/../../app/includes/footer.php';
