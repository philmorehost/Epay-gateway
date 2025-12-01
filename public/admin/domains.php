<?php
require_once 'templates/header.php';

// Fetch all domains
$query = "SELECT d.id, d.domain_name, d.registrar, d.status, d.expires_at, u.name as client_name
          FROM domains d
          JOIN orders o ON d.order_id = o.id
          JOIN users u ON o.user_id = u.id
          ORDER BY d.created_at DESC";
$domains = $db->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<h1>All Registered Domains</h1>

<div class="card">
    <div class="card-header">Domain List</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Domain Name</th><th>Client</th><th>Registrar</th><th>Status</th><th>Expires At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $domain): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($domain['domain_name']); ?></td>
                        <td><?php echo htmlspecialchars($domain['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($domain['registrar']); ?></td>
                        <td><span class="badge bg-success"><?php echo htmlspecialchars($domain['status']); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($domain['expires_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
