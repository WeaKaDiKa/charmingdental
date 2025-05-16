<?php
// Database connection
require_once "../db/config.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $treatment = $_POST['treatment'];
    $time = $_POST['time'];
    $date = $_POST['date'];

    // Start a transaction
    mysqli_begin_transaction($db);

    try {
        // First, get the username and dentist_name from the appointments table
        $usernameQuery = "SELECT dentist_name, username FROM appointments WHERE appointment_id = ?";
        $usernameStmt = $db->prepare($usernameQuery);
        $usernameStmt->bind_param("i", $id);
        $usernameStmt->execute();
        $usernameResult = $usernameStmt->get_result();
        
        // Fetch the result as an associative array
        $row = $usernameResult->fetch_assoc();
        if ($row) {
            $username = $row['username'];
            $dentist_name = $row['dentist_name'];
        } else {
            throw new Exception("No appointment found with the given ID.");
        }
        
        $usernameStmt->close();

        // Insert data into the approved_requests table, now including username and dentist_name
        $insertQuery = "INSERT INTO approved_requests (patient_id, patient_name, treatment, appointment_time, appointment_date, username, dentist_name)
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bind_param("issssss", $id, $name, $treatment, $time, $date, $username, $dentist_name);

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
    }

    mysqli_close($db);
}
?>