<?php
$page_title = 'Domain Name Search';
require_once '../app/includes/header.php';
require_once '../app/core/bootstrap.php';

$search_term = '';
$search_result = null;
$error_message = '';
$available = false;
$price = 0;

$api_key = $db->query("SELECT value FROM settings WHERE setting = 'connectreseller_api_key'")->fetch_assoc()['value'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['domain'])) {
    $search_term = trim(strtolower($_GET['domain']));

    if (empty($search_term)) {
        $error_message = "Please enter a domain name to search.";
    } elseif (empty($api_key)) {
        $error_message = "The domain checker is currently unavailable. Please contact support.";
    } else {
        $url = "https://api.connectreseller.com/ConnectReseller/ESHOP/checkdomainavailable?APIKey=" . urlencode($api_key) . "&websiteName=" . urlencode($search_term);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        if (isset($result['responseMsg']['statusCode']) && $result['responseMsg']['statusCode'] == 200) {
            $available = $result['responseData']['available'];
            if ($available) {
                // Fetch pricing from our local DB
                $tld = substr($search_term, strpos($search_term, '.') + 1);
                $price_stmt = $db->prepare("SELECT registration_price FROM tld_pricing WHERE tld = ? AND is_active = 1");
                $price_stmt->bind_param('s', $tld);
                $price_stmt->execute();
                $price_result = $price_stmt->get_result()->fetch_assoc();
                if ($price_result) {
                    $price = $price_result['registration_price'];
                } else {
                    $available = false; // Mark as unavailable if we don't sell this TLD
                    $error_message = "Sorry, we do not offer .{$tld} domains for registration at this time.";
                }
            }
        } else {
            $error_message = "Could not verify domain availability. The domain may be invalid or the registrar is offline.";
        }
    }
}
?>

<div class="text-center">
    <h1 class="display-5">Find Your Perfect Domain Name</h1>
    <p class="lead">Start your online journey with a unique domain. Search for your name, brand, or idea.</p>
</div>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="domain" class="form-control form-control-lg" placeholder="e.g., youridea.com" value="<?php echo htmlspecialchars($search_term); ?>" required>
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center mt-5">
    <div class="col-md-10">
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($search_term && !$error_message): ?>
            <div class="card">
                <div class="card-body">
                    <?php if ($available && $price > 0): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="text-success mb-0">Congratulations! <strong><?php echo htmlspecialchars($search_term); ?></strong> is available!</h4>
                            </div>
                            <div>
                                <span class="fs-4 me-3">NGN <?php echo number_format($price, 2); ?>/yr</span>
                                <a href="order_domain.php?domain=<?php echo htmlspecialchars($search_term); ?>" class="btn btn-primary btn-lg">Register Now</a>
                            </div>
                        </div>
                    <?php else: ?>
                         <h4 class="text-danger">Sorry, <strong><?php echo htmlspecialchars($search_term); ?></strong> is not available.</h4>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
