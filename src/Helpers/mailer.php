<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

function build_mailer(): PHPMailer
{
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = env('MAIL_HOST', 'localhost');
    $mailer->Port = (int) env('MAIL_PORT', '587');
    $mailer->SMTPAuth = true;
    $mailer->Username = env('MAIL_USERNAME', '');
    $mailer->Password = env('MAIL_PASSWORD', '');
    $encryption = env('MAIL_ENCRYPTION', 'tls');
    if ($encryption) {
        $mailer->SMTPSecure = $encryption;
    }
    $mailer->CharSet = 'utf-8';
    $mailer->setFrom(env('MAIL_FROM_ADDRESS', 'no-reply@example.com'), env('MAIL_FROM_NAME', 'Agenzia Plinio'));
    return $mailer;
}

function send_mail(string $to, string $subject, string $htmlBody, string $altBody = ''): bool
{
    try {
        $mailer = build_mailer();
        $mailer->addAddress($to);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $altBody ?: strip_tags($htmlBody);
        return $mailer->send();
    } catch (MailException $exception) {
        app_log('mail', 'Mail send failed', ['message' => $exception->getMessage()]);
        return false;
    }
}
