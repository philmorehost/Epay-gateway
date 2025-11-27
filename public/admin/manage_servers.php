<?php
require_once '../../app/core/bootstrap.php';

// Authenticate and authorize the admin
require_permission('manage_servers');

$page_title = "Manage Dedicated Servers";
include_once 'templates/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Dedicated Servers</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Server/Customer</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Product/Service</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">IP Address</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $servers_result = $db->query("
                                    SELECT ds.id, ds.ip_address, ds.status, u.name AS user_name, p.name AS product_name
                                    FROM dedicated_servers ds
                                    JOIN orders o ON ds.order_id = o.id
                                    JOIN users u ON o.user_id = u.id
                                    JOIN products p ON o.product_id = p.id
                                    ORDER BY ds.created_at DESC
                                ");
                                if ($servers_result && $servers_result->num_rows > 0) {
                                    while ($server = $servers_result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($server['user_name']); ?></h6>
                                                <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($server['product_name']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($server['ip_address']); ?></p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-success"><?php echo htmlspecialchars(ucfirst($server['status'])); ?></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold"><?php echo htmlspecialchars($server['created_at'] ?? date('Y-m-d')); ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="javascript:;" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit server">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No dedicated servers found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once 'templates/footer.php';
?>
