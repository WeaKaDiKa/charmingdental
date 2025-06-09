<?php
require '../db/config.php';

function separateTimeSlot($timeSlot)
{
    $times = explode(" - ", $timeSlot);
    if (count($times) == 2) {
        return [trim($times[0]), trim($times[1])];
    }
    return [null, null];
}
function getAvailableTimeSlots($startTime, $endTime, $duration, $unavailableSlots, $db, $selectedDate)
{
    $slots = [];
    $currentTime = strtotime($startTime);
    $endTimeStamp = strtotime($endTime);

    $step = 30 * 60; // Always move by 30 minutes

    $isToday = ($selectedDate === date('Y-m-d'));
    $now = strtotime(date('h:i A'));

    while ($currentTime + ($duration * 60) <= $endTimeStamp) {
        $nextTime = $currentTime + ($duration * 60);

        if ($isToday && $nextTime <= $now) {
            $currentTime += $step;
            continue;
        }

        $isUnavailable = false;
        foreach ($unavailableSlots as $unavailableSlot) {
            list($unavailableFrom, $unavailableTo) = separateTimeSlot($unavailableSlot);
            if ($unavailableFrom && $unavailableTo) {
                $unavailableFromTime = strtotime($unavailableFrom);
                $unavailableToTime = strtotime($unavailableTo);

                if (
                    ($currentTime >= $unavailableFromTime && $currentTime < $unavailableToTime) ||
                    ($nextTime > $unavailableFromTime && $nextTime <= $unavailableToTime) ||
                    ($currentTime <= $unavailableFromTime && $nextTime >= $unavailableToTime)
                ) {
                    $isUnavailable = true;
                    break;
                }
            }
        }

        if (!$isUnavailable) {
            $fromTime = date("h:i A", $currentTime);
            $toTime = date("h:i A", $nextTime);
            $slots[] = "$fromTime - $toTime";
        }

        $currentTime += $step; // Always move by 30 minutes
    }

    return $slots;
}


// Fetch unavailable slots from the database
function getUnavailableSlots($date, $db)
{
    $query = "SELECT appointment_time_start, appointment_time_end 
          FROM appointments 
          WHERE appointment_date = ? 
          AND status NOT IN ('cancelled', 'rejected')";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        die(json_encode(["error" => "SQL Error: " . $db->error]));
    }

    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $unavailableSlots = [];
    while ($row = $result->fetch_assoc()) {
        $slot = $row['appointment_time_start'] . ' - ' . $row['appointment_time_end'];
        $unavailableSlots[] = $slot;
    }
    $unavailableSlots[] = '12:00 PM - 1:00 PM';
    return $unavailableSlots;
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $startTime = "08:00 AM";
    $endTime = "05:00 PM";
    $duration = $_POST['duration'] ?? 30;

    $unavailableSlots = getUnavailableSlots($date, $db);
    $availableSlots = getAvailableTimeSlots($startTime, $endTime, $duration, $unavailableSlots, $db, $date);


    header('Content-Type: application/json');
    echo json_encode($availableSlots, JSON_PRETTY_PRINT);
    exit;

}
