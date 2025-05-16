<?php
// Include database connection
require '../db/config.php';

// Function to generate a 6-digit OTP
function generateOTP($length = 6)
{
    $otp = "";
    for ($i = 0; $i < $length; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    $_SESSION['email'] = $email;
    $otp = generateOTP();
    $success = false;
    // Update OTP in the database
    $sql = "UPDATE users SET otp = ? WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $otp, $email);
        if ($stmt->execute()) {
            $success = true;
            echo "OTP regenerated.";

        } else {
            echo "Error updating OTP: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing the statement: " . $db->error;
    }
    if ($success) {
        // Fetch user details for email
        $sql = "SELECT first_name, last_name FROM users WHERE email = ?";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($firstName, $lastName);
            $stmt->fetch();
            $stmt->close();
        } else {
            echo "Error fetching user details: " . $db->error;
            exit;
        }

        // Send email
        require_once '../db/sendmail.php';
        $message = "Here is your OTP. Use it to reset your account password. <strong>" . $otp . "</strong>";
        sendmail($email, $firstName . " " . $lastName, "Forgotten Password", $message);

    }
}

?>