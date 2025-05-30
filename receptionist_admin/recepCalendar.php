<?php
require_once '../db/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="adminStyles.css">
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
    <script src="recepScript.js" defer></script>
</head>
<style>
    /* Center the calendar horizontally and vertically */
    body {
        background-color: #d99e9e;
    }

    #calendar {
        max-width: none;
        width: 81%;
        margin: 0 auto;
        margin-top: 1px;
        margin-left: 16.28%;
        justify-content: center;
    }

    .container {
        padding: none;
    }


    @media (max-width: 768px) {
        #calendar {
            width: 100%;
            padding: 10px;
        }
    }

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
     .legend {
        display: flex;
        flex-direction: column; 
        gap: 10px;
        margin: 20px 0 0 0;    
        padding: 0 16px;        
        width: 100%;            
        box-sizing: border-box;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        display: inline-block;
    }
    /* Match your appointment colors */
    .legend-danger {
        background-color: #dc3545;
        border: 1px solid #dc3545;
    }
    .legend-primary {
        background-color: #007bff;
        border: 1px solid #007bff;
    }
    .legend-secondary {
        background-color: #6c757d;
        border: 1px solid #6c757d;
    }
    .legend-task {
        background-color: rgb(0, 133, 22);
        border: 1px solid rgb(0, 133, 22);
    }
</style>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper">
        <!-- Sidebar Menu -->
        <?php
        $navactive = "recepCalendar";

        require_once "../db/nav.php" ?>

        <div class="container w-100">
            <div class="legend"></div>
            <div id="calendar" class="w-100"></div>
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
    <?php

    $events = [];
    $sql = "SELECT patient_name, treatment, appointment_time, appointment_date, status FROM approved_requests";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Extract time range
            list($start_time, $end_time) = explode(' - ', $row['appointment_time']);

            // Convert time to 24-hour format
            $startDateTime = date('Y-m-d\TH:i:s', strtotime($row['appointment_date'] . ' ' . $start_time));
            $endDateTime = date('Y-m-d\TH:i:s', strtotime($row['appointment_date'] . ' ' . $end_time));

            // Determine event class based on status
            $eventClass = "fc-event-primary"; // Default: upcoming
            if ($row['status'] === 'cancelled') {
                $eventClass = "fc-event-danger";
            } elseif (strtolower($row['status']) === 'completed') {
                $eventClass = "fc-event-secondary";
            }

            // Add event to array
            $events[] = [
                'title' => $row['patient_name'] . ' - ' . $row['treatment'],
                'start' => $startDateTime,
                'end' => $endDateTime,
                'description' => $row['treatment'],
                'className' => $eventClass
            ];
        }
    }

    // Convert PHP array to JSON for JavaScript
    
    ?>

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

        $(document).ready(function () {
            var calendar = $('#calendar').fullCalendar({
                themeSystem: 'bootstrap5',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: <?= json_encode($events); ?>,

                select: function (start, end, allDay) {
                    if (title) {
                        var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                        var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
                    }
                }, editable: false
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var dropdownButtons = document.querySelectorAll('.dropdown-btn');

            dropdownButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    this.classList.toggle('active');
                    var dropdownContainer = this.nextElementSibling;
                    if (dropdownContainer.style.display === 'block') {
                        dropdownContainer.style.display = 'none';
                    } else {
                        dropdownContainer.style.display = 'block';
                    }
                });
            });
        });

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