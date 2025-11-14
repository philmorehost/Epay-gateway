<?php
require_once '../../app/core/bootstrap.php';

unset($_SESSION['admin_id']);
session_destroy();

header('Location: login.php');
exit;
