<?php

require_once('../db/db_patient_appointments.php');
require_once '../db/config.php';

$username = $_SESSION['username']; // Use username if needed
$patient_id = $_SESSION['id'];     // <-- Fixed missing semicolon

$allowedTabs = ['submitted', 'upcoming', 'rescheduled', 'completed', 'cancelled', 'rejected'];

// Get tab from GET and validate it
$activeTab = isset($_GET['tab']) && in_array($_GET['tab'], $allowedTabs)
    ? $_GET['tab']
    : 'submitted';

function fetchAppointmentsByStatus($db, $status, $patient_id)
{
    $query = "SELECT 
                a.appointment_id,
                a.patient_id,
                a.notes,
                CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
                s.name AS treatment_name,
                a.appointment_time_start,
                a.appointment_time_end,
                a.appointment_date,
                a.status
              FROM appointments a
              JOIN users u ON a.patient_id = u.id
              JOIN services s ON a.service_id = s.id
              WHERE a.status = ?
                AND a.patient_id = ?";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        die("Prepare Error: " . $db->error);
    }

    $stmt->bind_param("si", $status, $patient_id);
    $stmt->execute();
    return $stmt->get_result();
}

$resultsubmitted = fetchAppointmentsByStatus($db, 'submitted', $patient_id);
$resultupcoming = fetchAppointmentsByStatus($db, 'upcoming', $patient_id);
$resultcompleted = fetchAppointmentsByStatus($db, 'completed', $patient_id);
$resultrescheduled = fetchAppointmentsByStatus($db, 'rescheduled', $patient_id);
$resultcancelled = fetchAppointmentsByStatus($db, 'cancelled', $patient_id);
$resultreject = fetchAppointmentsByStatus($db, 'rejected', $patient_id);


