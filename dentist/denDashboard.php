<?php
require_once '../db/config.php';

if (!isset($_SESSION['id'])) {
    header('location: ../receptionist_admin/employeeLogin.php');
    exit();
}

// Fetch the nearest appointment with 'upcoming' status
$query = "SELECT id, patient_id, patient_name, treatment, appointment_time, appointment_date, dentist_name, status 
          FROM approved_requests 
          WHERE status = 'upcoming'
          ORDER BY ABS(DATEDIFF(STR_TO_DATE(appointment_date, '%Y-%m-%d'), CURDATE())) ASC,
                   STR_TO_DATE(appointment_time, '%H:%i:%s') ASC 
          LIMIT 1";
$result = mysqli_query($db, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($db));
}
$upcomingAppointments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch appointment statistics
$query_appointments = "
SELECT
    (SELECT COUNT(*) FROM appointments WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())) AS total_appointments,
    (SELECT COUNT(*) FROM approved_requests WHERE status = 'upcoming') AS approved_requests,
    (SELECT COUNT(*) FROM approved_requests WHERE status = 'Completed') AS completed_requests,
    (SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())) AS total_patients";
$result_appointments = mysqli_query($db, $query_appointments);
$total_appointments = $approved_requests = $completed_requests = $total_patients = 0;

if ($row_appointments = mysqli_fetch_assoc($result_appointments)) {
    $total_appointments = $row_appointments['total_appointments'];
    $approved_requests = $row_appointments['approved_requests'];
    $completed_requests = $row_appointments['completed_requests'];
    $total_patients = $row_appointments['total_patients'];
}

