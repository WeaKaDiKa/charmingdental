<?php
// fetch_email.php

require_once('../db/db_patient_appointments.php'); // Include your database connection file

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Prepare and execute the query to fetch the email
    $stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if ($email) {
        echo json_encode(['success' => true, 'email' => $email]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Username not provided']);
}
?>