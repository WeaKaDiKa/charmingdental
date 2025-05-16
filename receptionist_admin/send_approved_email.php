<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailer-master/src/SMTP.php';

ob_clean();
header('Content-Type: application/json');

try {
    // Get form data
    $appointmentId = $_POST['appointmentId'] ?? '';
    $patientName = $_POST['patientName'] ?? '';
    $treatment = $_POST['treatment'] ?? '';
    $appointmentTime = $_POST['appointmentTime'] ?? '';
    $appointmentDate = $_POST['appointmentDate'] ?? '';
    $email = $_POST['email'] ?? '';

    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dmmy36381@gmail.com'; // Your email address
    $mail->Password = 'pxvwqphmsiwbdssd'; // Your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('dmmy36381@gmail.com', 'Charming Smile Dental Clinic');
    $mail->addAddress($email, $patientName); // Replace with the recipient's email

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Appointment Confirmed';
    $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; }
                .detail { margin: 10px 0; }
                .highlight { color: #007bff; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Your Appointment Has Been Confirmed!</h2>
                <div class='detail'><strong>Appointment ID:</strong> {$appointmentId}</div>
                <div class='detail'><strong>Patient Name:</strong> {$patientName}</div>
                <div class='detail'><strong>Treatment:</strong> {$treatment}</div>
                <div class='detail'><strong>Date:</strong> {$appointmentDate}</div>
                <div class='detail'><strong>Time:</strong> {$appointmentTime}</div>
                
                <div style='margin-top: 20px;'>
                    <p>Your appointment has been confirmed. Please remember:</p>
                    <ul>
                        <li>Arrive 15 minutes before your scheduled time</li>
                        <li>Bring any relevant medical records or x-rays</li>
                        <li>If you need to reschedule, please give at least 24 hours notice</li>
                    </ul>
                    
                    <p>For any questions or concerns, please don't hesitate to contact us.</p>
                    
                    <p>Thank you for choosing Charming Smile Dental Clinic!</p>
                </div>
            </div>
        </body>
        </html>
    ";

    $mail->send();
    die(json_encode(['success' => true]));
} catch (Exception $e) {
    die(json_encode([
        'success' => false,
        'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
    ]));
}
?>