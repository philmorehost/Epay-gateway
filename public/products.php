<?php
require_once '../app/core/bootstrap.php';

// Fetch products to display
$products = $db->query("SELECT * FROM products ORDER BY category, name")->fetch_all(MYSQLI_ASSOC);

// Fetch base currency for display
$currency_setting = $db->query("SELECT value FROM settings WHERE setting = 'base_currency'")->fetch_assoc();
$base_currency = $currency_setting['value'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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
        .product-title {
            font-weight: 700;
            color: #343a40;
        }
        .product-price {
            font-size: 1.75rem;
            font-weight: 700;
            color: #007bff;
        }
        .price-period {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="installer-header text-center mb-5">
        <h1>Our Products & Services</h1>
        <p>Choose the best plan for your needs.</p>
    </div>

    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">No products are available at this time.</div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="product-card">
                        <div class="product-card-body">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="product-price"><?php echo htmlspecialchars($base_currency); ?> <?php echo number_format($product['price_monthly'], 2); ?><span class="price-period">/mo</span></p>
                            <p>or <?php echo htmlspecialchars($base_currency); ?> <?php echo number_format($product['price_annually'], 2); ?> annually</p>
                        </div>
                        <a href="order_summary.php?id=<?php echo $product['id']; ?>" class="btn btn-primary w-100 mt-3">Order Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="text-center mt-4">
        <a href="login.php">Client Area Login</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
