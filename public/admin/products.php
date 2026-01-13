<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$products_result = $db->query("SELECT * FROM products ORDER BY name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Product Management</h1>
    <a href="add_product.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Product</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price (Annually)</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products_result->num_rows > 0): ?>
                    <?php while($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo 'NGN ' . number_format($product['price_annually'], 2); ?></td>
                            <td><?php echo $product['stock_control'] ? $product['stock_quantity'] : 'Unlimited'; ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-pencil-square"></i> Edit</a>
                                <!-- Add a delete button/modal here later -->
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'footer.php';
?>
