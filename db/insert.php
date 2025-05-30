<?php

//insert.php
require_once 'dbinfo.php';
$connect = new PDO('mysql:host=' . $db_hostname . ';dbname=' . $db_database, $db_username, $db_password);
if (isset($_POST["title"])) {
    $query = "
 INSERT INTO events 
 (title, start_event, end_event) 
 VALUES (:title, :start_event, :end_event)
 ";
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
            ':title' => $_POST['title'],
            ':start_event' => $_POST['start'],
            ':end_event' => $_POST['end']
        )
    );
}


?>