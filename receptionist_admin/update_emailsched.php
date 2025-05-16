<?php
include '../db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $frequency = trim($_POST['frequency']);
    $message = trim($_POST['message']);
    $start = $_POST['start'];

    $stmt = $db->prepare("UPDATE emailsched SET frequency = ?, message = ?, start = ? WHERE id = ?");
    $stmt->bind_param("sssi", $frequency, $message, $start, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Record updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update record."]);
    }
}
?>