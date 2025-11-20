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

// Fetch the product details
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    // Product not found, redirect
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    if (empty($_POST['name']) || empty($_POST['category']) || !isset($_POST['price_annually'])) {
        $error_message = "Please fill in all required fields.";
    } else {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $price_monthly = (float)($_POST['price_monthly'] ?? 0);
        $price_annually = (float)($_POST['price_annually'] ?? 0);
        $wholesale_discount_percent = (float)($_POST['wholesale_discount_percent'] ?? 0);

        $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, category = ?, price_monthly = ?, price_annually = ?, wholesale_discount_percent = ? WHERE id = ?");
        $stmt->bind_param('sssdddi', $name, $description, $category, $price_monthly, $price_annually, $wholesale_discount_percent, $product_id);

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
                    <input type="number" step="0.01" class="form-control" id="price_annually" name="price_annually" value="<?php echo $product['price_annually']; ?>" required>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="products.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<?php
require_once 'footer.php';
?>
