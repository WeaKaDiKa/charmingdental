<?php

require_once('../db/db_patient_appointments.php');
require_once '../db/config.php';

$username = $_SESSION['username']; // Use username if needed
$patient_id = $_SESSION['id'];     // <-- Fixed missing semicolon

// Fetch all appointments from the approved_requests table
$query = "SELECT id, patient_id, treatment, appointment_time, appointment_date, dentist_name, status
          FROM approved_requests
          WHERE username = ?";
$stmt = mysqli_prepare($db, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $appointments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing the statement: " . mysqli_error($db);
}

// Fetch all rejected appointments
$query_rejected = "SELECT id, patient_id, treatment, appointment_time, appointment_date, dentist_name, status, reason
                   FROM rejected_requests
                   WHERE username = ?";
$stmt_rejected = mysqli_prepare($db, $query_rejected);

if ($stmt_rejected) {
    mysqli_stmt_bind_param($stmt_rejected, "s", $username);
    mysqli_stmt_execute($stmt_rejected);
    $result_rejected = mysqli_stmt_get_result($stmt_rejected);

    while ($row = mysqli_fetch_assoc($result_rejected)) {
        $row['status'] = 'rejected';
        $appointments[] = $row;
    }

    mysqli_stmt_close($stmt_rejected);
} else {
    echo "Error preparing the statement for rejected requests: " . mysqli_error($db);
}

// Variables for rescheduling logic
$current_appointment_id = $_GET['appointment_id'] ?? null;

// Fetch approved upcoming appointments excluding current appointment if rescheduling
$approved_query = "SELECT appointment_date, appointment_time, id 
                   FROM approved_requests 
                   WHERE patient_id = ? 
                     AND status = 'approved' 
                     AND appointment_date >= CURDATE()";

if ($current_appointment_id !== null) {
    $approved_query .= " AND appointment_id != ?";
}

// Fetch submitted appointments excluding current appointment if rescheduling
$submitted_query = "SELECT appointment_date, appointment_time, appointment_id 
                    FROM appointments 
                    WHERE patient_id = ? 
                      AND appointment_date >= CURDATE()";

if ($current_appointment_id !== null) {
    $submitted_query .= " AND appointment_id != ?";
}

$booked_slots = [];

