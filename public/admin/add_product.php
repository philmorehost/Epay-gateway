<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token(); // CSRF check

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

        $stmt = $db->prepare("INSERT INTO products (name, description, category, price_monthly, price_annually, wholesale_discount_percent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssddd', $name, $description, $category, $price_monthly, $price_annually, $wholesale_discount_percent);

        if ($stmt->execute()) {
            $success_message = "Product added successfully! <a href='products.php'>View Products</a>";
        } else {
            $error_message = "Failed to add product: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<h1>Add New Product</h1>

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
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="Shared Hosting">Shared Hosting</option>
                        <option value="Dedicated Servers">Dedicated Servers</option>
                        <option value="VPS Hosting">VPS Hosting</option>
                        <option value="Domains">Domains</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="wholesale_discount_percent" class="form-label">Wholesale Discount (%)</label>
                    <input type="number" step="0.01" class="form-control" id="wholesale_discount_percent" name="wholesale_discount_percent" value="0.00">
                </div>
            </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price_monthly" class="form-label">Monthly Price (NGN)</label>
                    <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly" value="0.00">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price_annually" class="form-label">Annually Price (NGN)</label>
                    <input type="number" step="0.01" class="form-control" id="price_annually" name="price_annually" value="0.00" required>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="products.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>


<?php
require_once 'footer.php';
?>
