<?php
// Database connection
require_once '../db/config.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $name = mysqli_real_escape_string($db, $_POST['name']);
    $date = mysqli_real_escape_string($db, $_POST['date']);
    $treatment = mysqli_real_escape_string($db, $_POST['treatment']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $status = mysqli_real_escape_string($db, $_POST['status']);
    
    // Validate required fields
    if (empty($name) || empty($date) || empty($treatment) || $price === false || empty($status)) {
        die(json_encode(['success' => false, 'message' => 'All fields are required and must be valid']));
    }
    
    // Insert the payment record
    $query = "INSERT INTO payment (name, date, treatment, price, status, created_at, archived) 
              VALUES (?, ?, ?, ?, ?, NOW(), 0)";
              
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sssds", $name, $date, $treatment, $price, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Payment added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding payment: ' . mysqli_error($db)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($db);
?>