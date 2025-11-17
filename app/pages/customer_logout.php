<?php
// app/pages/customer_logout.php

session_start();
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['reseller_id_for_customer']);

// Redirect to the reseller's storefront home page
header('Location: /index.php');
exit;
