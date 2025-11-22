<?php
// app/core/cron_tasks.php

function send_invoice_reminders() {
    global $db;

    echo "Running: Send Invoice Reminders...\n";

    // Define when to send reminders (e.g., 7 days before due date)
    $reminder_days = 7;
    $target_due_date = date('Y-m-d', strtotime("+$reminder_days days"));

    // 1. Fetch the email template
    $template_result = $db->query("SELECT * FROM email_templates WHERE name = 'Invoice Reminder'");
    $template = $template_result->fetch_assoc();
    if (!$template) {
        echo " - 'Invoice Reminder' email template not found. Skipping.\n";
        return;
    }

    // 2. Fetch unpaid invoices that are due soon
    $query = "SELECT
                i.id, i.amount, i.due_date,
                u.name as client_name, u.email as client_email
              FROM invoices i
              JOIN users u ON i.user_id = u.id
              WHERE i.status = 'Unpaid' AND i.due_date = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $target_due_date);
    $stmt->execute();
    $invoices_to_remind = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($invoices_to_remind)) {
        echo " - No invoices due for a reminder today.\n";
        return;
    }

    echo " - Found " . count($invoices_to_remind) . " invoices to remind.\n";

    // 3. Loop through and send emails
    foreach ($invoices_to_remind as $invoice) {
        $subject = str_replace('{invoice_id}', $invoice['id'], $template['subject']);

        $body = str_replace(
            ['{client_name}', '{invoice_id}', '{invoice_amount}', '{invoice_due_date}'],
            [htmlspecialchars($invoice['client_name']), $invoice['id'], number_format($invoice['amount'], 2), date('M j, Y', strtotime($invoice['due_date']))],
            $template['body']
        );

        if (send_email($invoice['client_email'], $subject, $body)) {
            echo "   - Reminder sent for Invoice #" . $invoice['id'] . " to " . $invoice['client_email'] . "\n";
        } else {
            echo "   - FAILED to send reminder for Invoice #" . $invoice['id'] . "\n";
        }
    }

    echo " - Finished sending reminders.\n";
}
