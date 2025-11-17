<?php
// Reseller Portal - Products
require_once __DIR__ . '/../../app/core/bootstrap.php';

// Check if user is logged in and is an approved reseller
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?page=login');
    exit;
}
$stmt = $db->prepare("SELECT is_reseller FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
if (!$user || $user['is_reseller'] != 1) {
    header('Location: /index.php?page=dashboard');
    exit;
}

// Fetch all products and calculate wholesale prices
$result = $db->query("SELECT *,
                             (price_monthly - (price_monthly * wholesale_discount_percent / 100)) as wholesale_monthly,
                             (price_annually - (price_annually * wholesale_discount_percent / 100)) as wholesale_annually
                      FROM products
                      ORDER BY name ASC");
$products = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Wholesale Products';
include __DIR__ . '/../../app/includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="/public/reseller/index.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="/public/reseller/products.php" class="list-group-item list-group-item-action active">Wholesale Products</a>
            <a href="/public/reseller/settings.php" class="list-group-item list-group-item-action">Settings</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h2>Wholesale Product Pricing</h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Your Monthly Cost</th>
                            <th>Your Annual Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($product['wholesale_monthly'], 2)); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($product['wholesale_annually'], 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../app/includes/footer.php';
