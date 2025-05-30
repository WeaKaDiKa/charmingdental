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

// Check if user is logged in
if (!isset($_SESSION['id']) || !$_SESSION['id']) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}


// Fetch services from the database
$service_query = "SELECT id, name, duration, rate FROM services";
$service_result = $conn->query($service_query);
if (!$service_result) {
    die(json_encode(['success' => false, 'message' => "Error fetching services: " . $conn->error]));
}
$services = $service_result->fetch_all(MYSQLI_ASSOC);

// Fetch dentists from the database
$dentist_query = "SELECT dentist_id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS name FROM dentists";
$dentist_result = $conn->query($dentist_query);
if (!$dentist_result) {
    die(json_encode(['success' => false, 'message' => "Error fetching dentists: " . $conn->error]));
}
$dentists = $dentist_result->fetch_all(MYSQLI_ASSOC);

// Fetch user details
$userId = $_SESSION['id'];

$user_query = "SELECT first_name, middle_name, last_name, mobile, email, username FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => "Error preparing user query: " . $conn->error]));
}
$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    die(json_encode(['success' => false, 'message' => "Error executing user query: " . $stmt->error]));
}

$user_result = $stmt->get_result();
if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();

    $_SESSION['full_name'] = trim($user['first_name'] . ' ' . ($user['middle_name'] ?? '') . ' ' . $user['last_name']);
    $_SESSION['mobile'] = $user['mobile'];
    $_SESSION['email'] = $user['email'];  // Store email in session
    $_SESSION['username'] = $user['username'];

} else {
    echo json_encode(['success' => false, 'message' => "No user found with ID: " . $userId]);
    exit();
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['otpsubmit']) && !isset($_POST['otpresend']) && !isset($_POST['resched']) && !isset($_POST['reschedupcoming']) && !isset($_POST['cancel_appointment'])) {
    $required_fields = ['dentist', 'service', 'appointment_date', 'appointment_time'];
    $missing_fields = array_filter($required_fields, function ($field) {
        return empty($_POST[$field]);
    });

    if (empty($missing_fields)) {
        $patient_name = $_SESSION['full_name'] ?? '';
        $patient_contact = $_SESSION['mobile'] ?? '';
        $dentist_name = $_POST['dentist'] ?? '';
        $service_name = $_POST['service'] ?? '';
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $username = $_SESSION['username'] ?? '';
        $email = $_SESSION['email'] ?? '';
        $refnum = $_POST['refnum'] ?? '';
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, p_name, p_contact, dentist_name, service_name, appointment_date, appointment_time, username, email, transaction)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die(json_encode(['success' => false, 'message' => "Error preparing insert statement: " . $conn->error]));
        }
       $stmt->bind_param("isssssssss", $userId, $patient_name, $patient_contact, $dentist_name, $service_name, $appointment_date, $appointment_time, $username, $email, $refnum);

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

        // Medical Information
        $disease = trim($_POST['disease'] ?? '');
        $recent_surgery = trim($_POST['recent_surgery'] ?? '');
        $current_disease = trim($_POST['current_disease'] ?? '');
        $userid = $_SESSION['id'] ?? '';

        $sql = "SELECT disease, recent_surgery, current_disease FROM medical WHERE usersid = ? ORDER BY medid DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $latest_disease = $latest_recent_surgery = $latest_current_disease = '';
        if ($stmt) {
            $stmt->bind_param("i", $userid);
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
                    $stmt->bind_param("issss", $userid, $disease, $recent_surgery, $current_disease, $medcertlink);
                    $stmt->execute();
                    $stmt->close();
                }
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

        // Insert proofimg into downpayment
        if ($proofimg) {
            $sql = "INSERT INTO downpayment (proofimg, appointmentid) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $proofimg, $appointmentid);
                if ($stmt->execute()) {
                    echo "<script>alert('Downpayment proof uploaded successfully.');</script>";
                }
                $stmt->close();
            }
        }
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

