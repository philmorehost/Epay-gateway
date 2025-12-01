<?php
require_once 'templates/header.php';

$error = $success = null;

// Handle adding a new customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_customer') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } else {
        try {
            // Check if email already exists for another reseller's customer
            $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "A customer with this email address already exists in the system.";
            } else {
                $stmt = $db->prepare("INSERT INTO customers (reseller_user_id, name, email) VALUES (?, ?, ?)");
                $stmt->bind_param('iss', $user_id, $name, $email);
                $stmt->execute();
                $success = "Customer added successfully.";
            }
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

// Fetch reseller's customers
$stmt = $db->prepare("SELECT * FROM customers WHERE reseller_user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<h1>My Customers</h1>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<!-- Add Customer Form -->
<div class="card mb-4">
    <div class="card-header">Add New Customer</div>
    <div class="card-body">
        <form action="customers.php" method="post">
            <input type="hidden" name="action" value="add_customer">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="name" placeholder="Full Name" required>
                </div>
                <div class="col-md-5">
                    <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Add Customer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customer List -->
<div class="card">
    <div class="card-header">Your Customer List</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Date Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="4" class="text-center">You have not added any customers yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <!-- Actions like Edit, Delete, or View Orders would go here -->
                                <a href="#" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
