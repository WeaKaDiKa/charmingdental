<?php
require_once('../db/db_patient_appointments.php');

// Comprehensive session check
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header('location: patLogin.php');
    exit();
}

$firstName = $_SESSION['first_name'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$username = $_SESSION['username'];

// Improved database connection with error handling
require_once '../db/dbinfo.php';

function generateotp()
{
    $otp = "";
    for ($i = 0; $i < 6; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}
// Connect to the database
try {
    $db = new mysqli($db_hostname, $db_username, $db_password, $db_database);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
} catch (Exception $e) {
    // Log the error and show user-friendly message
    error_log($e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}
// Fetch all upcoming appointments with sorting (latest first)
$upcomingQuery = "SELECT * 
                 FROM approved_requests 
                 WHERE username = ? 
                 AND status != 'completed' 
                 AND status != 'Completed'
                 AND status != 'cancelled'
                 ORDER BY STR_TO_DATE(created_at, '%Y-%m-%d') DESC, 
                          STR_TO_DATE(created_at, '%Y-%m-%d %H:%i:%s') DESC";

try {
    $upcomingStmt = $db->prepare($upcomingQuery);
    if (!$upcomingStmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }

    $upcomingStmt->bind_param("s", $username);
    if (!$upcomingStmt->execute()) {
        throw new Exception("Execute failed: " . $upcomingStmt->error);
    }

    $upcomingResult = $upcomingStmt->get_result();
    $upcomingAppointments = $upcomingResult->fetch_all(MYSQLI_ASSOC);

    if (empty($upcomingAppointments)) {
        error_log("No upcoming appointments found for username: " . $username);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}



// Fetch all approval appointments with sorting (latest first)
$approvalQuery = "SELECT * 
                 FROM appointments 
                 WHERE username = ? 
                 AND status != 'completed' 
                 AND status != 'Completed'
                 AND status != 'cancelled'
                 ORDER BY STR_TO_DATE(created_at, '%Y-%m-%d') DESC, 
                          STR_TO_DATE(created_at, '%Y-%m-%d %H:%i:%s') DESC";

try {
    $previousStmt = $db->prepare($approvalQuery);
    if (!$previousStmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }

    $previousStmt->bind_param("s", $username);
    if (!$previousStmt->execute()) {
        throw new Exception("Execute failed: " . $previousStmt->error);
    }

    $previousResult = $previousStmt->get_result();
    $previousAppointments = $previousResult->fetch_all(MYSQLI_ASSOC);

    if (empty($previousAppointments)) {
        error_log("No previous appointments found for username: " . $username);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

// Close statements and connection
$upcomingStmt->close();
$previousStmt->close();
$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otpresend'])) {
    $otp = generateOTP();
    $id = $_SESSION['id'];

    // Update OTP in the database
    $sql = "UPDATE users SET otp = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $otp, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "OTP resend successful";
        }
        $stmt->close();
    }

    // Fetch user details for email
    $sql = "SELECT email, first_name, last_name FROM users WHERE id = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($email, $firstName, $lastName);
        $stmt->fetch();
        $stmt->close();
    }

    // Send email
    require_once '../db/sendmail.php';
    $message = "Here is your OTP. Use it to activate your account after signing in. <strong>" . $otp . "</strong>";
    sendmail($email, $firstName . " " . $lastName, "Confirm your email", $message);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otpsubmit'])) {
    $inputOTP = $_POST['otp'];
    $id = $_SESSION['id'];

    // Database query to fetch the stored OTP
    $sql = "SELECT otp FROM users WHERE id = ?";

    // Prepare and bind
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);

        // Execute the query
        if ($stmt->execute()) {
            // Bind the result
            $stmt->bind_result($storedOTP);
            if ($stmt->fetch()) {
                // Check if the input OTP matches the stored OTP
                if ($inputOTP == $storedOTP) {
                    // Close the statement before running another query
                    $stmt->close();

                    // OTP is valid, update user status to active
                    $updateSql = "UPDATE users SET status = 'active' WHERE id = ?";
                    $updateStmt = $db->prepare($updateSql);
                    if ($updateStmt) {
                        $updateStmt->bind_param("i", $id);
                        if ($updateStmt->execute()) {
                            $_SESSION['success'] = "OTP confirmed";
                            $_SESSION['status'] = "active";
                            echo "User status updated to active.";
                        } else {
                            echo "Error updating user status: " . $updateStmt->error;
                        }
                        $updateStmt->close();
                    } else {
                        echo "Error preparing the update statement: " . $db->error;
                    }
                } else {
                    echo "Invalid OTP.";
                    // Close the statement as the fetch is complete
                    $stmt->close();
                }
            } else {
                echo "User not found.";
                // Close the statement as the fetch is complete
                $stmt->close();
            }
        } else {
            echo "Error executing query: " . $stmt->error;
            // Close the statement as the fetch is complete
            $stmt->close();
        }
    } else {
        echo "Error preparing the statement: " . $db->error;
    }
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php require_once "../db/head.php" ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- For current_timezone -->
    <link rel="stylesheet" href="patDashboard.css">
    <link rel="stylesheet" href="main.css">
</head>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper">
        <?php
        $navactive = "patDashboard";

        require_once "../db/nav.php" ?>

        <div class="main-content">
            <div class="user-management">
                <div class="greeting">
                    <h1>Good day,<br>
                        <?php
                        if ($gender == 'Male') {
                            echo "Mr. " . htmlspecialchars($firstName);
                        } elseif ($gender == 'Female') {
                            echo "Ms. " . htmlspecialchars($firstName);
                        } else {
                            echo htmlspecialchars($firstName); // Fallback for other values
                        }
                        ?>
                    </h1>
                </div>
                <?php if (isset($_SESSION['status']) && $_SESSION['status'] == "active"): ?>
                    <div class="appointment-section">
                        <h3>APPROVED APPOINTMENTS</h3>
                        <?php if (!empty($upcomingAppointments)): ?>
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                                <div class="appointment-card">
                                    <div class="date-display">
                                        <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                    <div class="appointment-details">
                                        <div>
                                            <strong>Appointment No.</strong><br>
                                            <?php echo htmlspecialchars($appointment['patient_id']); ?>
                                        </div>
                                        <div>
                                            <strong>Treatment and Price (₱)</strong><br>
                                            <?php echo htmlspecialchars($appointment['treatment']); ?>
                                        </div>
                                        <div>
                                            <strong>Time</strong><br>
                                            <?php echo htmlspecialchars($appointment['appointment_time']); ?>
                                        </div>
                                        <div>
                                            <strong>Dentist</strong><br>
                                            <?php echo htmlspecialchars($appointment['dentist_name']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="appointment-card">
                                <div class="appointment-details">
                                    <p>No approved appointments.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="appointment-section">
                        <h3>FOR APPROVAL</h3>
                        <?php if (!empty($previousAppointments)): ?>
                            <?php foreach ($previousAppointments as $appointment): ?>
                                <div class="appointment-card">
                                    <div class="date-display">
                                        <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?>
                                    </div>
                                    <div class="appointment-details">
                                        <div>
                                            <strong>Appointment No.</strong><br>
                                            <?php echo htmlspecialchars($appointment['appointment_id']); ?>
                                        </div>
                                        <div>
                                            <strong>Treatment and Price (₱)</strong><br>
                                            <?php echo htmlspecialchars($appointment['service_name']); ?>
                                        </div>
                                        <div>
                                            <strong>Time</strong><br>
                                            <?php echo htmlspecialchars(string: $appointment['appointment_time']); ?>
                                        </div>
                                        <div>
                                            <strong>Dentist</strong><br>
                                            <?php echo htmlspecialchars($appointment['dentist_name']); ?>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button class="btn btn-secondary" data-bs-toggle="modal"
                                                data-bs-target="#rescheduleModal"
                                                data-appointmentid="<?php echo htmlspecialchars($appointment['appointment_id']); ?>"
                                                data-time="<?php echo htmlspecialchars($appointment['appointment_time']); ?>"
                                                data-date="<?= $appointment['appointment_date'] ?>">Reschedule</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="appointment-card">
                                <div class="appointment-details">
                                    <p>No approval appointments yet.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif (isset($_SESSION['status']) && $_SESSION['status'] == "inactive"): ?>

                    <div class="container">
                        <div class="card">

                            <div class="card-body">
                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
                                        <strong>Success</strong> <?= $_SESSION['success']; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>

                                    <?php unset($_SESSION['success']);
                                    ?>
                                    <?php

                                endif; ?>
                                <h5 class="text-center mb-5">Please activate your account by typing in your OTP sent to your
                                    email to proceed.</h5>


                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <form method="post">
                                        <div class="form-group d-flex flex-column">


                                            <label for="otp" class="text-center">
                                                OTP
                                            </label>
                                            <input type="text" name="otp" id="otp" required>


                                            <button type="submit" name="otpsubmit" class="btn btn-primary my-1">
                                                Confirm
                                            </button>
                                        </div>
                                    </form>
                                    <form method="post">
                                        <button type="submit" name="otpresend" class="btn btn-link my-1">
                                            Resend OTP
                                        </button>
                                    </form>

                                </div>

                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- <div class="payment-section">
                    <h3>PAYMENTS</h3>
                    <p>No pending payments.</p>
                </div> -->
            </div>
        </div>
    </div><!-- Reschedule Modal -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rescheduleForm" method="post">
                        <input type="hidden" name="appointment_id" id="appointment_id">
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">New Appointment Date</label>
                            <?php
                            $tomorrow = date('Y-m-d', strtotime('+1 day')); // Get tomorrow's date in YYYY-MM-DD format
                            ?>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date"
                                required min="<?= $tomorrow ?>">

                        </div>
                        <div class="mb-3">
                            <label for="appointment_time" class="form-label">New Appointment Time</label>
                            <select class="form-select" id="appointment_time" name="appointment_time" required></select>
                        </div>
                        <button type="submit" name="resched" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function getDuration(timeRange) {
            // Split the time range
            let [startTime, endTime] = timeRange.split(" - ");

            // Function to convert time format "hh:mm AM/PM" to minutes
            function timeToMinutes(time) {
                let [hours, minutes] = time.match(/\d+/g).map(Number);
                let period = time.includes("PM") ? "PM" : "AM";

                // Convert 12-hour format to 24-hour format
                if (period === "PM" && hours !== 12) {
                    hours += 12;
                } else if (period === "AM" && hours === 12) {
                    hours = 0;
                }

                return hours * 60 + minutes; // Total minutes
            }

            // Get minutes for start and end times
            let startMinutes = timeToMinutes(startTime);
            let endMinutes = timeToMinutes(endTime);

            // Return the duration in minutes
            return endMinutes - startMinutes;
        }

        document.addEventListener('DOMContentLoaded', function () {

            var rescheduleModal = document.getElementById("rescheduleModal");
            rescheduleModal.addEventListener("show.bs.modal", function (event) {
                var button = event.relatedTarget;
                var appointmentId = button.getAttribute("data-appointmentid");
                var appointmentDate = button.getAttribute("data-date");
                var appointmentTime = button.getAttribute("data-time");

                const startTime = '08:00'; // Using 24-hour format for clarity
                const endTime = '17:00'; // Using 24-hour format for clarity
                const duration = getDuration(appointmentTime); // Duration in minutes

                function getAvailableTimeSlots(startTime, endTime, duration) {
                    const slots = [];
                    let currentTime = new Date(`1970-01-01T${startTime}:00`);
                    const endTimeDate = new Date(`1970-01-01T${endTime}:00`);

                    while (currentTime < endTimeDate) {
                        let nextTime = new Date(currentTime.getTime() + duration * 60000);
                        if (nextTime <= endTimeDate) {
                            // Format the time slot
                            slots.push(`${currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${nextTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`);
                        }
                        currentTime = nextTime; // Move to the next time slot
                    }
                    return slots; // Return the array of available time slots
                }


                // Convert date to YYYY-MM-DD format
                var dateObj = new Date(appointmentDate);
                var formattedDate = dateObj.toISOString().split('T')[0]; // Ensures YYYY-MM-DD format

                document.getElementById("appointment_id").value = appointmentId;
                document.getElementById("appointment_date").value = formattedDate;

                var timeSelect = document.getElementById("appointment_time");

                timeSelect.innerHTML = ''; // Clear previous options
                const timeSlots = getAvailableTimeSlots(startTime, endTime, duration);
                timeSlots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = slot;

                    timeSelect.appendChild(option);
                    if (slot === appointmentTime) {
                        option.selected = true; // Set the current time slot as selected
                    }
                });

            });
        });
    </script>



    <div id="logoutConfirmDialog" class="logout-confirm-dialog" style="display: none;">
        <div class="logout-dialog-content">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to logout?</p>
            <div class="logout-dialog-buttons">
                <button onclick="logout()" class="btn-confirm">Yes, Logout</button>
                <button onclick="closeLogoutDialog()" class="btn-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function fetchCurrentTime() {
            $.ajax({
                url: '../db/current_timezone.php', // URL of the PHP script
                method: 'GET',
                success: function (data) {
                    $('#datetime').html(data); // Update the HTML with the fetched data
                },
                error: function () {
                    console.error('Error fetching time.');
                }
            });
        }

        // Fetch current time every second (1000 milliseconds)
        setInterval(fetchCurrentTime, 1000);

        // Initial call to display time immediately on page load
        fetchCurrentTime();


        document.addEventListener('DOMContentLoaded', function () {
            var dropdownButtons = document.querySelectorAll('.dropdown-btn');

            dropdownButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    // Toggle active class on the button
                    this.classList.toggle('active');

                    // Find the next sibling dropdown container
                    var dropdownContainer = this.nextElementSibling;

                    // Toggle dropdown visibility
                    if (dropdownContainer.style.display === 'block') {
                        dropdownContainer.style.display = 'none';
                    } else {
                        dropdownContainer.style.display = 'block';
                    }
                });
            });
        });

        // Add this new function for logout confirmation
        function confirmLogout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = 'logout.php';
            }
        }

        function showLogoutDialog() {
            document.getElementById('logoutConfirmDialog').style.display = 'block';
        }

        function closeLogoutDialog() {
            document.getElementById('logoutConfirmDialog').style.display = 'none';
        }

        function logout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>

</html>