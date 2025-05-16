<?php

require_once 'dbinfo.php';

$connection = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);
$db = mysqli_select_db($connection, $db_database);

if (isset($_POST['updatedata'])) {
    $id = $_POST['update_id'];

    $name = $_POST['name'];
    $date = $_POST['date'];
    $treatment = $_POST['treatment'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $query = "UPDATE payment SET name='$name', date='$date', treatment='$treatment', price='$price', status='$status' WHERE id='$id' ";
    $query_run = mysqli_query($connection, $query);

    if ($query_run) {
        echo '<script> alert("Data Updated"); </script>';
        header("Location:../receptionist_admin/recepPayment.php");
    } else {
        echo '<script> alert("Data not Updated"); </script>';
    }
}

?>