<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Настройки SMTP из .env
        if (setting('mail_driver', 'smtp') === 'smtp') {
            $this->mailer->isSMTP();
            $this->mailer->Host       = setting('mail_host', 'smtp.gmail.com');
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = setting('mail_username', '');
            $this->mailer->Password   = setting('mail_password', '');
            $this->mailer->SMTPSecure = setting('mail_encryption', PHPMailer::ENCRYPTION_STARTTLS);
            $this->mailer->Port       = (int) setting('mail_port', 587);
        }

        // От кого
        $this->mailer->setFrom(setting('mail_from_address', 'no-reply@example.com'), setting('mail_from_name', 'PHP Starter Kit'));
        
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->isHTML(true);
    }

    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags($body);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Ошибка отправки письма: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
