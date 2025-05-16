<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailer-master/src/SMTP.php';

function sendmail($email, $message)
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
        $mail->addAddress($email, "Customer"); // Replace with the recipient's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Charming Smile Dental Clinic";
        $mail->Body = "
            <h1>Charming Smile Dental Clinic</h1>
           
            <p>$message</p>
        
            <p>Charming Smile Dental Clinic</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}