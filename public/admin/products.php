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

    if (empty($name)) {
        $error = "Product name is required.";
    } else {
        $stmt = $db->prepare("INSERT INTO products (name, description, price_monthly, price_annually, wholesale_discount_percent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddd", $name, $description, $price_monthly, $price_annually, $wholesale_discount_percent);

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

$page_title = 'Manage Products';
include __DIR__ . '/../../app/includes/header.php';
?>

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
