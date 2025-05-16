<?php
// Include database connection
require '../db/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
    $newPassword = $_POST['password'];
    $email = $_SESSION['email'];

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $hashedPassword, $email);
        if ($stmt->execute()) {
            echo "Password updated successfully.";
        } else {
            echo "Error updating password: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing the statement: " . $db->error;
    }
}
?>