<?php

namespace MailService;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require './vendor/autoload.php';

define('USERNAME_EMAIL', 'phannguyentrunghau123456@gmail.com'); // Thay bằng email của bạn
define('PASSWORD_EMAIL', 'klpa nhsp qtsk pvfl'); // Thay bằng mật khẩu của bạn (hoặc mật khẩu ứng dụng)

class MailService
{
    public static function send($to = 'phannguyentrunghau123456@gmail.com', $from = 'phannguyentrunghau123456@gmail.com', $subject = 'Notification', $content = '')
    {
        try {
            $mail = new PHPMailer(true);  // Tạo đối tượng PHPMailer với exception handling

            // Set up SMTP
            $mail->isSMTP();  // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;  // Enable SMTP authentication
            $mail->Username = USERNAME_EMAIL;  // SMTP username
            $mail->Password = PASSWORD_EMAIL;  // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;  // TCP port to connect to
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($from, 'Sender Name');  // Địa chỉ người gửi và tên người gửi
            $mail->addAddress($to);  // Địa chỉ người nhận

            // Content
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Subject = $subject;  // Tiêu đề email
            $mail->Body    = $content;  // Nội dung email

            // Send email
            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
}

