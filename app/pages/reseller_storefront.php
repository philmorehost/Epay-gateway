<?php
// Reseller's Public Storefront
// This page is loaded by the router when a reseller's custom domain is accessed.

global $reseller_data; // This global is populated by reseller_bootstrap.php

// Set page title and branding from reseller settings
$company_name = htmlspecialchars($reseller_data['settings']['company_name'] ?? 'Our Store');
$page_title = $company_name . ' - Products';

include __DIR__ . '/../includes/reseller_storefront_header.php';
?>

<div class="text-center">
    <h1><?php echo $company_name; ?></h1>
    <p class="lead">Welcome! Check out the products and services we offer.</p>
</div>

<div class="row">
    <?php if (empty($reseller_data['products'])): ?>
        <div class="col">
            <p class="text-center">No products are available at this time.</p>
        </div>
    <?php else: ?>
        <?php foreach ($reseller_data['products'] as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($product['name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        <h4 class="card-title pricing-card-title text-center">
                            $<?php echo number_format($product['retail_price'], 2); ?>
                            <small class="text-muted fw-light">/mo</small>
                        </h4>
                    </div>
                    <div class="card-footer text-center">
                        <!-- The order link will work in context of the reseller's domain -->
                        <a href="/index.php?page=order&id=<?php echo $product['id']; ?>" class="btn btn-primary">Order Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