$tab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
//$showActionColumn = !in_array($status, ['completed', 'cancelled']);

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
                        <a href="?tab=submitted"
                            class="tab <?php echo ($_GET['tab'] ?? 'submitted') == 'submitted' ? 'active' : ''; ?>">Submitted</a>

                        <a href="?tab=upcoming"
                            class="tab <?php echo ($_GET['tab'] ?? 'upcoming') == 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                        <a href="?tab=rescheduled"
                            class="tab <?php echo ($_GET['tab'] ?? '') == 'rescheduled' ? 'active' : ''; ?>">Re-scheduled</a>
                        <a href="?tab=completed"
                            class="tab <?php echo ($_GET['tab'] ?? '') == 'completed' ? 'active' : ''; ?>">Completed</a>
                        <a href="?tab=cancelled"
                            class="tab <?php echo ($_GET['tab'] ?? '') == 'cancelled' ? 'active' : ''; ?>">Cancelled</a>

                        <a href="?tab=rejected"
                            class="tab <?php echo ($_GET['tab'] ?? '') == 'rejected' ? 'active' : ''; ?>">Rejected</a>

                    </div>


                    <?php
                    if ($activeTab == 'submitted'): ?>

                        <div class="overflow-x-scroll  w-100">
                            <table id="appointment-approve-submitted" class="w-100">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>

                                        <th>Treatment</th>

                                        <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                            <th class="action-column">Action</th>
                                        <?php endif; ?>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultsubmitted)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time_start'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time_end']));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time_start']); ?> -
                                                <?php echo htmlspecialchars($row['appointment_time_end']); ?>
                                            </td>

                                            <td><?php echo htmlspecialchars($row['treatment_name']); ?></td>
                                            <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                                <td class="action-buttons">
                                                    <!--    <button class="complete-btn" data-id="<?//php// echo $row['id']; ?>">Mark as Done</button>
                                     <button class="archive-btn">Decline</button> -->
                                                    <button class="complete-btn btn btn-primary"
                                                        data-id="<?= $row['appointment_id']; ?>" data-bs-toggle="modal"
                                                        data-bs-target="#completeModal">
                                                        Mark as Done
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>


                    <?php elseif ($activeTab == 'upcoming'): ?>

                        <div class="overflow-x-scroll  w-100">
                            <table id="appointment-approve-upcoming" class="w-100">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>

                                        <th>Treatment</th>

                                        <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                            <th class="action-column">Action</th>
                                        <?php endif; ?>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultupcoming)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time_start'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time_end']));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time_start']); ?> -
                                                <?php echo htmlspecialchars($row['appointment_time_end']); ?>
                                            </td>

                                            <td><?php echo htmlspecialchars($row['treatment_name']); ?></td>
                                            <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                                <td class="action-buttons">
                                                    <!--    <button class="complete-btn" data-id="<?//php// echo $row['id']; ?>">Mark as Done</button>
                                     <button class="archive-btn">Decline</button> -->
                                                    <button class="complete-btn btn btn-primary"
                                                        data-id="<?= $row['appointment_id']; ?>" data-bs-toggle="modal"
                                                        data-bs-target="#completeModal">
                                                        Mark as Done
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($activeTab == 'rescheduled'): ?>

                        <div class="overflow-x-scroll  w-100">
                            <table id="appointment-approve-rescheduled" class="w-100">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>

                                        <th>Treatment</th>

                                        <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                            <th class="action-column">Action</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultrescheduled)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time_start'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time_end']));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time_start']); ?> -
                                                <?php echo htmlspecialchars($row['appointment_time_end']); ?>
                                            </td>

                                            <td><?php echo htmlspecialchars($row['treatment_name']); ?></td>
                                            <?php if ($_SESSION['usertype'] == 'dentist'): ?>

                                                <td class="action-buttons">
                                                    <!--    <button class="complete-btn" data-id="<?//php// echo $row['id']; ?>">Mark as Done</button>
                                     <button class="archive-btn">Decline</button> -->
                                                    <button class="complete-btn btn btn-primary"
                                                        data-id="<?= $row['appointment_id']; ?>" data-bs-toggle="modal"
                                                        data-bs-target="#completeModal">
                                                        Mark as Done
                                                    </button>
                                                </td>

                                            <?php endif; ?>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>

                            </table>
                        </div>
                    <?php elseif ($activeTab == 'completed'): ?>

                        <div class="overflow-x-scroll  w-100">
                            <table id="appointment-approve-completed" class="w-100">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>

                                        <th>Treatment</th>

                                        <th class="reason-column">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultcompleted)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time_start'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time_end']));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['appointment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time_start']); ?> -
                                                <?php echo htmlspecialchars($row['appointment_time_end']); ?>
                                            </td>

                                            <td><?php echo htmlspecialchars($row['treatment_name']); ?></td>

                                            <td class="reason-note">
                                                <?php echo htmlspecialchars($row['notes']); ?>
                                            </td>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($activeTab == 'cancelled'): ?>

                        <div class="overflow-x-scroll  w-100">
                            <table id="appointment-approve-cancelled" class="w-100">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>

                                        <th>Treatment</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultcancelled)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time_start'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time_end']));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time_start']); ?> -
                                                <?php echo htmlspecialchars($row['appointment_time_end']); ?>
                                            </td>

                                            <td><?php echo htmlspecialchars($row['treatment_name']); ?></td>


                                        </tr>
                                    <?php endwhile; ?>


                                    <?php while ($row = mysqli_fetch_assoc($resultreject)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time_start'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time_end']));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time_start']); ?> -
                                                <?php echo htmlspecialchars($row['appointment_time_end']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['treatment_name']); ?></td>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($activeTab == 'rejected'): ?>

                        <div class="overflow-x-scroll  w-100">
                            <table id="appointment-rejected" class="w-100">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>

                                        <th>Treatment</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultreject)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time_start'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time_end']));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time_start']); ?> -
                                                <?php echo htmlspecialchars($row['appointment_time_end']); ?>

                                            <td><?php echo htmlspecialchars($row['treatment_name']); ?></td>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <script>
                        $(document).ready(function () {
                            $('#appointment-approve-submitted').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],
                                "columnDefs": [
                                    { "orderable": false, "targets": "action-column" }

                                ]
                            });
                            $('#appointment-approve-upcoming').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],
                                "columnDefs": [
                                    { "orderable": false, "targets": "action-column" }

                                ]
                            });
                            $('#appointment-approve-rescheduled').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],
                                "columnDefs": [
                                    { "orderable": false, "targets": "action-column" }

                                ]
                            });
                            $('#appointment-approve-completed').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],

                            });
                            $('#appointment-approve-cancelled').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],

                            });
                            $('#appointment-rejected').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],

                            });
                        });
                    </script>


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


                // Convert date to YYYY-MM-DD format
                var dateObj = new Date(appointmentDate);
                var formattedDate = dateObj.toISOString().split('T')[0]; // Ensures YYYY-MM-DD format

                document.getElementById("appointment_id").value = appointmentId;
                document.getElementById("appointment_date").value = formattedDate;

                var timeSelect = document.getElementById("appointment_time");

                fetch('get_available_slots.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `date=${selectedDate}&duration=${duration}`
                })
                    .then(response => response.text())
                    .then(text => {
                        try {
                            const availableSlots = JSON.parse(text);

                            // Get current options (excluding the placeholder)
                            const currentOptions = Array.from(timeSelect.options)
                                .slice(1) // skip the first option "Select a time slot"
                                .map(opt => opt.value);

                            // Compare arrays: skip update if no change
                            const slotsChanged = availableSlots.length !== currentOptions.length ||
                                availableSlots.some((slot, i) => slot !== currentOptions[i]);

                            if (slotsChanged) {
                                // Update only if there's a change
                                timeSelect.innerHTML = '<option value="">Select a time slot</option>';
                                availableSlots.forEach(slot => {
                                    const option = document.createElement('option');
                                    option.value = slot;
                                    option.textContent = slot;
                                    timeSelect.appendChild(option);
                                });


                            }
                        } catch (error) {
                            console.error("Error parsing JSON:", error, "\nServer Response:", text);
                        }
                    })
                    .catch(error => console.error('Error fetching available slots:', error));

            });

            // Fetch current time every second (1000 milliseconds)
            setInterval(fetchCurrentTime, 1000);

            // Initial call to display time immediately on page load
            fetchCurrentTime();


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