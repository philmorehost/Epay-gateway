<?php
// app/views/reseller_product_page.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($reseller_settings['company_name'] ?? 'Our Products'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .product-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .product-card-body {
            flex-grow: 1;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="installer-header text-center mb-5">
        <?php if (!empty($reseller_settings['logo_url'])): ?>
            <img src="<?php echo htmlspecialchars($reseller_settings['logo_url']); ?>" alt="<?php echo htmlspecialchars($reseller_settings['company_name']); ?>" style="max-height: 80px; margin-bottom: 20px;">
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($reseller_settings['company_name'] ?? 'Our Products & Services'); ?></h1>
    </div>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-card-body">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="fs-4 fw-bold">$<?php echo number_format($product['price_monthly'], 2); ?>/mo</p>
                    </div>
                    <a href="customer_order.php?id=<?php echo $product['id']; ?>" class="btn btn-primary w-100 mt-3">Order Now</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
        <a href="customer_login.php">Customer Login</a>
    </div>
</div>

</body>
</html>
