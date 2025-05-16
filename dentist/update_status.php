<?php
// Database connection
require_once 'dbinfo.php';

// Create connection
$conn = new mysqli($db_hostname, $db_username, $db_password, $db_database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' and 'status' are set in the POST request
if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Only proceed with payment processing if status is 'Completed'
        if ($status === 'completed') {
            // Fetch the specific approved request based on the ID
            $query = "SELECT id, patient_name, created_at, treatment 
                      FROM approved_requests 
                      WHERE id = ?";
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("No request found with ID: $id");
            }

            $row = $result->fetch_assoc();
            $patient_name = $row['patient_name'];
            $created_at = $row['created_at'];
            $treatment = $row['treatment'];

            // Check if the payment record already exists
            $check_payment_query = "SELECT id FROM payment 
                                  WHERE name = ? AND date = ? AND treatment = ?";
            $check_stmt = $conn->prepare($check_payment_query);

            if ($check_stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $check_stmt->bind_param("sss", $patient_name, $created_at, $treatment);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                throw new Exception("Payment record already exists for this request.");
            }

            // Insert into payment table
            $insert_query = "INSERT INTO payment (name, date, treatment, status) 
                           VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);

            if ($insert_stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $payment_status = "Paid";
            $insert_stmt->bind_param("ssss", $patient_name, $created_at, 
                                   $treatment, $payment_status);

            if (!$insert_stmt->execute()) {
                throw new Exception("Error inserting payment record: " . 
                                  $insert_stmt->error);
            }

            echo "Payment record added for patient: $patient_name, " . 
                 "Treatment: $treatment<br>";
        }

        // Update the status of the approved request
        $update_query = "UPDATE approved_requests SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);

        if ($update_stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $update_stmt->bind_param("si", $status, $id);

        if (!$update_stmt->execute()) {
            throw new Exception("Error updating record: " . $update_stmt->error);
        }

        echo "Status updated to $status successfully";

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        // Close statements
        if (isset($stmt)) $stmt->close();
        if (isset($check_stmt)) $check_stmt->close();
        if (isset($insert_stmt)) $insert_stmt->close();
        if (isset($update_stmt)) $update_stmt->close();
    }
} else {
    echo "Missing 'id' or 'status' in the request";
}

// Close connection
$conn->close();
?>