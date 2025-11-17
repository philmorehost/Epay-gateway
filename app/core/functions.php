<?php
// Core Functions

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/SMTP.php';

function send_email($to, $subject, $body) {
    global $db; // Use the global database connection from bootstrap.php

    // Fetch SMTP settings from the database
    $settings = [];
    $result = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'");
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = $settings['smtp_host'] ?? 'localhost';
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['smtp_user'] ?? '';
        $mail->Password   = $settings['smtp_pass'] ?? '';
        $mail->SMTPSecure = $settings['smtp_encryption'] ?? PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $settings['smtp_port'] ?? 587;

        //Recipients
        $mail->setFrom($settings['smtp_user'] ?? 'noreply@example.com', 'Billing System');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