$firstName = $_SESSION['first_name'];
$gender = $_SESSION['gender'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dentist Dashboard</title>

    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="denDashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
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

    .treatment-container {
        margin: 20px;
        padding: 16px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    #treatment-box-container {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        /* Space between the boxes */
        justify-content: flex-start;
    }

    .treatment-box {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 16px;
        width: calc(33.333% - 16px);
        /* Adjust width for 3 columns */
        box-sizing: border-box;
        text-align: left;
    }

    .treatment-item {
        margin-bottom: 8px;
        font-size: 14px;
        color: #333;
    }

    .treatment-item strong {
        color: #555;
    }

    .no-data {
        width: 100%;
        text-align: center;
        color: #999;
        font-size: 16px;
    }

    .top-header img {
        width: 55px;
        height: 50px;
        margin-right: 10px;
        border-radius: 20%;
    }

    .popup {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 300px;
        background-color: white;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        text-align: center;
        padding: 20px;
    }

    .popup-content {
        font-size: 16px;
        font-weight: bold;
    }

    .close {
        color: red;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        float: right;
    }

    .close:hover {
        color: darkred;
    }

    .appointment-section {
        margin-bottom: 2rem;
    }

    .appointment-section h3,
    .payment-section h3 {
        font-size: 16px;
        color: #965757;
        margin-bottom: 10px;
    }

    .appointment-card,
    .payment-section {
        background-color: #fff;
        /* White background for cards */
        border: 1px solid #d99e9e;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .date-display {
        font-size: 18px;
        font-weight: bold;
        color: #d99e9e;
        margin-bottom: 1px;
    }

    .appointment-details {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        /* 4 columns */
        gap: 1rem;
        /* Spacing between columns */
        width: 100%;
        /* Ensure it stretches properly */
    }

    .appointment-details div {
        text-align: center;
        margin-bottom: 10px;
    }

    .payment-section {
        text-align: center;
        font-size: 18px;
        color: #5C3A31;
    }
</style>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <!-- Notification Pop-Up -->
    <div id="notificationPopup"
        style="display: none; position: fixed; top: 20px; right: 500px; background: white; color: red; text-align: center; padding: 15px; border-radius: 10px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1); z-index: 1000;">
        <strong>New Appointment!</strong><br><br>A patient has submitted an appointment request.
    </div>

    <div class="main-wrapper">
        <!-- Sidebar Menu -->
        <?php
        $navactive = "denDashboard";

        require_once "../db/nav.php" ?>
        <!-- Main Dashboard Content -->
        <div class="main-content">
            <!-- Main Dashboard Area -->
            <div class="dashboard">
                <div class="appointment-report">
                    <div class="report-header">
                        <div class="greeting">
                            <h2>Good Day, <br>
                                <?php
                                if ($gender == 'Male') {
                                    echo "Dr. " . htmlspecialchars($firstName);
                                } elseif ($gender == 'Female') {
                                    echo "Dra. " . htmlspecialchars($firstName);
                                } else {
                                    echo htmlspecialchars($firstName); // Fallback for other values
                                }
                                ?>
                            </h2>
                            <!-- <div class="statistics-report">
                                <h3>APPOINTMENT STATISTICS</h3>
                            </div> -->
                        </div>
                        <div class="date-today">
                            <p id="current-date"></p>
                            <!-- <div class="view-buttons">
                                <button class="chart-button" data-period="monthly">Monthly</button>
                                <button class="chart-button" data-period="weekly">Weekly</button>
                                <button class="chart-button" data-period="daily">Daily</button>
                            </div> -->
                        </div>
                    </div>
                    <div class="appointment-section">
                        <h3>YOUR NEXT PATIENT</h3>
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
                                            <strong>Treatment</strong><br>
                                            <?php echo htmlspecialchars($appointment['treatment']); ?>
                                        </div>
                                        <div>
                                            <strong>Time</strong><br>
                                            <?php echo htmlspecialchars($appointment['appointment_time']); ?>
                                        </div>
                                        <div>
                                            <strong>Patient Name</strong><br>
                                            <?php echo htmlspecialchars($appointment['patient_name']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="appointment-card">
                                <div class="appointment-details">
                                    <p>No Appointments Yet.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Bar Chart -->
                    <!-- <div class="bar-chart">
                        <canvas id="bar-chart" width="800" height="300"></canvas>
                    </div> -->
                </div>
                <div class="approval-request">
                    <!-- <div><p>APPROVAL REQUEST</p></div>
                    <div><p1 id = "totalAppointments"><?php echo htmlspecialchars($total_appointments); ?></p1></div>
                    <div class="description">
                        <p3>Pending Appointments to Approved
                            <button class="more-buttons" onclick="window.location.href='denRequest.php'">More</button>
                        </p3>
                    </div> -->
                    <div>
                        <p>UPCOMING APPOINTMENTS</p>
                    </div>
                    <div>
                        <p1><?php echo htmlspecialchars($approved_requests); ?></p1>
                    </div>
                    <div class="description">
                        <button class="more-buttons" onclick="window.location.href='denAppointments.php'">More</button>
                    </div>
                </div>
            </div>
            <!-- Statistics Summary -->
            <div class="statistics">
                <div class="stat-box">
                    <h4>TOTAL PATIENTS THIS MONTH</h4>
                    <div class="number">
                        <p><?php echo htmlspecialchars($total_patients); ?></p>
                    </div>
                </div>
                <div class="stat-box1">
                    <h4></h4>
                    <div class="number">
                        <!-- <p class="date-now"><?php echo htmlspecialchars($approved_requests); ?></p>
                        <div class="treatment-container">
                            <div id="treatment-box-container">
                                <?php
                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<div class='treatment-box'>
                                                <div class='treatment-item'>
                                                    <strong>Treatment:</strong> " . htmlspecialchars($row['treatment']) . "
                                                </div>
                                                <div class='treatment-item'>
                                                    <strong>Time:</strong> " . htmlspecialchars($row['appointment_time']) . "
                                                </div>
                                            </div>";
                                    }
                                } else {
                                    echo "<div class='no-data'>No data available</div>";
                                }
                                ?>
                            </div>
                        </div> -->
                    </div>
                </div>
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

    <!-- JavaScript for Dropdown and Chart -->
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

        document.addEventListener('DOMContentLoaded', function () {
            setInterval(fetchCurrentTime, 1000);
            fetchCurrentTime();
        });

        updateDate();
        setInterval(updateDate, 60000); // Update every minute

        function showPopup() {
            document.getElementById("notificationPopup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("notificationPopup").style.display = "none";
        }

        // Get the total appointments count from the PHP variable
        let totalAppointments = parseInt(document.getElementById("totalAppointments").textContent);

        // Show pop-up if there is a new appointment request
        if (totalAppointments > 0) {
            showPopup();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Dropdown functionality
            const dropdownButtons = document.querySelectorAll('.dropdown-btn');
            dropdownButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    this.classList.toggle('active');
                    const dropdownContainer = this.nextElementSibling;
                    dropdownContainer.style.display = dropdownContainer.style.display === 'block' ? 'none' : 'block';
                });
            });

            // Bar Chart with Chart.js
            const ctx = document.getElementById('bar-chart').getContext('2d');

            // Data for different time periods
            const chartData = {
                monthly: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    data: [10, 5, 8, 7, 12, 20, 18, 15, 10, 8, 6, 4]
                },
                weekly: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    data: [25, 18, 15, 20]
                },
                daily: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    data: [3, 5, 2, 8, 6, 4, 7]
                }
            };

            // Chart configuration
            let barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.monthly.labels,
                    datasets: [{
                        label: 'Appointments',
                        data: chartData.monthly.data,
                        backgroundColor: 'rgba(234, 84, 85, 0.7)',
                        borderColor: 'rgba(234, 84, 85, 1)',
                        borderWidth: 1,
                        borderRadius: 5, // Rounded bar corners
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Event listener for switching datasets
            const chartButtons = document.querySelectorAll('.chart-button');
            chartButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const period = this.dataset.period;
                    barChart.data.labels = chartData[period].labels;
                    barChart.data.datasets[0].data = chartData[period].data;
                    barChart.update();

                    // Highlight active button
                    chartButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
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


        //Real-Time Notifications and Date Updates   
        function updateDate() {
            const dateElement = document.getElementById('current-date');
            const today = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            dateElement.innerHTML = today.toLocaleDateString('en-US', options);
        }

        updateDate();
        setInterval(updateDate, 60000); // Update every minute

        // Real-Time Appointment Check
        function showPopup() {
            const popup = document.getElementById("notificationPopup");
            popup.style.display = "block";
            setTimeout(closePopup, 5000); // Auto-close after 5 seconds
        }

        function closePopup() {
            const popup = document.getElementById("notificationPopup");
            popup.style.display = "none";
        }

        function checkNewAppointments() {
            $.ajax({
                url: 'checkNewAppointments.php', // PHP script to check for new appointments
                method: 'GET',
                success: function (response) {
                    if (response === '1') { // If new requests are found
                        showPopup();
                    }
                },
                error: function () {
                    console.error("Error checking for new appointments.");
                }
            });
        }

        // Check for new appointments every 10 seconds
        setInterval(checkNewAppointments, 10000);

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