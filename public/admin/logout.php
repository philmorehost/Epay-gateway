<?php
require_once '../../app/core/bootstrap.php';

unset($_SESSION['staff_id']);
unset($_SESSION['staff_permissions']);
unset($_SESSION['is_super_admin']);
// session_destroy(); // Avoid destroying the whole session if other parts of the app use it

header('Location: login.php');
exit;
