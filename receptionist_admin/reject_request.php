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
        $mail->addAddress($email, $patientName); // Replace with the patient's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Appointment Rejection Notification';
        $mail->Body = "
            <h1>Appointment Update</h1>
            <p>Dear $name,</p>
            <p>We regret to inform you that we are unable to accommodate your requested appointment at this time. Unfortunately, due to [reason, e.g., scheduling conflicts, unavailability], we are unable to proceed with your booking.</p>
            <p>We sincerely apologize for any inconvenience this may cause. If you would like to reschedule or need further assistance, please don’t hesitate to contact us at 0915-123-4567 or email us at charmingsmiledc@gmail.com.</p>
            <p>Thank you for considering Charming Smile Dental Clinic for your care. We appreciate your understanding.</p>
            <p>Best regards,</p>
            <p>Charming Smile Dental Clinic</p>
        ";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Appointment rejected and email sent.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $treatment = $_POST['treatment'];
    $time = $_POST['time'];
    $date = $_POST['date'];
    $reason = $_POST['reason'];

    // Start a transaction
    mysqli_begin_transaction($db);

    try {
        // Fetch patient_id, username, and dentist_name from appointments
        $usernameQuery = "SELECT patient_id, dentist_name, username FROM appointments WHERE appointment_id = ?";
        $usernameStmt = $db->prepare($usernameQuery);
        $usernameStmt->bind_param("i", $id);
        $usernameStmt->execute();
        $usernameResult = $usernameStmt->get_result();

        $row = $usernameResult->fetch_assoc();
        if ($row) {
            $patient_id = $row['patient_id'];  // Fetch patient_id here
            $username = $row['username'];
            $dentist_name = $row['dentist_name'];
        } else {
            throw new Exception("No appointment found with the given ID.");
        }
        $usernameStmt->close();

        // Insert into rejected_requests with correct patient_id
        $insertQuery = "INSERT INTO rejected_requests (patient_id, patient_name, treatment, appointment_time, appointment_date, username, dentist_name, reason)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bind_param("isssssss", $patient_id, $name, $treatment, $time, $date, $username, $dentist_name, $reason);

        if (!$insertStmt->execute()) {
            throw new Exception("Error inserting data: " . $insertStmt->error);
        }
        $insertStmt->close();

        // Delete the appointment from the appointments table
        $deleteQuery = "DELETE FROM appointments WHERE appointment_id = ?";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $id);

        if (!$deleteStmt->execute()) {
            throw new Exception("Error deleting data: " . $deleteStmt->error);
        }
        $deleteStmt->close();

        // Commit the transaction
        mysqli_commit($db);

} catch (Exception $e) {
    // Roll back the transaction on failure
    mysqli_rollback($db);
    // Optionally log or display error message here
}

mysqli_close($db);

}
?>