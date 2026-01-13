<?php
// Hostbill Installer

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the steps
$steps = [
    1 => 'Welcome & Requirements Check',
    2 => 'Database Configuration',
    3 => 'Admin User Setup',
    4 => 'Finish Installation'
];

// Get the current step, default to 1
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if (!array_key_exists($current_step, $steps)) {
    $current_step = 1;
}

// Logic to prevent skipping steps
if ($current_step > 1 && empty($_SESSION['installer_step_completed'][$current_step - 1])) {
    header('Location: index.php?step=' . ($current_step - 1));
    exit;
}


// --- Main Page Structure ---

// Header
include 'header.php';

// Page content
echo '<div class="container mt-5">';
echo '<div class="row justify-content-center">';
echo '<div class="col-md-8">';
echo '<div class="card shadow-sm">';
echo '<div class="card-body p-5">';

echo '<h1 class="text-center mb-4">Hostbill Installation</h1>';

// Progress Bar
echo '<div class="progress mb-4" style="height: 25px;">';
foreach ($steps as $step => $title) {
    $status_class = ($step < $current_step) ? 'bg-success' : (($step == $current_step) ? 'bg-primary' : 'bg-light text-dark');
    $width = 100 / count($steps);
    echo "<div class=\"progress-bar {$status_class}\" role=\"progressbar\" style=\"width: {$width}%\" aria-valuenow=\"{$width}\" aria-valuemin=\"0\" aria-valuemax=\"100\">Step {$step}</div>";
}
echo '</div>';


// Load the current step's content
$step_file = "step_{$current_step}.php";
if (file_exists($step_file)) {
    include $step_file;
} else {
    echo "<div class='alert alert-danger'>Error: Step file not found: {$step_file}</div>";
}

echo '</div>'; // card-body
echo '</div>'; // card
echo '</div>'; // col
echo '</div>'; // row
echo '</div>'; // container

// Footer
include 'footer.php';
