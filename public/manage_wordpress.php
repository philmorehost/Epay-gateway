<?php
$page_title = 'Manage WordPress';
require_once '../app/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../app/core/bootstrap.php';
require_once '../app/modules/Cpanel.php';

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null;
$message = '';
$plugins = [];
$themes = [];
$install_id = null;

if (!$order_id) {
    header('Location: index.php');
    exit;
}

$stmt = $db->prepare("SELECT o.domain, o.cpanel_username, s.hostname, s.api_key_1
                     FROM orders o
                     JOIN products p ON o.product_id = p.id
                     JOIN servers s ON p.server_id = s.id
                     WHERE o.id = ? AND o.user_id = ? AND o.status = 'Active'");
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service || empty($service['cpanel_username'])) {
    header('Location: index.php');
    exit;
}

$server_details = ['hostname' => $service['hostname'], 'api_key_1' => $service['api_key_1']];
$cpanel = new Cpanel($server_details);

// --- Find the WordPress Installation ID ---
$wp_list_result = $cpanel->uapi_query($service['cpanel_username'], 'WordPressManager', 'list_instances');
if (isset($wp_list_result['data'][0]['install_id'])) {
    $install_id = $wp_list_result['data'][0]['install_id'];
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $install_id) {
    verify_csrf_token();

    if (isset($_POST['action']) && $_POST['action'] === 'one_click_login') {
        $login_result = $cpanel->uapi_query($service['cpanel_username'], 'WordPressManager', 'create_user_session', ['install_id' => $install_id, 'user' => 'admin']);
        if (isset($login_result['data']['login_url'])) {
            header('Location: ' . $login_result['data']['login_url']);
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Failed to create WordPress session.</div>";
        }
    }
}

// --- Fetch Plugin & Theme data if installation is found ---
if ($install_id) {
    // Fetch plugins
    $plugin_result = $cpanel->wp_cli($service['cpanel_username'], $install_id, 'plugin list --format=json');
    if (isset($plugin_result['data']['stdout'])) {
        $plugins = json_decode(base64_decode($plugin_result['data']['stdout']), true);
    }

    // Fetch themes
    $theme_result = $cpanel->wp_cli($service['cpanel_username'], $install_id, 'theme list --format=json');
    if (isset($theme_result['data']['stdout'])) {
        $themes = json_decode(base64_decode($theme_result['data']['stdout']), true);
    }
}

?>

<h1 class="mb-4">WordPress Management for <span class="text-primary"><?php echo htmlspecialchars($service['domain']); ?></span></h1>
<?php if ($message) echo $message; ?>
<?php if (!$install_id) echo "<div class='alert alert-warning'>Could not find a WordPress installation for this account. Management features are disabled.</div>"; ?>

<div class="row">
    <!-- One-Click Login -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5><i class="bi bi-box-arrow-in-right"></i> One-Click Login</h5></div>
            <div class="card-body">
                <p>Securely log in to your WordPress admin dashboard.</p>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="one_click_login">
                    <button type="submit" class="btn btn-primary" <?php if(!$install_id) echo 'disabled'; ?>>Login to WP Dashboard</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ... Other components ... -->

    <!-- Plugin & Theme Management -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-plug"></i> Plugin & Theme Management</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Installed Plugins</h6>
                        <table class="table table-sm">
                            <?php if(!empty($plugins)): foreach ($plugins as $plugin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($plugin['name']); ?></td>
                                <td><span class="badge bg-<?php echo $plugin['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo $plugin['status']; ?></span></td>
                                <td><?php echo htmlspecialchars($plugin['version']); ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td class="text-muted">No plugins found or could not connect.</td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Installed Themes</h6>
                        <table class="table table-sm">
                             <?php if(!empty($themes)): foreach ($themes as $theme): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($theme['name']); ?></td>
                                <td><span class="badge bg-<?php echo $theme['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo $theme['status']; ?></span></td>
                                <td><?php echo htmlspecialchars($theme['version']); ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td class="text-muted">No themes found or could not connect.</td></tr>
                             <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$db->close();
require_once '../app/includes/footer.php';
?>
