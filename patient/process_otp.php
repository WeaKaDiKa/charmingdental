<?php
// Include database connection
require '../db/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $otp = $_POST['otp'];
    $email = $_SESSION['email'];

    // Check if the OTP matches
    $sql = "SELECT otp FROM users WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $stmt->bind_result($storedOTP);
            if ($stmt->fetch() && $otp == $storedOTP) {
                echo "OTP confirmed.";
              
            } else {
                echo "Invalid OTP.";
            }
            $stmt->close();
        } else {
            echo "Error executing query: " . $stmt->error;
        }
    } else {
        echo "Error preparing the statement: " . $db->error;
    }
}
?>
