<?php
include '../db/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $db->prepare("DELETE FROM emailsched WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Record deleted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete record."]);
    }
}
?>