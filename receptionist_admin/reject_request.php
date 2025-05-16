<?php
// Database connection
require_once "../db/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign POST data
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? null;
    $treatment = $_POST['treatment'] ?? null;
    $time = $_POST['time'] ?? null;
    $date = $_POST['date'] ?? null;
    $email = $_POST['email'] ?? null;
    $reason = $_POST['reason'] ?? null;

    // Validate input
    if (empty($id) || empty($name) || empty($treatment) || empty($time) || empty($date) || empty($email) || empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    mysqli_begin_transaction($db);

    try {
        $stmt = $db->prepare("SELECT dentist_name, username FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointment = $result->fetch_assoc();
        $stmt->close();

        if (!$appointment) {
            mysqli_rollback($db);
            echo json_encode(['status' => 'error', 'message' => 'No appointment found with the given ID.']);
            exit;
        }

        $dentist_name = $appointment['dentist_name'];
        $username = $appointment['username'];


        $stmt = $db->prepare("INSERT INTO rejected_requests (patient_id, patient_name, treatment, appointment_time, appointment_date, username, dentist_name, reason)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $id, $name, $treatment, $time, $date, $username, $dentist_name, $reason);

        if (!$stmt->execute()) {
            mysqli_rollback($db);
            echo json_encode(['status' => 'error', 'message' => 'Error inserting into rejected_requests: ' . $stmt->error]);
            exit;
        }
        $stmt->close();


        $stmt = $db->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            mysqli_rollback($db);
            echo json_encode(['status' => 'error', 'message' => 'Error deleting appointment: ' . $stmt->error]);
            exit;
        }
        $stmt->close();


        mysqli_commit($db);
        mysqli_close($db);

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'dmmy36381@gmail.com';
            $mail->Password = 'pxvwqphmsiwbdssd';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('dmmy36381@gmail.com', 'Charming Smile Dental Clinic');
            $mail->addAddress($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Appointment Rejection Notification';
            $mail->Body = "
            <h1>Appointment Update</h1>
            <p>Dear $name,</p>
            <p>We regret to inform you that we are unable to accommodate your requested appointment at this time. Reason: <strong>$reason</strong>.</p>
            <p>We sincerely apologize for any inconvenience this may cause. If you would like to reschedule or need further assistance, please don’t hesitate to contact us at 0915-123-4567 or email us at charmingsmiledc@gmail.com.</p>
            <p>Thank you for considering Charming Smile Dental Clinic for your care. We appreciate your understanding.</p>
            <p>Best regards,</p>
            <p>Charming Smile Dental Clinic</p>
        ";

            $mail->send();



        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
            exit;
        }
        echo json_encode(['status' => 'success', 'message' => 'Appointment rejected and email sent.']);

    } catch (Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['status' => 'error', 'message' => 'Database transaction failed.']);
        exit;
    }


} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>