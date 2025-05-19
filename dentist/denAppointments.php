<?php
// Database connection code at the top of your file
require_once '../db/config.php';

$query = "SELECT id, patient_id, notes, patient_name, treatment, appointment_time, appointment_date, status
          FROM approved_requests";

$result = mysqli_query($db, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($db));
}
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
//$showActionColumn = !in_array($status, ['completed', 'cancelled']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['id']) || empty($_POST['notes'])) {
        echo "<script>alert('ID and Notes are required');</script>";
        exit;
    }

    $id = intval($_POST['id']); // Convert to integer to prevent SQL injection
    $notes = trim($_POST['notes']); // Trim spaces
    $status = "completed";
    // Check if ID is valid
    if ($id <= 0) {
        echo "<script>alert('Invalid ID');</script>";
        exit;
    }

    // Prepare and execute update query
    $stmt = $db->prepare("UPDATE approved_requests SET notes = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $notes, $status, $id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<script>alert('Request updated successfully!'); window.location.href='denAppointments.php';</script>";
    } else {
        echo "<script>alert('Failed to update record or no changes made');</script>";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dentist Appointments</title>
    <link rel="stylesheet" href="denAppointments.css">
    <?php require_once "../db/head.php" ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="recepScript.js" defer></script>
</head>
<style>
    .logout-confirm-dialog {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .logout-dialog-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border-radius: 5px;
        width: 300px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .logout-dialog-buttons {
        margin-top: 20px;
    }

    .btn-confirm,
    .btn-cancel {
        padding: 8px 20px;
        margin: 0 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-confirm {
        background-color: #ea5455;
        color: white;
    }

    .btn-confirm:hover {
        background-color: #d64849;
    }

    .btn-cancel {
        background-color: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background-color: #5a6268;
    }

    .top-header img {
        width: 55px;
        height: 50px;
        margin-right: 10px;
        border-radius: 20%;
    }
</style>

<body style="min-height:100vh;">
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper">
        <!-- Sidebar Menu -->
        <?php
        $navactive = "denAppointments";

        require_once "../db/nav.php" ?>


        <div class="user-table">
            <div class="table-header">
                <div>
                    <h2>List of Appointments</h2>
                    <!-- <div class="search-bar">
                            <input type="text" placeholder="Search by name">
                        </div> -->
                </div>
            </div>
            <div class="appointments-tabs">
                <button class="tab active" data-tab="upcoming">Upcoming</button>
                <button class="tab" data-tab="rescheduled">Re-scheduled</button>
                <button class="tab" data-tab="completed">Completed</button>
                <button class="tab" data-tab="cancelled">Cancelled</button>
                <?php

                $queryreject = "SELECT id, patient_id, patient_name, treatment, appointment_time, appointment_date, status
                FROM rejected_requests";

                $resultreject = mysqli_query($db, $queryreject);

                if (!$resultreject) {
                    die("Query Error: " . mysqli_error($db));
                }

                if ($_SESSION['usertype'] == 'clinic_receptionist'):


                    ?>
                    <button class="tab" data-tab="rejected">Rejected</button>
                <?php endif; ?>
                <!-- <button class="tab" data-tab="archived">Archived</button> -->
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Appointment No.</th>
                        <th>Appointment Date</th>
                        <th>Appointment Time</th>
                        <th>Name</th>
                        <th>Treatment</th>
                        <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                            <th class="action-column">Action</th>
                        <?php endif; ?>
                        <th class="reason-column" style="display:none;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                        // Determine appointment status
                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                        $currentDateTime = date('Y-m-d H:i:s');

                        // Get the end time of the appointment
                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                        // Default status is upcoming
                        $status = 'upcoming';

                        // Check if appointment has ended
                        if (strtotime($appointmentEndDateTime) < strtotime($currentDateTime)) {
                            $status = 'completed';
                        }

                        // Check for manual status updates from the database
                        if (!empty($row['status'])) {
                            $status = strtolower($row['status']);
                        }
                        ?>
                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>
                            <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                <?php if ($status !== 'completed' && $status !== 'cancelled'): ?>
                                    <td class="action-buttons">
                                        <!--    <button class="complete-btn" data-id="<?//php// echo $row['id']; ?>">Mark as Done</button>
                                     <button class="archive-btn">Decline</button> -->
                                        <button class="complete-btn btn btn-primary" data-id="<?= $row['id']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#completeModal">
                                            Mark as Done
                                        </button>
                                    </td>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($status === 'completed'): ?>
                                <td class="reason-note">
                                    <?php echo htmlspecialchars($row['notes']); ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>


                    <?php while ($row = mysqli_fetch_assoc($resultreject)): ?>
                        <?php
                        // Determine appointment status
                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                        $currentDateTime = date('Y-m-d H:i:s');

                        // Get the end time of the appointment
                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                        // Check for manual status updates from the database
                        if (!empty($row['status'])) {
                            $status = strtolower($row['status']);
                        }
                        ?>
                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>

                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>
    </div><!-- Complete Task Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalLabel">Mark as Done</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="completeForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="requestId">
                        <div class="mb-3">
                            <label for="dentalNotes" class="form-label">Dental Notes</label>
                            <textarea class="form-control" id="dentalNotes" name="notes" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


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
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".complete-btn").forEach(button => {
                button.addEventListener("click", function () {
                    let requestId = this.getAttribute("data-id");
                    document.getElementById("requestId").value = requestId;
                });
            });

        });

        document.addEventListener('DOMContentLoaded', function () {
            // Handle tab switching
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const status = this.getAttribute('data-status');
                    filterAppointments(status);

                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Handle Complete button clicks
            document.querySelectorAll('.complete-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');
                    row.setAttribute('data-status', 'completed');

                    // Update status in database via AJAX
                    const appointmentId = this.getAttribute('data-id');
                    updateAppointmentStatus(appointmentId, 'completed');

                    // Re-filter to show in correct tab
                    filterAppointments(document.querySelector('.tab.active').getAttribute('data-status'));
                });
            });
        });

        function filterAppointments(status) {
            document.querySelectorAll('.appointment-row').forEach(row => {
                if (row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        /*
                function updateAppointmentStatus(id, status) {
                    // AJAX call to update status in database
                    fetch('update_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}&status=${status}`
                    });
                }
        
                $(document).ready(function () {
                    $('.complete-btn').click(function () {
                        var appointmentId = $(this).data('id');
        
                        $.ajax({
                            url: 'update_status.php',
                            type: 'POST',
                            data: { id: appointmentId, status: 'Completed' },
                            success: function (response) {
                                // Optionally, update the row's status visually
                                location.reload(); // Reload the page to reflect changes
                            },
                            error: function () {
                            }
                        });
                    });
                });
        */
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab');

            // Retrieve last active tab from localStorage
            const activeTab = localStorage.getItem('activeAppointmentTab') || 'upcoming';

            // Set active tab
            tabs.forEach(tab => {
                tab.classList.remove('active');
                if (tab.dataset.tab === activeTab) {
                    tab.classList.add('active');
                }
            });

            // Filter appointments based on active tab
            filterAppointmentsByTab();

            // Add event listeners to tabs
            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Save active tab to localStorage
                    localStorage.setItem('activeAppointmentTab', this.dataset.tab);

                    // Filter appointments
                    filterAppointmentsByTab();
                });
            });

            function filterAppointmentsByTab() {
                const activeTab = document.querySelector('.tab.active');
                const activeTabName = activeTab.dataset.tab;

                document.querySelectorAll('.user-table table tbody tr').forEach(row => {
                    const rowStatus = row.dataset.status.toLowerCase();
                    row.style.display = (rowStatus === activeTabName) ? '' : 'none';
                });
            }
        });

        // Event listeners for specific tabs
        document.querySelector('.appointments-tabs .tab[data-tab="upcoming"]').addEventListener('click', () => {
            filterByStatus('Upcoming');
        });

        document.querySelector('.appointments-tabs .tab[data-tab="completed"]').addEventListener('click', () => {
            filterByStatus('Completed');
        });

        document.querySelector('.appointments-tabs .tab[data-tab="rescheduled"]').addEventListener('click', () => {
            filterByStatus('Rescheduled');
        });

        document.querySelector('.appointments-tabs .tab[data-tab="cancelled"]').addEventListener('click', () => {
            filterByStatus('Cancelled');
        });

        // Function to filter table rows based on status
        function filterByStatus(status) {
            const tableRows = document.querySelectorAll('table tbody tr');
            const reasonColumnHeader = document.querySelector(".reason-column");
            const actionColumnHeader = document.querySelector(".action-column");

            if (status === "Completed") {
                reasonColumnHeader.style.display = "";
                document.querySelectorAll(".reason-note").forEach(cell => cell.style.display = "");
            } else {
                reasonColumnHeader.style.display = "none";
                document.querySelectorAll(".reason-note").forEach(cell => cell.style.display = "none");
            }

            if (status === "Rescheduled" || status === "Upcoming") {
                actionColumnHeader.style.display = "";
                document.querySelectorAll(".action-buttons").forEach(cell => cell.style.display = "");
            } else {
                actionColumnHeader.style.display = "none";
                document.querySelectorAll(".action-buttons").forEach(cell => cell.style.display = "none");
            }

            tableRows.forEach(row => {
                if (row.getAttribute('data-status') === status) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });

            // Set the active tab
            document.querySelectorAll('.appointments-tabs .tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector(`.appointments-tabs .tab[data-tab="${status.toLowerCase()}"]`).classList.add('active');
        }

        // Function to fetch the current time
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

        // Fetch current time every second
        setInterval(fetchCurrentTime, 1000);
        fetchCurrentTime(); // Initial call to display time on page load

        // Tab switching logic
        document.addEventListener("DOMContentLoaded", () => {
            const tabs = document.querySelectorAll(".appointments-tabs .tab");
            const tabPanes = document.querySelectorAll(".tab-content .tab-pane");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    // Remove active class from all tabs and panes
                    tabs.forEach(t => t.classList.remove("active"));
                    tabPanes.forEach(pane => pane.classList.remove("active"));

                    // Add active class to the clicked tab and corresponding pane
                    tab.classList.add("active");
                    const tabId = tab.getAttribute("data-tab");
                    document.getElementById(tabId).classList.add("active");
                });
            });
        });

        // Dropdown toggle functionality
        document.addEventListener('DOMContentLoaded', function () {
            var dropdownButtons = document.querySelectorAll('.dropdown-btn');

            dropdownButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    this.classList.toggle('active');
                    var dropdownContainer = this.nextElementSibling;

                    // Toggle dropdown visibility
                    dropdownContainer.style.display = dropdownContainer.style.display === 'block' ? 'none' : 'block';
                });
            });
        });

        // Logout confirmation dialog
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
        };
    </script>

</body>

</html>