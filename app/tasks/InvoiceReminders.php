<?php
// app/tasks/InvoiceReminders.php
// This task is included by the master cron.php script.

echo "Executing Invoice Reminders task...\n";

// --- Configuration ---
$reminder_days_before_due = 3; // Send reminder 3 days before the due date.
$reminder_email_template_name = 'Invoice Reminder'; // The name of the email template to use.

// --- Find the email template ---
$template_stmt = $db->prepare("SELECT * FROM email_templates WHERE name = ?");
$template_stmt->bind_param("s", $reminder_email_template_name);
$template_stmt->execute();
$template_result = $template_stmt->get_result();
$email_template = $template_result->fetch_assoc();
$template_stmt->close();

if (!$email_template) {
    echo "  - Warning: Email template '{$reminder_email_template_name}' not found. Skipping task.\n";
    return; // or throw new Exception(...)
}

// --- Find invoices that need reminders ---
$target_due_date = date('Y-m-d', strtotime("+{$reminder_days_before_due} days"));
$invoices_stmt = $db->prepare("
    SELECT i.id as invoice_id, i.amount, i.due_date, u.name as user_name, u.email as user_email
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    WHERE i.status = 'Unpaid' AND i.due_date = ?
");
$invoices_stmt->bind_param("s", $target_due_date);
$invoices_stmt->execute();
$invoices_result = $invoices_stmt->get_result();
$invoices_to_remind = $invoices_result->fetch_all(MYSQLI_ASSOC);
$invoices_stmt->close();

if (empty($invoices_to_remind)) {
    echo "  - No invoices due for reminders today.\n";
    return;
}

echo "  - Found " . count($invoices_to_remind) . " invoice(s) needing reminders.\n";

// --- Send the reminder emails ---
foreach ($invoices_to_remind as $invoice) {
    // Replace placeholders in the template
    $subject = str_replace('{invoice_id}', $invoice['invoice_id'], $email_template['subject']);
    $subject = str_replace('{name}', htmlspecialchars($invoice['user_name']), $subject);

    $body = str_replace('{invoice_id}', $invoice['invoice_id'], $email_template['body']);
    $body = str_replace('{name}', htmlspecialchars($invoice['user_name']), $body);
    $body = str_replace('{due_date}', date('F j, Y', strtotime($invoice['due_date'])), $body);
    $body = str_replace('{amount}', number_format($invoice['amount'], 2), $body);
    $body = str_replace('{invoice_link}', BASE_URL . '/index.php?page=view_invoice&id=' . $invoice['invoice_id'], $body);

    // Send the email
    if (send_email($invoice['user_email'], $subject, $body)) {
        echo "    - Reminder sent for invoice #{$invoice['invoice_id']} to {$invoice['user_email']}.\n";
    } else {
        echo "    - FAILED to send reminder for invoice #{$invoice['invoice_id']}.\n";
    }
}

echo "Invoice Reminders task finished.\n";
