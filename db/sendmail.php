<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailer-master/src/SMTP.php';

function sendmail($email, $name, $subject, $message)
{

    // Send email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'dmmy36381@gmail.com'; // Your email address
        $mail->Password = 'pxvwqphmsiwbdssd'; // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('dmmy36381@gmail.com', 'Charming Smile Dental Clinic');
        $mail->addAddress($email, $name); // Replace with the recipient's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "
            <h1>$subject</h1>
            <p>We look forward to seeing you and ensuring your dental health is in excellent condition.</p>
            <p>$message</p>
            <p>Best regards,</p>
            <p>Charming Smile Dental Clinic</p>
        ";

        $mail->send();
         json_encode(['status' => 'success', 'message' => 'Email sent successfully.']);
    } catch (Exception $e) {
         json_encode(['status' => 'error', 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo]);
    }
}