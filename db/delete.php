<?php

//delete.php
require_once 'dbinfo.php';

if (isset($_POST["id"])) {
    $connect = new PDO('mysql:host=' . $db_hostname . ';dbname=' . $db_database, $db_username, $db_password);
    $query = "
 DELETE from events WHERE id=:id
 ";
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
            ':id' => $_POST['id']
        )
    );
}

?>