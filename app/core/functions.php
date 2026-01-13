<?php
// Core Functions Library

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/SMTP.php';

/**
 * Central function for sending emails.
 */
function send_email($db, $to, $subject, $body) {
    $settings_result = $db->query("SELECT * FROM settings WHERE setting LIKE 'smtp_%' OR setting = 'company_name'");
    $settings = [];
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting']] = $row['value'];
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'] ?? '';
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'] ?? '';
        $mail->Password = $settings['smtp_password'] ?? '';
        $mail->SMTPSecure = $settings['smtp_encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)($settings['smtp_port'] ?? 587);
        $from_name = $settings['company_name'] ?? 'Hostbill';
        $from_email = $settings['smtp_username'] ?? '';
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Generates a full URL for a given path.
 */
function site_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    return "{$protocol}://{$host}/{$path}";
}

/**
 * Generates and stores a CSRF token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a submitted CSRF token.
 */
function validate_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}

/**
 * Verifies the CSRF token from a POST request.
 */
function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            die('CSRF token validation failed. Request rejected.');
        }
    }
}
