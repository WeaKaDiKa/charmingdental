<?php
// Database connection
require_once '../db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id']; // ID of the appointment to delete
    $date = $_POST['date'];
    $time = $_POST['time'];
    $name = $_POST['name'];
    $treatment = $_POST['treatment'];

    // Start a transaction to ensure both actions succeed or fail together
    mysqli_begin_transaction($db);

    try {
        // Insert data into the approved_requests table
        $insertQuery = "INSERT INTO completed_appointments (id, appointment_date, appointment_time, patient_name, treatment)
                        VALUES ('$id', '$date', '$time', '$name', '$treatment')";

        if (!mysqli_query($db, $insertQuery)) {
            throw new Exception("Error inserting data: " . mysqli_error($db));
        }

        // Delete the appointment from the appointments table
        $deleteQuery = "DELETE FROM approved_requests WHERE id = '$id'";

        if (!mysqli_query($db, $deleteQuery)) {
            throw new Exception("Error deleting data: " . mysqli_error($db));
        }

        // Commit the transaction
        mysqli_commit($db);

    } catch (Exception $e) {
        // Roll back the transaction on failure
        mysqli_roll_back($db);
        echo "Transaction failed: " . $e->getMessage();
    }

    mysqli_close($db);
}
?>
