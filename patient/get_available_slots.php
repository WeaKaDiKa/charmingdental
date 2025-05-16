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

    // Determine if selected date is today
    $isToday = ($selectedDate === date('Y-m-d'));
    $now = strtotime(date('h:i A')); // Current time as timestamp

    while ($currentTime < $endTimeStamp) {
        $nextTime = $currentTime + ($duration * 60);

        if ($nextTime <= $endTimeStamp) {
            // Skip past time slots if the date is today
            if ($isToday && $nextTime <= $now) {
                $currentTime = $nextTime;
                continue;
            }

            // Format the time slot
            $fromTime = date("h:i A", $currentTime);
            $toTime = date("h:i A", $nextTime);
            $timeSlot = "$fromTime - $toTime";

            $isUnavailable = false;

            foreach ($unavailableSlots as $unavailableSlot) {
                list($unavailableFrom, $unavailableTo) = separateTimeSlot($unavailableSlot);
                if ($unavailableFrom && $unavailableTo) {
                    $unavailableFromTime = strtotime($unavailableFrom);
                    $unavailableToTime = strtotime($unavailableTo);

                    if (
                        ($currentTime >= $unavailableFromTime && $currentTime < $unavailableToTime) ||
                        ($nextTime > $unavailableFromTime && $nextTime <= $unavailableToTime)
                    ) {
                        $isUnavailable = true;
                        break;
                    }
                }
            }

            if (!$isUnavailable) {
                $slots[] = $timeSlot;
            }
        }

        $currentTime = $nextTime;
    }

    return $slots;
}
/*
function getAvailableTimeSlots($startTime, $endTime, $duration, $unavailableSlots, $db, $date)
{
    $slots = [];
    $currentTime = strtotime($startTime);
    $endTimeStamp = strtotime($endTime);

    while ($currentTime < $endTimeStamp) {
        $nextTime = $currentTime + ($duration * 60);
        if ($nextTime <= $endTimeStamp) {
            // Format time slot
            $fromTime = date("h:i A", $currentTime);
            $toTime = date("h:i A", $nextTime);
            $timeSlot = "$fromTime - $toTime";

            $isUnavailable = false;

            // Check if the slot overlaps with any unavailable slots
            foreach ($unavailableSlots as $unavailableSlot) {
                list($unavailableFrom, $unavailableTo) = separateTimeSlot($unavailableSlot);

                if ($unavailableFrom && $unavailableTo) {
                    $unavailableFromTime = strtotime($unavailableFrom);
                    $unavailableToTime = strtotime($unavailableTo);

                    // Check if available slot overlaps with unavailable slot
                    if (
                        ($currentTime >= $unavailableFromTime && $currentTime < $unavailableToTime) ||
                        ($nextTime > $unavailableFromTime && $nextTime <= $unavailableToTime)
                    ) {
                        $isUnavailable = true;
                        break;
                    }
                }
            }

            // Add slot only if it's not unavailable
            if (!$isUnavailable) {
                $slots[] = $timeSlot;
            }
        }

        $currentTime = $nextTime;
    }
    return $slots;
}
*/
// Fetch unavailable slots from the database
function getUnavailableSlots($date, $db)
{
    $query = "SELECT appointment_time FROM appointments WHERE appointment_date = ? 
              UNION 
              SELECT appointment_time FROM approved_requests WHERE appointment_date = ?";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        die(json_encode(["error" => "SQL Error: " . $db->error]));
    }

    $stmt->bind_param("ss", $date, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $unavailableSlots = [];
    while ($row = $result->fetch_assoc()) {
        $unavailableSlots[] = $row['appointment_time'];
    }

    return $unavailableSlots;
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d'); // Get the selected date
    $startTime = "08:00 AM"; // Default start time
    $endTime = "05:00 PM"; // Default end time
    $duration = $_POST['duration'] ?? 30; // Default duration (30 mins)

    $unavailableSlots = getUnavailableSlots($date, $db);
    $availableSlots = getAvailableTimeSlots($startTime, $endTime, $duration, $unavailableSlots, $db, $date);


    header('Content-Type: application/json');
    echo json_encode($availableSlots, JSON_PRETTY_PRINT);
    exit;

}
