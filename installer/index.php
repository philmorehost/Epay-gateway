<?php
// Installer entry point
session_start();

$step = $_GET['step'] ?? 1;

switch ($step) {
    case 1:
        include 'steps/step1.php';
        break;
    case 2:
        include 'steps/step2.php';
        break;
    case 3:
        include 'steps/step3.php';
        break;
    case 4:
        include 'steps/step4.php';
        break;
    default:
        include 'steps/step1.php';
        break;
}
