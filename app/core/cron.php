<?php
// app/core/cron.php
// This is the master cron script. It should be run periodically (e.g., every 5 minutes) by the server's cron daemon.

// Set a longer execution time as cron jobs can be long-running.
set_time_limit(300);

// Include the main bootstrap file to get access to the database and functions.
// We are in app/core, bootstrap is in the same directory.
require_once __DIR__ . '/bootstrap.php';

echo "Cron job runner started at " . date('Y-m-d H:i:s') . "\n";

// --- Fetch due cron jobs ---
$now = date('Y-m-d H:i:s');
$stmt = $db->prepare("SELECT * FROM cron_jobs WHERE next_run <= ? AND is_active = 1");
$stmt->bind_param("s", $now);
$stmt->execute();
$result = $stmt->get_result();
$due_jobs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($due_jobs)) {
    echo "No jobs are due to run.\n";
    exit;
}

echo "Found " . count($due_jobs) . " due job(s).\n";

// --- Execute each due job ---
foreach ($due_jobs as $job) {
    $task_file = __DIR__ . '/../tasks/' . $job['task_file'];

    echo "Running job: {$job['name']}...\n";

    if (file_exists($task_file)) {
        try {
            // Include the task file. The task file should contain the logic to be executed.
            // It will have access to the $db object.
            include $task_file;

            // Parse the schedule (e.g., "+1 day") into a format MySQL's DATE_ADD understands (e.g., "1 DAY")
            $schedule_parts = explode(' ', ltrim($job['schedule'], '+'));
            $interval_value = (int) $schedule_parts[0];
            $interval_unit = strtoupper($schedule_parts[1]);

            // Update the job's last_run and next_run times
            $update_stmt = $db->prepare("UPDATE cron_jobs SET last_run = ?, next_run = DATE_ADD(?, INTERVAL ? " . $interval_unit . ") WHERE id = ?");
            $update_stmt->bind_param("ssii", $now, $now, $interval_value, $job['id']);
            $update_stmt->execute();
            $update_stmt->close();

            echo "Job '{$job['name']}' completed successfully.\n";

        } catch (Exception $e) {
            echo "Error executing job '{$job['name']}': " . $e->getMessage() . "\n";
            // Optionally, you could add more robust error logging here, e.g., to a file or database table.
        }
    } else {
        echo "Task file not found for job '{$job['name']}': {$job['task_file']}\n";
    }
}

echo "Cron job runner finished at " . date('Y-m-d H:i:s') . "\n";
