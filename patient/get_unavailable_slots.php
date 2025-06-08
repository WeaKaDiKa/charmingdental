<?php

require_once('../db/db_patient_appointments.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $date = $_POST['date'];
    $query = "SELECT appointment_time FROM appointments WHERE appointment_date = ? 
              UNION 
              SELECT appointment_time FROM approved_requests WHERE appointment_date = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $date, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $unavailableSlots = [];

    while ($row = $result->fetch_assoc()) {
        $unavailableSlots[] = $row['appointment_time'];
    }

    echo json_encode($unavailableSlots);

}

