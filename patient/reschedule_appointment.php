<?php
// updatecode.php

// Include your database connection file
include '../db/config.php';

if (isset($_POST['reschedule_data'])) {
    $id = $_POST['update_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $treatment = $_POST['treatment'];
    $status = $_POST['status'];

    // Prepare the SQL query
    $sql = "UPDATE approved_requests 
            SET appointment_date = ?, appointment_time = ?, treatment = ?, status = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $date, $time, $treatment, $status, $id);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>