<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';   // SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'muhdmuhd158@gmail.com';   // Your Gmail
    $mail->Password   = 'kakh nuxn btbz ooix';      // Your Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('muhdmuhd158@gmail.com', 'Bizipay System');
    $mail->addAddress('mails4simastar@gmail.com');  // User email

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset';
    $mail->Body    = 'Click <a href="https://yourdomain.com/reset">here</a> to reset your password.';
    $mail->AltBody = 'Click the link to reset your password: https://yourdomain.com/reset';

    $mail->send();
    echo 'Password reset email has been sent.';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
   