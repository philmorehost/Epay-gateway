<?php
// Public Products Page
// Note: bootstrap.php is included by the router (public/index.php)

// Fetch all products to display
$result = $db->query("SELECT * FROM products ORDER BY name ASC");
$products = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Our Products';
include __DIR__ . '/../includes/header.php';
?>

<div class="text-center">
    <h1>Our Products</h1>
    <p class="lead">Check out our available products and services.</p>
</div>

<div class="row">
    <?php if (empty($products)): ?>
        <div class="col">
            <p class="text-center">No products are available at this time.</p>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($product['name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        <ul class="list-unstyled">
                            <?php if ($product['price_monthly']): ?>
                                <li><strong>Monthly:</strong> $<?php echo htmlspecialchars($product['price_monthly']); ?></li>
                            <?php endif; ?>
                            <?php if ($product['price_annually']): ?>
                                <li><strong>Annually:</strong> $<?php echo htmlspecialchars($product['price_annually']); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/order.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Order Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
