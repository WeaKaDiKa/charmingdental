<?php
header('Content-Type: application/json');

// Database dbection
require_once '../db/config.php';


// Get data from the request
$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'];
$dentist = $data['dentist'];
$duration = $data['duration'];

// Query to fetch available time slots
$query = "
    SELECT start_time, end_time 
    FROM time_slots 
    WHERE slot_date = ? AND den_id = ? AND is_reserved = 0
";
$stmt = $db->prepare($query);
$stmt->bind_param('ss', $date, $dentist);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = $row['start_time'] . ' - ' . $row['end_time'];
}

if (!empty($slots)) {
    echo json_encode(['success' => true, 'slots' => $slots]);
} else {
    echo json_encode(['success' => false, 'message' => 'No available time slots']);
}

$stmt->close();
$db->close();
?>