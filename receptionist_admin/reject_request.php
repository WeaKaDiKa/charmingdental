<?php
// Database connection
require_once "../db/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['id'];
    $patientName = $_POST['name'];
    $treatment = $_POST['treatment'];
    $appointmentTime = $_POST['time'];
    $appointmentDate = $_POST['date'];
    $email = $_POST['email'];
    $rejectionReason = $_POST['reason'];

    // Validate input
    if (empty($appointmentId) || empty($patientName) || empty($rejectionReason)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    // Start a transaction
    mysqli_begin_transaction($db);

    try {
        // Update the appointment status
        $updateQuery = "UPDATE appointments SET status = 'rejected', notes = ? WHERE appointment_id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bind_param("si", $rejectionReason, $appointmentId);

        if (!$updateStmt->execute()) {
            throw new Exception("Error updating appointment status: " . $updateStmt->error);
        }
        $updateStmt->close();

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dmmy36381@gmail.com';
        $mail->Password = 'pxvwqphmsiwbdssd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('dmmy36381@gmail.com', 'Charming Smile Dental Clinic');
        $mail->addAddress($email, $patientName);

        $mail->isHTML(true);
        $mail->Subject = 'Appointment Rejection Notification';
        $mail->Body = "
            <h1>Appointment Update</h1>
            <p>Dear $patientName,</p>
            <p>We regret to inform you that we are unable to accommodate your requested appointment at this time. Unfortunately, due to the following reason: <strong>$rejectionReason</strong>, we are unable to proceed with your booking.</p>
            <p>We sincerely apologize for any inconvenience this may cause. If you would like to reschedule or need further assistance, please don’t hesitate to contact us at 0915-123-4567 or email us at charmingsmiledc@gmail.com.</p>
            <p>Thank you for considering Charming Smile Dental Clinic for your care. We appreciate your understanding.</p>
            <p>Best regards,<br>Charming Smile Dental Clinic</p>
        ";

        $mail->send();

        // Commit transaction after successful email
        mysqli_commit($db);
        echo json_encode(['status' => 'success', 'message' => 'Appointment rejected and email sent.']);

    } catch (Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['status' => 'error', 'message' => 'Transaction failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
