<?php
require_once '../db/config.php';
// Fetch the nearest appointment with 'upcoming' status
$query = "SELECT 
            a.appointment_id AS id,
            a.patient_id,
            CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
            s.name AS treatment,
            CONCAT(
               DATE_FORMAT(STR_TO_DATE(a.appointment_time_start, '%h:%i %p'), '%h:%i %p'),
        ' - ',
        DATE_FORMAT(STR_TO_DATE(a.appointment_time_end, '%h:%i %p'), '%h:%i %p')
            ) AS appointment_time,
            a.appointment_date,
            CONCAT(e.first_name, ' ', e.last_name) AS dentist_name,
            a.status
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        JOIN services s ON a.service_id = s.id
        JOIN users_employee e ON a.dentist_id = e.id
        WHERE a.status = 'upcoming'
        ORDER BY 
            ABS(DATEDIFF(STR_TO_DATE(a.appointment_date, '%Y-%m-%d'), CURDATE())) ASC,
            STR_TO_DATE(a.appointment_time_start, '%H:%i:%s') ASC
        LIMIT 5";

$result = mysqli_query($db, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($db));
}
$upcomingAppointments = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (!isset($_SESSION['id'])) {
    header('location: employeeLogin.php');
    exit();
}


$firstName = $_SESSION['first_name'];
$gender = $_SESSION['gender'];

$total_users = 0;
$total_appointments = 0;
$total_notified = 0;
$approved_requests = 0;

$query_users = "
SELECT
    (SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())) AS total_patients,
    (SELECT COUNT(*) FROM users_employee WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())) AS total_employees,
    (SELECT COUNT(*) FROM appointments WHERE status = 'upcoming') AS approved_requests,
    (SELECT COUNT(*) FROM appointments WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())) AS total_appointments
";

$result_users = mysqli_query($db, $query_users);
if ($row_users = mysqli_fetch_assoc($result_users)) {
    $total_patients = $row_users['total_patients'];
    $total_employees = $row_users['total_employees'];
    $total_appointments = $row_users['total_appointments'];
    $approved_requests = $row_users['approved_requests'];
}

$total_users = $total_patients + $total_employees;


require_once "../db/scheduledemail.php";



// Fetch due schedules
$query = "
   SELECT e.id, e.patientid, e.frequency, e.message, e.last_send, u.email
FROM emailsched e
JOIN users u ON e.patientid = u.id
WHERE e.last_send IS NULL 
   OR (
       e.frequency = 'daily' AND DATE_ADD(e.last_send, INTERVAL 1 DAY) <= CURDATE()
       OR e.frequency = 'weekly' AND DATE_ADD(e.last_send, INTERVAL 1 WEEK) <= CURDATE()
       OR e.frequency = 'biweekly' AND DATE_ADD(e.last_send, INTERVAL 2 WEEK) <= CURDATE()
       OR e.frequency = 'monthly' AND DATE_ADD(e.last_send, INTERVAL 1 MONTH) <= CURDATE()
       OR e.frequency = '3months' AND DATE_ADD(e.last_send, INTERVAL 3 MONTH) <= CURDATE()
       OR e.frequency = '6months' AND DATE_ADD(e.last_send, INTERVAL 6 MONTH) <= CURDATE()
       OR e.frequency = 'yearly' AND DATE_ADD(e.last_send, INTERVAL 1 YEAR) <= CURDATE()
   );
";

$result = $db->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $email = $row['email'];
        $message = $row['message'];

        // Send email
        if (sendmail($email, $message)) {
            // Update last_send date on success
            $updateQuery = "UPDATE emailsched SET last_send = CURDATE() WHERE id = ?";
            $stmt = $db->prepare($updateQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="adminStyles.css">
    <?php require_once "../db/head.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <div class="main-wrapper overflow-hidden">
        <!-- Sidebar Menu -->
        <?php
        $navactive = "recepDashboard";

        require_once "../db/nav.php" ?>

        <!-- Main Dashboard Content -->
        <div class="main-content overflow-hidden">
            <!-- Greeting Section -->
            <div class="card mb-3">
                <div class="card-body">
                    <h2>Good Day, <br>
                        <?php
                        if ($gender == 'Male') {
                            echo "Mr. " . htmlspecialchars($firstName);
                        } elseif ($gender == 'Female') {
                            echo "Ms. " . htmlspecialchars($firstName);
                        } else {
                            echo htmlspecialchars($firstName); // Fallback for other values
                        }
                        ?>
                    </h2>
                </div>
            </div>

            <!-- Main Dashboard Area -->
            <div class="card mb-3">
                <div class="card-body">
                    <!-- <div class="report-header">
                        <h3>Appointment Statistical Report</h3>
                        <div class="view-buttons">
                            <button class="chart-button" data-period="monthly">Monthly</button>
                            <button class="chart-button" data-period="weekly">Weekly</button>
                            <button class="chart-button" data-period="daily">Daily</button>
                        </div>
                    </div>
                     <div class="bar-chart">
                        <canvas id="bar-chart" width="1400" height="300"></canvas>
                    </div> -->
                    <div class="appointment-section">
                        <h2>APPOINTMENT LIST</h2>
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
                </div>
            </div>
            <!-- Statistics Summary -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card ">
                        <div class="card-body">
                            <h4>Total System Users</h4>
                            <p><?php echo htmlspecialchars($total_users); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card ">
                        <div class="card-body">
                            <h4>Total Upcoming Appointments</h4>
                            <p><?php echo htmlspecialchars($approved_requests); ?></p>
                        </div>
                        <!-- <div class="stat-box">
                    <h4>Total Patients Notified with SMS This Month</h4>
                    <p>0</p>
                </div> -->
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

            // Fetch current time every second (1000 milliseconds)
            setInterval(fetchCurrentTime, 1000);

            // Initial call to display time immediately on page load
            fetchCurrentTime();

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

                /*       // Bar Chart with Chart.js
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
                      }); */
            });

            // Add this new function for logout confirmation
            function confirmLogout() {
                if (confirm("Are you sure you want to logout?")) {
                    window.location.href = '../dentist/logout.php';
                }
            }

            function showLogoutDialog() {
                document.getElementById('logoutConfirmDialog').style.display = 'block';
            }

            function closeLogoutDialog() {
                document.getElementById('logoutConfirmDialog').style.display = 'none';
            }

            function logout() {
                window.location.href = '../dentist/logout.php';
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