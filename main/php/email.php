<?php
// email.php - SMTP-aware mail sender using PHPMailer if available

// Load Composer autoload
require_once __DIR__ . '/../../vendor/autoload.php'; // Adjusted for /var/www/html/main/php/email.php

/**
 * Send an email via SMTP (PHPMailer) or fallback to mail()
 *
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (plain text or HTML)
 * @param string|null $from Optional sender email
 * @param string|null $fromName Optional sender name
 * @return bool True if sent successfully
 */
function send_email($to, $subject, $body, $from = null, $fromName = null) {
    $to = (string)$to;
    if ($to === '') {
        return false;
    }

    // Use environment variables if not explicitly passed
    $from = $from ?: getenv('SMTP_FROM') ?: 'no-reply@meta-shark.local';
    $fromName = $fromName ?: getenv('SMTP_FROM_NAME') ?: 'Meta Shark';
    $smtpEnabled = getenv('SMTP_ENABLED') !== 'false'; // default true

    // Try PHPMailer if available and SMTP enabled
    if ($smtpEnabled && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_USER') ?: 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USERNAME');
            $mail->Password   = getenv('SMTP_PASSWORD');

            $secure = strtolower(getenv('SMTP_SECURE') ?: 'tls');
            if ($secure === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
            } else {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = getenv('SMTP_PORT') ?: 587;
            }

            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            // fallback to mail()
        }
    } elseif ($smtpEnabled) {
        error_log("PHPMailer class not found. Run `composer require phpmailer/phpmailer` in project root.");
    }

    // Fallback to PHP mail() if PHPMailer fails or is unavailable
    $headers = [
        'From: ' . $from,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8'
    ];

    return @mail($to, $subject, $body, implode("\r\n", $headers));
}
