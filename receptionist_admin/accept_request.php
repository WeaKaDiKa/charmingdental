<?php
// Database connection
require_once "../db/config.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Start a transaction
    mysqli_begin_transaction($db);

    try {
        // Start transaction
        mysqli_begin_transaction($db);

        // Update the appointment status to 'upcoming'
        $updateQuery = "UPDATE appointments SET status = 'upcoming' WHERE appointment_id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bind_param("i", $id);

        if (!$updateStmt->execute()) {
            throw new Exception("Error updating appointment status: " . $updateStmt->error);
        }

        $updateStmt->close();

        // Commit transaction
        mysqli_commit($db);
        echo json_encode(['status' => 'success', 'message' => 'accepted']);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($db);
      
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
