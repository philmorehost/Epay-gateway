<?php
// Client Registration
// Note: bootstrap.php is included by the router (public/index.php)

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $tos = isset($_POST['tos']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!$tos) {
        $error = "You must accept the Terms of Service.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Hash the password and insert the new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                $success = "Registration successful! You can now log in.";

                // Send welcome email
                $template_stmt = $db->prepare("SELECT subject, body FROM email_templates WHERE name = 'Welcome Email'");
                $template_stmt->execute();
                $template_result = $template_stmt->get_result();
                $template = $template_result->fetch_assoc();
                $template_stmt->close();

                if ($template) {
                    $subject = str_replace('{name}', $name, $template['subject']);
                    $body = str_replace('{name}', $name, $template['body']);
                    $body = str_replace('{email}', $email, $body);

                    send_email($email, $subject, $body);
                }

            } else {
                $error = "An error occurred during registration. Please try again.";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}

$page_title = 'Register';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                Create an Account
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                <form action="/index.php?page=register" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="tos" name="tos">
                        <label class="form-check-label" for="tos">I accept the <a href="/tos.php" target="_blank">Terms of Service</a></label>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
