<?php
require_once 'header.php';
require_once '../../app/core/bootstrap.php';

$message = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    verify_csrf_token();

    $user_id = (int)$_POST['user_id'];
    $new_status = (int)$_POST['status'];

    if ($new_status === 2 || $new_status === 0) { // Can approve to 2 or deny back to 0

        $db->begin_transaction();
        try {
            // Update the user's reseller status
            $stmt = $db->prepare("UPDATE users SET reseller_status = ? WHERE id = ?");
            $stmt->bind_param('ii', $new_status, $user_id);
            $stmt->execute();

            // If a reseller is approved, create an entry in reseller_settings
            if ($new_status === 2) {
                $check_stmt = $db->prepare("SELECT reseller_id FROM reseller_settings WHERE reseller_id = ?");
                $check_stmt->bind_param('i', $user_id);
                $check_stmt->execute();
                if($check_stmt->get_result()->num_rows === 0) {
                     $insert_stmt = $db->prepare("INSERT INTO reseller_settings (reseller_id) VALUES (?)");
                     $insert_stmt->bind_param('i', $user_id);
                     $insert_stmt->execute();
                }
            }
            $db->commit();
            $message = "<div class='alert alert-success'>Reseller status updated successfully.</div>";
        } catch (Exception $e) {
            $db->rollback();
            $message = "<div class='alert alert-danger'>Failed to update reseller status.</div>";
        }
    }
}

// Fetch all users who are pending or approved resellers
$resellers_result = $db->query("SELECT id, email, first_name, last_name, reseller_status FROM users WHERE reseller_status IN (1, 2) ORDER BY reseller_status DESC, id ASC");

?>

<h1 class="mb-4">Manage Resellers</h1>
<?php echo $message; ?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Pending Applications</h5>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pending_count = 0;
                $resellers_result->data_seek(0);
                while($reseller = $resellers_result->fetch_assoc()):
                    if ($reseller['reseller_status'] == 1):
                        $pending_count++;
                ?>
                        <tr>
                            <td><?php echo $reseller['id']; ?></td>
                            <td><?php echo htmlspecialchars($reseller['first_name'] . ' ' . $reseller['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($reseller['email']); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $reseller['id']; ?>">
                                    <button type="submit" name="status" value="2" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="POST" class="d-inline">
                                     <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $reseller['id']; ?>">
                                    <button type="submit" name="status" value="0" class="btn btn-sm btn-danger">Deny</button>
                                </form>
                            </td>
                        </tr>
                <?php
                    endif;
                endwhile;
                if ($pending_count === 0):
                ?>
                     <tr><td colspan="4" class="text-center">No pending applications.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <hr class="my-5">

        <h5 class="card-title">Approved Resellers</h5>
        <table class="table table-striped table-hover">
             <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                 <?php
                $approved_count = 0;
                $resellers_result->data_seek(0);
                while($reseller = $resellers_result->fetch_assoc()):
                    if ($reseller['reseller_status'] == 2):
                        $approved_count++;
                ?>
                        <tr>
                            <td><?php echo $reseller['id']; ?></td>
                            <td><?php echo htmlspecialchars($reseller['first_name'] . ' ' . $reseller['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($reseller['email']); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-info">Manage</a>
                            </td>
                        </tr>
                <?php
                    endif;
                endwhile;
                if ($approved_count === 0):
                ?>
                     <tr><td colspan="4" class="text-center">No approved resellers.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$db->close();
require_once 'footer.php';
?>
