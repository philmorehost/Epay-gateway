<?php
require_once '../app/core/bootstrap.php';

session_destroy();

header('Location: login.php');
exit;
