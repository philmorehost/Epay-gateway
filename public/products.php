<?php
$page_title = 'Our Products';
require_once '../app/includes/header.php';
require_once '../app/core/bootstrap.php';

// Fetch all non-hidden products
$products_result = $db->query("SELECT * FROM products WHERE hidden = 0 ORDER BY category, name ASC");

$products_by_category = [];
while ($product = $products_result->fetch_assoc()) {

    // --- Reseller Pricing Logic ---
    if (defined('IS_RESELLER_STOREFRONT') && IS_RESELLER_STOREFRONT === true) {
        $wholesale_discount = (float)$product['wholesale_discount_percent'];
        $reseller_markup = (float)$GLOBALS['reseller_data']['settings']['retail_markup_percent'];

        // Calculate wholesale price by applying the discount
        $wholesale_price = $product['price_annually'] * (1 - ($wholesale_discount / 100));

        // Calculate final retail price by applying the reseller's markup
        $retail_price = $wholesale_price * (1 + ($reseller_markup / 100));

        // Overwrite the price for the storefront view
        $product['price_annually'] = $retail_price;
    }
    // --- End Reseller Pricing Logic ---

    $products_by_category[$product['category']][] = $product;
}
?>

<h1 class="text-center mb-5">Our Products & Services</h1>

<?php if (empty($products_by_category)): ?>
    <div class="alert alert-info text-center">There are currently no products available. Please check back later.</div>
<?php else: ?>
    <?php foreach ($products_by_category as $category => $products): ?>
        <h2 class="mb-4"><?php echo htmlspecialchars($category); ?></h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-header">
                            <h4 class="my-0 fw-normal"><?php echo htmlspecialchars($product['name']); ?></h4>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="mt-auto">
                                <h1 class="card-title pricing-card-title">
                                    <?php if(defined('IS_RESELLER_STOREFRONT') && IS_RESELLER_STOREFRONT === true) echo 'USD '; else echo 'NGN '; ?>
                                    <?php echo number_format($product['price_annually'], 2); ?><small class="text-muted fw-light">/yr</small>
                                </h1>
                                <a href="order.php?pid=<?php echo $product['id']; ?>" class="w-100 btn btn-lg btn-primary">Order Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
