<?php

require_once '../db/config.php';

$query = "SELECT id, name, date, treatment, price, status, created_at 
          FROM payment 
          WHERE archived = 1 
          ORDER BY created_at $order 
          LIMIT $limit OFFSET $offset";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Move payment to archived table (or mark it as archived)
    $query = "UPDATE payment SET archived = 1 WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update payment.']);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

mysqli_close($db);
?>