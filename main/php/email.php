<?php
require_once __DIR__ . '/../vendor/autoload.php';

function send_email($to, $subject, $body, $from = null, $fromName = null) {
    if (empty($to)) return false;

    $from = $from ?: getenv('SMTP_FROM');
    $fromName = $fromName ?: getenv('SMTP_FROM_NAME');

    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        error_log("PHPMailer not found. Run composer require phpmailer/phpmailer");
        return false;
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_USER'); // e.g., smtp.gmail.com
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = getenv('SMTP_SECURE') === 'ssl' 
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS 
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getenv('SMTP_PORT');

        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        return $mail->send();
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log("PHPMailer error: " . $e->getMessage());
        return false;
    }
}
?>
