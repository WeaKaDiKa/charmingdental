<?php require_once '../db/config.php';
$query = "SELECT id, CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name, emergencyname, emergencycontact,gender, mobile, email, username
          FROM users";
$result = mysqli_query($db, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($db));
}
if (!isset($_GET['patientid'])) {
    header("Location: denPatientlist.php");
    exit;
}

$patientid = intval($_GET['patientid']);
$stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $patientid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: denPatientlist.php");
    exit;
}

// Initialize dental chart values
$teeth = '';
$cavity = '';
$gum_d = '';
$gum_m = '';

// Check if dentalchart exists
$stmt = $db->prepare("SELECT teeth, cavity, gum_d, gum_m FROM dentalchart WHERE usersid = ?");
$stmt->bind_param("i", $patientid);
$stmt->execute();
$chartResult = $stmt->get_result();
$exist = 0;

if ($chartResult->num_rows > 0) {
    $row = $chartResult->fetch_assoc();
    $teeth = htmlspecialchars($row['teeth']);
    $cavity = htmlspecialchars($row['cavity']);
    $gum_d = htmlspecialchars($row['gum_d']);
    $gum_m = htmlspecialchars($row['gum_m']);
    $exist = 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['patientid'])) {
    $usersid = intval($_GET['patientid']);
    $teeth = $_POST['teeth'] ?? '';
    $cavity = $_POST['cavity'] ?? '';
    $gumf = $_POST['gumf'] ?? '';
    $guml = $_POST['guml'] ?? '';

    // Check if the record already exists
    $stmt = $db->prepare("SELECT * FROM dentalchart WHERE usersid = ?");
    $stmt->bind_param("i", $usersid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update
        $update = $db->prepare("UPDATE dentalchart SET teeth = ?, cavity = ?, gum_d = ?, gum_m = ? WHERE usersid = ?");
        $update->bind_param("ssssi", $teeth, $cavity, $gumf, $guml, $usersid);
        $update->execute();
    } else {
        // Insert
        $insert = $db->prepare("INSERT INTO dentalchart (usersid, teeth, cavity, gum_d, gum_m) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("issss", $usersid, $teeth, $cavity, $gumf, $guml);
        $insert->execute();
    }

    header("Location: denPatientlist.php?message=dentalsaved"); // redirect as needed
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dentist Dashboard</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="denDashboard.css">

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <link href="style_dentalchart.css" rel="stylesheet" type="text/css" />

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
        $navactive = "denPatientlist";

        require_once "../db/nav.php" ?>
        <!-- Main Dashboard Content -->
        <div class="main-content">
            <!-- Main Dashboard Area -->
            <div class="card">

                <div class="card-body d-flex align-items-center justify-content-center flex-column w-100">
                    <h2>Dental Chart</h2>
                    <div id="app">
                        <!-- tops -->
                        <div class="gum-num-container gum-l-container" id="top-gum-l-container">
                            <div class="gum-label">L</div>
                        </div>
                        <div class="gum-num-container gum-f-container" id="top-gum-f-container">
                            <div class="gum-label">F</div>
                        </div>
                        <div class="container" id="top-teeth"></div>
                        <div class="container" id="bottom-teeth"></div>
                        <div class="gum-num-container gum-f-container" id="bottom-gum-f-container">
                            <div class="gum-label">F</div>
                        </div>
                        <div class="gum-num-container gum-l-container" id="bottom-gum-l-container">
                            <div class="gum-label">L</div>
                        </div>
                        <!-- etc -->
                        <div class="container" id="btn-container">
                            <div class="btn" id="remove">Remove</div>
                            <div class="btn" id="mand">MAND</div>
                            <div class="btn" id="max">MAX</div>
                            <div class="btn" id="reset">Tooth</div>
                            <div class="btn" id="screw">Screw</div>
                            <div class="btn" id="extraction">Extraction</div>
                            <div class="btn" id="crown">Crown</div>
                            <div class="btn" id="bridge">Bridge</div>
                            <!-- cavity btns -->
                            <div class="btn" id="clear">Clear</div>
                            <div class="btn cav-btn" id="b">B/F</div>
                            <div class="btn cav-btn" id="m">M</div>
                            <div class="btn cav-btn" id="o">O/I</div>
                            <div class="btn cav-btn" id="d">D</div>
                            <div class="btn cav-btn" id="l">L</div>
                            <div class="btn cav-btn" id="v">V</div>
                            <!-- system btns -->
                            <div class="btn" id="deselect">Deselect</div>
                            <!-- beta 
                    <div class="btn" id="undo">Undo</div>
                -->
                            <div class="btn" id="select-all">Select All</div>
                            <div class="btn" id="red-blue"></div>
                        </div>
                    </div>
                </div>


                <div id="#img-out"></div>

                <br />
                <form method="post">
                    <input type="hidden" id="teeth" name="teeth" value="<?= $teeth ?>" placeholder="Teeth Data" />
                    <input type="hidden" id="cavity" name="cavity" value="<?= $cavity ?>" placeholder="Cavity Data" />
                    <input type="hidden" id="gumf" name="gumf" value="<?= $gum_d ?>" placeholder="Gum F Data" />
                    <input type="hidden" id="guml" name="guml" value="<?= $gum_m ?>" placeholder="Gum L Data" />

                    <div style="display:flex; justify-content: center;">
                        <a id="download" class="btn btn-secondary outside-ctrls" download="chart.png" href="#">Generate
                            Image</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
                <script src="app.js"></script>
                <?php if ($exist): ?>
                    <script>
                        data.teeth = JSON.parse($('#teeth').val() || '[]');
                        data.cavity = JSON.parse($('#cavity').val() || '[]');
                        data.gumsF = JSON.parse($('#gumf').val() || '[]');
                        data.gumsL = JSON.parse($('#guml').val() || '[]');
                        build.table();
                    </script>
                <?php endif; ?>
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
        function updateDate() {
            const dateElement = document.getElementById('current-date');
            const today = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            dateElement.innerHTML = today.toLocaleDateString('en-US', options);
        }

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