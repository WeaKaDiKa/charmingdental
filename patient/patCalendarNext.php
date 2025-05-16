<?php
require_once '../db/config.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('location: index.php'); // Redirect to login if not logged in
    exit();
}

// Retrieve selected date and time from URL parameters
$appointmentDate = isset($_GET['date']) ? $_GET['date'] : null;
$appointmentTime = isset($_GET['time']) ? $_GET['time'] : null;
$dentistName = isset($_GET['dentistName']) ? $_GET['dentistName'] : null;
$treatment = isset($_GET['treatment']) ? $_GET['treatment'] : null;

// Validate appointment date and time
if (empty($appointmentDate) || empty($appointmentTime)) {
    echo "Error: Appointment date and time must be provided.";
    exit();
}

require_once('../db/db_patient_appointments.php')
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Charming Smile Dental Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="patAppointments.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* General button styling */
        /* Updated color palette */
        /* Main content styling */
        .main-content {
            padding: 2rem;
            background-color: #fff;
        }

        /* Section headers styling */
        .main-content h2 {
            background-color: #FFE4E1;
            color: #8B4513;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        /* Selection cards */
        #dentistList,
        #treatmentList {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dentist-item,
        .treatment-item {
            background-color: #FFE4E1;
            padding: 1.5rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Button styling */
        .select-dentist-button,
        .select-treatment-button {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            border: none;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .select-dentist-button {
            background-color: #28a745;
        }

        .select-treatment-button {
            background-color: #007BFF;
        }

        /* Summary section */
        #summary {
            background-color: #FFE4E1;
            padding: 2rem;
            border-radius: 8px;
            margin: 2rem 0;
        }

        #summary h3 {
            color: #8B4513;
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        #summary p {
            margin: 0.75rem 0;
            color: #A0522D;
            font-size: 1.1rem;
        }

        #summary span {
            font-weight: 600;
            color: #8B4513;
        }

        /* Confirm button */
        #confirmButton {
            background-color: #28a745;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: block;
            margin: 2rem auto;
            width: fit-content;
        }

        /* Back button - positioned at the right */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .back-button {
            background-color: #D8A5A5;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            margin-left: auto;
            /* Ensures button stays on the right */
        }

        .top-header img {
            width: 55px;
            height: 50px;
            margin-right: 10px;
            border-radius: 20%;
        }
    </style>
</head>

<body>
    <!-- Top Header -->
    <div class="top-header">
        <div class="left-section">
            <img src="../receptionist_admin/pfp.jpg" alt="Profile Picture" class="profile-pic">
            <div class="logoDental">CHARMING SMILE<br>DENTAL CLINIC</div>
        </div>
        <div class="user-info">
            <div class='current-datetime'>
                <span id="datetime"></span>
            </div>
            <div class="profile"><i class="fas fa-user-circle"></i></div>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav">
                <li><span onclick="window.location.href='patDashboard.php';"><i class="fas fa-tachometer-alt"></i>
                        Dashboard</span></li>
                <button class="dropdown-btn">
                    <span onclick="window.location.href='patCalendar.php';"><i class="fas fa-calendar-alt"></i> Calendar
                        <i class="fas fa-chevron-down dropdown-icon"></i></span>
                </button>
                <ul class="dropdown-container">
                    <li><span onclick="window.location.href='patAppointments.php';"><i class="fas fa-notes-medical"></i>
                            Appointments</span></li>
                </ul>
                <li><span onclick="window.location.href='patRecord.php';"><i class="fas fa-user"></i> Patient
                        Record</span></li>
                <li><span onclick="window.location.href='patPayment.php';"><i class="fas fa-credit-card"></i>
                        Payment</span></li>
                <li><span onclick="window.location.href='patPolicy.php';"><i class="fas fa-file-alt"></i> Policy</span>
                </li>
            </ul>
            <ul class="nav logout-nav">
                <li><span onclick="showLogoutDialog();"><i class="fas fa-sign-out-alt"></i> Logout</span></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header-container">
                <h2>Select Your Dentist</h2>
                <button id="backButton" class="back-button">Back</button>
            </div>

            <div id="dentistList">
                <div class="dentist-item" data-dentist="Dra. Charmaine Zapata">
                    <span>Dra. Charmaine Zapata</span>
                    <button class="select-dentist-button">Select</button>
                </div>
                <div class="dentist-item" data-dentist="Dr. Camille Fuentes">
                    <span>Dr. Camille Fuentes</span>
                    <button class="select-dentist-button">Select</button>
                </div>
            </div>

            <h2>Select Your Treatment</h2>

            <div id="treatmentList">
                <div class="treatment-item" data-treatment="Teeth Cleaning" data-price="500">
                    <span>Teeth Cleaning - ₱500</span>
                    <button class="select-treatment-button">Select</button>
                </div>
                <div class="treatment-item" data-treatment="Cavity Filling" data-price="800">
                    <span>Cavity Filling - ₱800</span>
                    <button class="select-treatment-button">Select</button>
                </div>
            </div>

            <form id="appointmentForm" method="POST" action="patDashboard.php">
                <div id="summary">
                    <h3>Summary of Selection</h3>
                    <p id="selectedDate">Selected Date: <span
                            id="dateValue"><?php echo htmlspecialchars($appointmentDate); ?></span></p>
                    <p id="selectedTime">Selected Time: <span
                            id="timeValue"><?php echo htmlspecialchars($appointmentTime); ?></span></p>
                    <p id="selectedDentist">Selected Dentist: <span
                            id="dentistValue"><?php echo htmlspecialchars($dentistName); ?></span></p>
                    <p id="selectedTreatment">Selected Treatment: <span
                            id="treatmentValue"><?php echo htmlspecialchars($treatment); ?></span></p>
                </div>

                <input type="hidden" name="appointmentDate" value="<?php echo htmlspecialchars($appointmentDate); ?>">
                <input type="hidden" name="appointmentTime" value="<?php echo htmlspecialchars($appointmentTime); ?>">
                <input type="hidden" name="dentistName" value="<?php echo htmlspecialchars($dentistName); ?>">
                <input type="hidden" name="treatment" value="<?php echo htmlspecialchars($treatment); ?>">

                <button type="submit" name="confirmButton" id="confirmButton">Confirm Appointment</button>
            </form>
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

        let selectedDentist = '';
        let selectedTreatment = '';

        document.querySelectorAll('.select-dentist-button').forEach(button => {
            button.addEventListener('click', function () {
                selectedDentist = this.parentElement.getAttribute('data-dentist');
                document.getElementById('dentistValue').textContent = selectedDentist;

                // Set hidden input value
                document.querySelector('input[name="dentistName"]').value = selectedDentist;
            });
        });

        document.querySelectorAll('.select-treatment-button').forEach(button => {
            button.addEventListener('click', function () {
                selectedTreatment = this.parentElement.getAttribute('data-treatment');
                const price = this.parentElement.getAttribute('data-price');
                document.getElementById('treatmentValue').textContent = `${selectedTreatment} - ${price}`;

                // Set hidden input value
                document.querySelector('input[name="treatment"]').value = selectedTreatment;
            });
        });

        // Confirm button functionality
        document.getElementById('confirmButton').addEventListener('click', function () {
            if (selectedDentist && selectedTreatment) {
                alert(`Appointment confirmed with ${selectedDentist} for ${selectedTreatment}.`);
            } else {
                alert("Please select both a dentist and a treatment before confirming.");
            }
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

        document.getElementById('backButton').onclick = function () {
            window.location.href = 'patCalendar.php';
        };
    </script>

</body>

</html>