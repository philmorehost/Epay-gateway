<?php
require_once 'templates/header.php';

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

        if ($action === 'edit' && $product_id) {
            $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price_monthly = ?, price_annually = ?, category = ? WHERE id = ?");
            $stmt->bind_param('ssddsi', $name, $description, $price_monthly, $price_annually, $category, $product_id);
            $stmt->execute();
            $success = "Product updated successfully.";
        } elseif ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO products (name, description, price_monthly, price_annually, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdds', $name, $description, $price_monthly, $price_annually, $category);
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
?>
<div class="card mb-4">
    <div class="card-header"><?php echo $product_to_edit ? 'Edit Product' : 'Add New Product'; ?></div>
    <div class="card-body">
        <form action="products.php" method="post">
            <input type="hidden" name="action" value="<?php echo $product_to_edit ? 'edit' : 'add'; ?>">
            <?php if ($product_to_edit): ?>
                <input type="hidden" name="product_id" value="<?php echo $product_to_edit['id']; ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $product_to_edit['name'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"><?php echo $product_to_edit['description'] ?? ''; ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price_monthly" class="form-label">Monthly Price</label>
                    <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly" value="<?php echo $product_to_edit['price_monthly'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price_annually" class="form-label">Annual Price</label>
                    <input type="number" step="0.01" class="form-control" id="price_annually" name="price_annually" value="<?php echo $product_to_edit['price_annually'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control" id="category" name="category" value="<?php echo $product_to_edit['category'] ?? ''; ?>" required>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>$<?php echo number_format($product['price_monthly'], 2); ?></td>
                        <td>$<?php echo number_format($product['price_annually'], 2); ?></td>
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

<?php require_once 'templates/footer.php'; ?>
