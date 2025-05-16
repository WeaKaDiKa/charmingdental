// get_appointments.php
<?php
require_once 'config.php';

$status = $_GET['status'] ?? 'upcoming';

$query = "SELECT * FROM approved_requests WHERE 1=1 ";

switch ($status) {
    case 'completed':
        $query .= "AND status = 'completed' ";
        break;
    case 'upcoming':
        $query .= "AND status = 'upcoming' AND appointment_date >= CURDATE() ";
        break;
    case 'rescheduled':
        $query .= "AND status = 'rescheduled' ";
        break;
    case 'cancelled':
        $query .= "AND status = 'cancelled' ";
        break;
    case 'followup':
        $query .= "AND status = 'followup' ";
        break;
}

$query .= "ORDER BY appointment_date ASC, appointment_time ASC";

try {
    $result = $db->query($query);
    $appointments = [];
    
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode($appointments);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}