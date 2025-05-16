<?php

require_once '../db/config.php';


// Check for new "upcoming" appointment requests where notification has not been sent
$query = "SELECT id FROM approved_requests WHERE status = 'upcoming' AND notification_sent = 0";
$result = mysqli_query($db, $query);

if (mysqli_num_rows($result) > 0) {
    echo '1'; // New request found

    // Update the notification_sent field to 1 to prevent duplicate notifications
    $updateQuery = "UPDATE approved_requests SET notification_sent = 1 WHERE status = 'upcoming'";
    mysqli_query($db, $updateQuery);
} else {
    echo '0'; // No new requests
}

mysqli_close($db);
?>
