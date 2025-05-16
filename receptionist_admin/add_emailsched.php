<?php
include '../db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientid = intval($_POST['patientid']);
    $frequency = trim($_POST['frequency']);
    $message = trim($_POST['message']);
    $start = $_POST['start'];

    if (!$patientid || empty($frequency) || empty($message) || empty($start)) {
        echo json_encode(["success" => false, "message" => "All fields are required!"]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO emailsched (patientid, frequency, message, start) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $patientid, $frequency, $message, $start);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Schedule added successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add schedule."]);
    }
}
?>