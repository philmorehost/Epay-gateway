<?php
require_once '../app/core/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user's domains
$user_id = $_SESSION['user_id'];
$query = "SELECT d.domain_name, d.status, d.expires_at, d.registrar
          FROM domains d
          JOIN orders o ON d.order_id = o.id
          WHERE o.user_id = ?
          ORDER BY d.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$domains = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Domains - Client Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container py-5">
    <div class="installer-header text-center mb-4">
        <h1>My Domains</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Domain Name</th><th>Registrar</th><th>Status</th><th>Expires At</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($domains)): ?>
                        <tr><td colspan="5" class="text-center">You have no registered domains.</td></tr>
                    <?php else: ?>
                        <?php foreach ($domains as $domain): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($domain['domain_name']); ?></td>
                                <td><?php echo htmlspecialchars($domain['registrar']); ?></td>
                                <td><span class="badge bg-success"><?php echo htmlspecialchars($domain['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($domain['expires_at'])); ?></td>
                                <td>
                                    <a href="#" class="btn btn-primary btn-sm">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
     <div class="text-center mt-4">
        <a href="domain_checker.php" class="btn btn-success">Register a New Domain</a>
        <a href="index.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
