<?php

//load.php
function readevents($db_hostname, $db_database, $db_username, $db_password)
{
    $connect = new PDO('mysql:host=' . $db_hostname . ';dbname=' . $db_database, $db_username, $db_password);

    $data = [];

    $query = "SELECT id, title, start_event, end_event FROM events ORDER BY id";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    foreach ($result as $row) {
        $data[] = [
            'id' => $row["id"],
            'title' => $row["title"],
            'start' => $row["start_event"],
            'end' => $row["end_event"],
            'className' => "fc-event-task", // Default class for loaded events
            'resourceEditable' => true
        ];
    }

    return $data;
}
?>