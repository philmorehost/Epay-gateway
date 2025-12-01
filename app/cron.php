<?php
// app/cron.php

// This script should be executed by a server cron job, e.g., every 5 minutes.
// */5 * * * * php /path/to/your/project/app/cron.php

require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/cron_tasks.php';

echo "Cron job executed at " . date('Y-m-d H:i:s') . "\n";

// --- TASKS TO RUN ---

send_invoice_reminders();

// Example: Suspend overdue services (to be implemented later)
// suspend_overdue_services();

echo "Cron job finished.\n";
