<?php
require_once('../db/config.php');

// Validate and sanitize input
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Name = mysqli_real_escape_string($db, $_POST['PName']);
    $Date = mysqli_real_escape_string($db, $_POST['PDate']);
    $Treatment = mysqli_real_escape_string($db, $_POST['PTreatment']);
    $Price = mysqli_real_escape_string($db, $_POST['PPrice']);
    $Status = mysqli_real_escape_string($db, $_POST['PStatus']);

    // Use INSERT ... ON DUPLICATE KEY UPDATE to prevent duplicates
    $query = "INSERT INTO payment (name, date, treatment, price, status) 
              VALUES ('$Name', '$Date', '$Treatment', '$Price', '$Status') 
              ON DUPLICATE KEY UPDATE 
                  price = VALUES(price), 
                  status = VALUES(status)";

    if (mysqli_query($db, $query)) {
        if (mysqli_affected_rows($db) > 0) {
            echo "Record inserted/updated successfully!";
        } else {
            echo "Duplicate record detected, no changes made.";
        }
    } else {
        echo "Error: " . mysqli_error($db);
    }
}

//Get Particular Record
function get_record()
{
    global $db;
    $UserData = $_POST['UserData'];
    $query = " select * from payment where name='$UserData'";
    $result = mysqli_query($db, $query);

    while ($row = mysqli_fetch_assoc($result))
        ; {
        $user_data = "";
        $user_data[1] = $row['name'];
        $user_data[2] = $row['date'];
        $user_data[3] = $row['treatment'];
        $user_data[4] = $row['price'];
        $user_data[5] = $row['status'];
    }
    echo json_encode($user_data);
}

