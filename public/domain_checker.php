<?php
require_once '../app/core/bootstrap.php';
require_once '../app/modules/ConnectReseller.php';

$results = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
    $domain_input = trim($_POST['domain']);
    $tlds_to_check = [];
    $results = [];

    // Scenario 1: User enters a full domain like "example.com"
    if (strpos($domain_input, '.') !== false) {
        $tlds_to_check[] = $domain_input;
    }
    // Scenario 2: User enters just a name like "example"
    else {
        // Use a default list of TLDs if none are provided via checkboxes
        $default_tlds = ['com', 'net', 'org', 'io'];
        $selected_tlds = $_POST['tlds'] ?? $default_tlds;
        foreach ($selected_tlds as $tld) {
            $tlds_to_check[] = $domain_input . '.' . ltrim($tld, '.');
        }
    }

    if (!empty($tlds_to_check)) {
        try {
            $connect_reseller = new ConnectReseller();
            foreach ($tlds_to_check as $domain) {
                $availability = $connect_reseller->check_availability($domain);
                $results[$domain] = $availability;
            }
        } catch (Exception $e) {
            $error = "API Error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a domain name to search.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Name Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>Find Your Perfect Domain</h1>
    </div>

    <div class="card" style="max-width: 800px; margin: auto;">
        <div class="card-body">
            <form action="domain_checker.php" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="domain" class="form-control form-control-lg" placeholder="e.g., yourdomain">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="mb-3 text-center">
                    <label class="form-label">Select TLDs to check:</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="tlds[]" value="com" id="tld_com" checked>
                        <label class="form-check-label" for="tld_com">.com</label>
                    </div>
                     <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="tlds[]" value="net" id="tld_net">
                        <label class="form-check-label" for="tld_net">.net</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="tlds[]" value="org" id="tld_org">
                        <label class="form-check-label" for="tld_org">.org</label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger mt-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($results): ?>
        <div class="card mt-4" style="max-width: 800px; margin: auto;">
            <div class="card-header">Search Results</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($results as $domain => $status): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong><?php echo htmlspecialchars($domain); ?></strong>
                        <?php if ($status === 'available'): ?>
                            <span class="badge bg-success">Available</span>
                            <a href="order_summary.php?domain=<?php echo $domain; ?>" class="btn btn-sm btn-primary">Register</a>
                        <?php else: ?>
                            <span class="badge bg-danger">Unavailable</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
