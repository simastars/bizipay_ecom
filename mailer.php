<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $body, $altBody = '', $admin_email = '', $cc = [], $bcc = [], $replyTo = [])
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'simastartech@gmail.com';   // Your Gmail
        $mail->Password   = 'arsrztqbgdicnygg';         // Your Gmail App Password
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients (apply "From" and "Reply-To" like your headers)
        $fromEmail = $admin_email ?: 'simastartech@gmail.com';
        $mail->setFrom($fromEmail, 'Bizipay System');

        // If no reply-to passed, default to admin_email
        if (!empty($replyTo)) {
            foreach ($replyTo as $email => $name) {
                $mail->addReplyTo($email, $name);
            }
        } else {
            $mail->addReplyTo($fromEmail);
        }

        $mail->addAddress($to);

        // CC
        if (!empty($cc)) {
            foreach ($cc as $email) {
                $mail->addCC($email);
            }
        }

        // BCC
        if (!empty($bcc)) {
            foreach ($bcc as $email) {
                $mail->addBCC($email);
            }
        }

        // Custom headers like your `$headers`
        $mail->addCustomHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->CharSet = 'ISO-8859-1'; // same as your old Content-Type
        $mail->isHTML(true);           // ensures HTML content

        // Content
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
