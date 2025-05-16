<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Ensure no other output before JSON
ob_clean();
header('Content-Type: application/json');

try {
    // Get form data
    $name = $_POST['name'] ?? '';
    $treatment = $_POST['treatment'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $reason = $_POST['reason'] ?? '';

    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'don914gojocruz@gmail.com';
    $mail->Password = 'gmuo fiak zznj fxmx';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('your-clinic@example.com', 'Dental Clinic');
    $mail->addAddress('dmmy36381@gmail.com');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Appointment Request Update';
    $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; }
                .detail { margin: 10px 0; }
                .reason { 
                    background-color: #f8f8f8;
                    padding: 15px;
                    margin: 15px 0;
                    border-left: 4px solid #e74c3c;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Appointment Request Update</h2>
                <p>Dear {$name},</p>
                
                <p>We regret to inform you that your appointment request with the following details cannot be accommodated:</p>
                
                <div class='detail'><strong>Treatment:</strong> {$treatment}</div>
                <div class='detail'><strong>Date:</strong> {$date}</div>
                <div class='detail'><strong>Time:</strong> {$time}</div>
                
                <div class='reason'>
                    <strong>Reason for rejection:</strong><br>
                    {$reason}
                </div>
                
                <p>We encourage you to schedule another appointment at a different time that works better for you.</p>
                
                <p>If you have any questions or would like to schedule a new appointment, please don't hesitate to contact us.</p>
                
                <p>Thank you for your understanding.</p>
                <p>Best regards,<br>Charming Smile Dental Clinic</p>
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