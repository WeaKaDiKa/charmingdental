<?php

session_start();

// Database connection
require_once 'dbinfo.php';

// Create connection
$conn = new mysqli($db_hostname, $db_username, $db_password, $db_database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

// Fetch user details
$userId = $_SESSION['id'];

// Fetch services from the database
$service_query = "SELECT * FROM services";
$service_result = $conn->query($service_query);
if (!$service_result) {
    die(json_encode(['success' => false, 'message' => "Error fetching services: " . $conn->error]));
}
$services = $service_result->fetch_all(MYSQLI_ASSOC);

// Fetch dentists from the database
$dentist_query = "SELECT id as dentist_id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS name FROM users_employee WHERE usertype = 'dentist'";
$dentist_result = $conn->query($dentist_query);
if (!$dentist_result) {
    die(json_encode(['success' => false, 'message' => "Error fetching dentists: " . $conn->error]));
}
$dentists = $dentist_result->fetch_all(MYSQLI_ASSOC);


if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['otpsubmit']) && !isset($_POST['otpresend']) && !isset($_POST['resched']) && !isset($_POST['reschedupcoming']) && !isset($_POST['cancel_appointment'])) {
    $required_fields = ['dentist', 'service', 'appointment_date', 'appointment_time'];
    $missing_fields = array_filter($required_fields, function ($field) {
        return empty($_POST[$field]);
    });

    if (empty($missing_fields)) {

        $dentist_id = $_POST['dentist'] ?? '';
        $service_id = $_POST['service'] ?? '';
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';

        if (!empty($appointment_time)) {
            $timeRange = explode(' - ', $appointment_time); // Note the spaces around the dash

            if (count($timeRange) === 2) {
                $appointment_time_start = trim($timeRange[0]);
                $appointment_time_end = trim($timeRange[1]);
            }
        }
        // Downpayment Proof Upload
        $proofimg = null;
        if (isset($_FILES['proofimg']) && $_FILES['proofimg']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/payment/";
            $file_name = basename($_FILES["proofimg"]["name"]);
            $uniquefile = uniqid() . "_" . $file_name;
            $target_file = $target_dir . $uniquefile;

            if (move_uploaded_file($_FILES["proofimg"]["tmp_name"], $target_file)) {
                $proofimg = $uniquefile;
            }
        }
        $refnum = $_POST['refnum'] ?? '';
        $stmt = $conn->prepare("INSERT INTO appointments 
    (patient_id, dentist_id, service_id, appointment_date, appointment_time_start, appointment_time_end, transaction, proofimg)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            die(json_encode(['success' => false, 'message' => "Error preparing insert statement: " . $conn->error]));
        }

        $stmt->bind_param("iiisssss", $userId, $dentist_id, $service_id, $appointment_date, $appointment_time_start, $appointment_time_end, $refnum, $proofimg);

        if ($stmt->execute()) {
            $appointmentid = $stmt->insert_id;

            // Handle companions
            if (!empty($_POST['name']) && !empty($_POST['gender']) && !empty($_POST['age'])) {
                $companion_names = $_POST['name'];
                $companion_genders = $_POST['gender'];
                $companion_ages = $_POST['age'];

                for ($i = 0; $i < count($companion_names); $i++) {
                    if (!empty($companion_names[$i]) && !empty($companion_genders[$i]) && !empty($companion_ages[$i])) {
                        $stmt = $conn->prepare("INSERT INTO companion (name, gender, age, appointmentid) VALUES (?, ?, ?, ?)");
                        if (!$stmt) {
                            die(json_encode(['success' => false, 'message' => "Error preparing companion insert statement: " . $conn->error]));
                        }
                        $stmt->bind_param("ssii", $companion_names[$i], $companion_genders[$i], $companion_ages[$i], $appointmentid);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Appointment recorded successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error executing query: ' . $stmt->error]);
        }

        $stmt->close();

        /*    // Medical Information
           $disease = trim($_POST['disease'] ?? '');
           $disease = $disease === '' ? 'no' : $disease;

           $recent_surgery = trim($_POST['recent_surgery'] ?? '');
           $recent_surgery = $recent_surgery === '' ? 'no' : $recent_surgery;

           $current_disease = trim($_POST['current_disease'] ?? '');
           $current_disease = $current_disease === '' ? 'no' : $current_disease;


           $sql = "SELECT disease, recent_surgery, current_disease FROM medical WHERE usersid = ? ORDER BY medid DESC LIMIT 1";
           $stmt = $conn->prepare($sql);
           $latest_disease = $latest_recent_surgery = $latest_current_disease = '';
           if ($stmt) {
               $stmt->bind_param("i", $userId);
               $stmt->execute();
               $stmt->bind_result($latest_disease, $latest_recent_surgery, $latest_current_disease);
               $stmt->fetch();
               $stmt->close();
           }

           if (!empty($disease) || !empty($recent_surgery) || !empty($current_disease)) {
               if ($disease != $latest_disease || $recent_surgery != $latest_recent_surgery || $current_disease != $latest_current_disease) {
                   $medcertlink = null;
                   if (isset($_FILES['medical_certificate']) && $_FILES['medical_certificate']['error'] == UPLOAD_ERR_OK) {
                       $target_dir = "../uploads/";
                       $file_name = basename($_FILES["medical_certificate"]["name"]);
                       $uniquefile = uniqid() . "_" . $file_name;
                       $target_file = $target_dir . $uniquefile;

                       if (move_uploaded_file($_FILES["medical_certificate"]["tmp_name"], $target_file)) {
                           $medcertlink = $uniquefile;
                       }
                   }

                   $sql = "INSERT INTO medical (usersid, disease, recent_surgery, current_disease, medcertlink) VALUES (?, ?, ?, ?, ?)";
                   $stmt = $conn->prepare($sql);
                   if ($stmt) {
                       $stmt->bind_param("issss", $userId, $disease, $recent_surgery, $current_disease, $medcertlink);
                       $stmt->execute();
                       $stmt->close();
                   }
               }
           }
    */

    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
    }
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resched'])) {
    $appointmentId = $_POST['appointment_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];

    $sql = "UPDATE appointments SET appointment_date = ?, appointment_time = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssi", $appointmentDate, $appointmentTime, $appointmentId);
        if ($stmt->execute()) {
            echo "<script>alert('Appointment rescheduled successfully.'); window.location.href = 'patDashboard.php';</script>";
        } else {
            echo "<script>alert('Error updating appointment: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reschedupcoming'])) {
    $appointmentId = $_POST['appointment_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];

    $sql = "UPDATE approved_requests SET appointment_date = ?, appointment_time = ?, status = 'rescheduled' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssi", $appointmentDate, $appointmentTime, $appointmentId);
        if ($stmt->execute()) {
            echo "<script>alert('Appointment rescheduled successfully.'); window.location.href = 'patAppointments.php';</script>";
        } else {
            echo "<script>alert('Error updating appointment: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_appointment'])) {
    $appointmentId = $_POST['cancel_appointment_id'];

    $sql = "UPDATE approved_requests SET status = 'cancelled' WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $appointmentId);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Appointment cancelled successfully!";
        } else {
            $_SESSION['error'] = "Error updating status: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing the statement: " . $conn->error;
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