// Prepare and execute approved requests query
if ($stmt = $db->prepare($approved_query)) {
    if ($current_appointment_id !== null) {
        $stmt->bind_param("ii", $patient_id, $current_appointment_id);
    } else {
        $stmt->bind_param("i", $patient_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row['appointment_date'] . ' ' . $row['appointment_time'];
    }
    $stmt->close();
}

// Prepare and execute submitted appointments query
if ($stmt = $db->prepare($submitted_query)) {
    if ($current_appointment_id !== null) {
        $stmt->bind_param("ii", $patient_id, $current_appointment_id);
    } else {
        $stmt->bind_param("i", $patient_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row['appointment_date'] . ' ' . $row['appointment_time'];
    }
    $stmt->close();
}

// Close the database connection
mysqli_close($db);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Charming Smile Dental Clinic</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="patAppointments.css">
    <link rel="stylesheet" href="main.css">
</head>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <!-- Main Wrapper -->
    <div class="main-wrapper overflow-hidden">
        <!-- Sidebar -->
        <?php
        $navactive = "patAppointments";
        require_once "../db/nav.php" ?>
        <!-- Main Content -->
        <div class="main-content overflow-hidden">
            <div class="card">
                <div class="card-body">
                    <div class="appointments-header">
                        <h2>Appointment Lists</h2>
                    </div>
                    <div class="appointments-tabs overflow-x-scroll overflow-y-hidden" style="margin-bottom:10px;">

                        <button class="tab active" data-tab="upcoming">Upcoming</button>
                        <button class="tab" data-tab="rescheduled">Re-scheduled</button>
                        <button class="tab" data-tab="Completed">Completed</button>
                        <button class="tab" data-tab="cancelled">Cancelled</button>
                        <button class="tab" data-tab="rejected">Rejected</button>
                    </div>
                    <div class="overflow-x-scroll">

                        <table class="w-100">
                            <thead>
                                <tr>
                                    <th>Appointment No.</th>
                                    <th>Appointment Date</th>
                                    <th>Appointment Time</th>
                                    <th>Treatment and Price (₱)</th>
                                    <th>Dentist</th>
                                    <th class="action-column">Action</th>
                                    <th class="reason-column" style="display: none;">Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No Appointments</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <?php
                                        $appointmentDateTime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                        $currentDateTime = date('Y-m-d H:i:s');
                                        $appointmentEndTime = date('H:i:s', strtotime($appointment['appointment_time'] . ' +30 minutes'));
                                        $appointmentEndDateTime = $appointment['appointment_date'] . ' ' . $appointmentEndTime;

                                        $status = 'upcoming';

                                        if (strtotime($appointmentEndDateTime) < strtotime($currentDateTime)) {
                                            $status = 'completed';
                                        }

                                        if (!empty($appointment['status'])) {
                                            $status = strtolower($appointment['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?= $status ?>">
                                            <td><?= htmlspecialchars($appointment['id']) ?></td>
                                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                                            <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                                            <td><?= htmlspecialchars($appointment['treatment']) ?></td>
                                            <td><?= htmlspecialchars($appointment['dentist_name']) ?></td>

                                            <?php if ($status === 'upcoming' || $status === 'rescheduled'): ?>
                                                <td class="action-buttons">
                                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#rescheduleModal"
                                                        data-appointmentid="<?php echo htmlspecialchars($appointment['id']); ?>"
                                                        data-time="<?php echo htmlspecialchars($appointment['appointment_time']); ?>"
                                                        data-date="<?= $appointment['appointment_date'] ?>">Reschedule</button>
                                                    <button class="btn btn-danger btn-cancel" data-bs-toggle="modal"
                                                        data-bs-target="#cancelModal"
                                                        data-id="<?= htmlspecialchars($appointment['id']) ?>">
                                                        Cancel
                                                    </button>

                                                </td>
                                            <?php else: ?>
                                                <td class="action-buttons" style="display: none;"></td>
                                            <?php endif; ?>

                                            <?php if ($status === 'rejected'): ?>
                                                <td class="reason-column"><?= htmlspecialchars($appointment['reason']) ?></td>
                                            <?php else: ?>
                                                <td class="reason-column" style="display: none;"></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Cancel Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <input type="hidden" name="cancel_appointment_id" id="cancel_appointment_id">
                        <p>Are you sure you want to cancel this appointment?</p>
                        <div class="modal-footer">

                            <button type="submit" name="cancel_appointment" class="btn btn-danger">Yes, Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".btn-cancel").forEach(button => {
                button.addEventListener("click", function () {
                    let appointmentId = this.getAttribute("data-id");
                    document.getElementById("cancel_appointment_id").value = appointmentId;
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
    <!-- Reschedule Modal -->
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
                        <button type="submit" name="reschedupcoming" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        var bookedSlots = <?= json_encode($booked_slots); ?>;

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
                var timeSlots = getAvailableTimeSlots(startTime, endTime, duration, formattedDate);


                const startTime = '08:00'; // Using 24-hour format for clarity
                const endTime = '17:00'; // Using 24-hour format for clarity
                const duration = getDuration(appointmentTime); // Duration in minutes

                function getAvailableTimeSlots(startTime, endTime, duration, selectedDate) {
                    const slots = [];
                    let currentTime = new Date(`1970-01-01T${startTime}:00`);
                    const endTimeDate = new Date(`1970-01-01T${endTime}:00`);

                    while (currentTime < endTimeDate) {
                        let nextTime = new Date(currentTime.getTime() + duration * 60000);
                        if (nextTime <= endTimeDate) {
                            // Format the time slot as "HH:MM - HH:MM"
                            const slot = `${currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${nextTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

                            // Construct full datetime string to check against bookedSlots
                            const slotStartTime = currentTime.toTimeString().split(' ')[0].slice(0, 5); // HH:MM
                            const fullDateTime = `${selectedDate} ${slotStartTime}`;

                            // Check if slot is booked
                            if (!bookedSlots.includes(fullDateTime)) {
                                slots.push(slot);
                            }
                        }
                        currentTime = nextTime;
                    }
                    return slots;
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


    <script>


        document.addEventListener("DOMContentLoaded", function () {
            const tabs = document.querySelectorAll(".tab");
            const reasonColumnHeader = document.querySelector(".reason-column");
            const actionColumnHeader = document.querySelector(".action-column");

            tabs.forEach(tab => {
                tab.addEventListener("click", function () {
                    const selectedTab = this.getAttribute("data-tab");

                    // Show "Reason" column only for "Rejected"
                    if (selectedTab === "rejected") {
                        reasonColumnHeader.style.display = "";
                        document.querySelectorAll(".reason-column").forEach(cell => cell.style.display = "");
                    } else {
                        reasonColumnHeader.style.display = "none";
                        document.querySelectorAll(".reason-column").forEach(cell => cell.style.display = "none");
                    }

                    // Show "Action" column only for "Upcoming" and "Rescheduled"
                    if (["upcoming", "rescheduled"].includes(selectedTab)) {
                        actionColumnHeader.style.display = "";
                        document.querySelectorAll(".action-buttons").forEach(cell => cell.style.display = "");
                    } else {
                        actionColumnHeader.style.display = "none";
                        document.querySelectorAll(".action-buttons").forEach(cell => cell.style.display = "none");
                    }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            const tabs = document.querySelectorAll(".tab");
            const rows = document.querySelectorAll(".appointment-row");
            const reasonColumnHeader = document.querySelector(".reason-column");

            tabs.forEach(tab => {
                tab.addEventListener("click", function () {
                    const selectedTab = this.getAttribute("data-tab");

                    // Toggle visibility of rows
                    rows.forEach(row => {
                        if (row.getAttribute("data-status") === selectedTab || selectedTab === "all") {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });

                    // Show "Reason" column only for the "Rejected" tab
                    if (selectedTab === "rejected") {
                        reasonColumnHeader.style.display = "";
                        document.querySelectorAll(".reason-column").forEach(cell => cell.style.display = "");
                    } else {
                        reasonColumnHeader.style.display = "none";
                        document.querySelectorAll(".reason-column").forEach(cell => cell.style.display = "none");
                    }
                });
            });
        });

        // First implementation (appears around line 191):
        document.addEventListener("DOMContentLoaded", function () {
            // Function to filter appointments
            function filterAppointments(status) {
                const rows = document.querySelectorAll('.appointment-row');
                rows.forEach(row => {
                    const rowStatus = row.getAttribute('data-status').toLowerCase();
                    if (rowStatus === status.toLowerCase()) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Add click handlers to tabs
            const tabs = document.querySelectorAll('.appointments-tabs .tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Filter appointments based on tab
                    const status = this.getAttribute('data-tab');
                    filterAppointments(status);
                });
            });

            // Initially filter to show upcoming appointments
            filterAppointments('upcoming');
        });


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

        // Close modal if user clicks outside of it
        window.onclick = function (event) {
            var logoutDialog = document.getElementById('logoutConfirmDialog');
            if (event.target == logoutDialog) {
                closeLogoutDialog();
            }
        }
    </script>
</body>

</html>