<?php
require_once '../db/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dentist Dashboard</title>
    <link rel="stylesheet" href="denCalendar.css">
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
    #calendar {
        max-width: none;
        width: 83.55%;
        margin: 0 auto;
        margin-top: 1px;
        margin-left: 16.28%;
        justify-content: center;
        position: absolute;
    }

    .container {
        height: 100%;
        margin: 0;
        padding: 0;
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

    /* Add this to your denCalendar.css file */
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 5px;
        position: relative;
    }

    .modal-header {
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
        margin-bottom: 15px;
    }

    .modal-title {
        margin: 0;
        font-size: 1.25rem;
    }

    .close-button {
        position: absolute;
        right: 20px;
        top: 15px;
        font-size: 20px;
        cursor: pointer;
        background: none;
        border: none;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .modal-footer {
        padding-top: 15px;
        border-top: 1px solid #ddd;
        text-align: right;
    }

    .btn {
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        margin-left: 10px;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }

    .btn:hover {
        opacity: 0.9;
    }

    /* Delete Modal Specific Styles */
    .delete-modal .modal-content {
        max-width: 400px;
    }

    .delete-modal .modal-body {
        padding: 20px 0;
        text-align: center;
    }

    /* Notification Toast Styles */
    .notification-modal {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #fff;
        padding: 15px 25px;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    }

    .notification-modal.success {
        border-left: 4px solid #28a745;
    }

    .notification-modal.error {
        border-left: 4px solid #dc3545;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
    }

    .top-header img {
        width: 55px;
        height: 50px;
        margin-right: 10px;
        border-radius: 20%;
    }
</style>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper">
        <!-- Sidebar Menu -->
        <?php
        $navactive = "denCalendar";

        require_once "../db/nav.php" ?>
        <div class="container">
            <div id="calendar"></div>
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
    <div id="taskModal" class="custom-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <div class="form-group">
                        <label for="taskTitle">Task Title and Description</label>
                        <textarea type="text" class="form-control" id="taskTitle" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelTask">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTask">Save Task</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal delete-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="close-button">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this task?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification" class="notification-modal">
        <span id="notificationMessage"></span>
    </div>
    <?php

    $events = [];
    $sql = "SELECT id,patient_name, treatment, appointment_time, appointment_date, status FROM approved_requests";
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
                'id' => "0",
                'title' => $row['patient_name'] . ' - ' . $row['treatment'],
                'start' => $startDateTime,
                'end' => $endDateTime,
                'className' => $eventClass,
                'resourceEditable' => false
            ];
        }
    }
    require_once "../db/load.php";
    $data = readevents($db_hostname, $db_database, $db_username, $db_password);

    foreach ($data as $datum) {
        $events[] = $datum;
    }
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
            // Notification function
            function showNotification(message, type = 'success') {
                const notification = $('#notification');
                notification.removeClass('success error').addClass(type);
                $('#notificationMessage').text(message);
                notification.show();

                // Hide notification after 3 seconds
                setTimeout(function () {
                    notification.css('animation', 'fadeOut 0.3s ease-out');
                    setTimeout(function () {
                        notification.hide();
                        notification.css('animation', 'slideIn 0.3s ease-out');
                    }, 300);
                }, 3000);
            }

            var calendar = $('#calendar').fullCalendar({
                editable: true,
                themeSystem: 'bootstrap5',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: <?= json_encode($events); ?>,
                selectable: true,
                selectHelper: true,
                select: function (start, end, allDay) {
                    window.selectedStart = start;
                    window.selectedEnd = end;

                    // Clear previous input
                    $('#taskTitle').val('');
                    $('#taskDescription').val('');

                    // Show add task modal
                    $('#taskModal').show();
                },
                eventResize: function (event) {
                    var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                    var title = event.title;
                    var id = event.id;
                    $.ajax({
                        url: "../db/update.php",
                        type: "POST",
                        data: { title: title, start: start, end: end, id: id },
                        success: function () {
                            $('#calendar').fullCalendar('refetchEvents');
                            showNotification('Task updated successfully');
                            setTimeout(function () {
                                location.reload();
                            }, 1000); // Delay reload to allow UI updates
                        },
                        error: function () {
                            showNotification('Error updating task', 'error');
                        }
                    });
                },
                eventDrop: function (event) {
                    var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                    var title = event.title;
                    var id = event.id;
                    $.ajax({
                        url: "../db/update.php",
                        type: "POST",
                        data: { title: title, start: start, end: end, id: id },
                        success: function () {
                            $('#calendar').fullCalendar('refetchEvents');
                            showNotification('Task updated successfully');
                            setTimeout(function () {
                                location.reload();
                            }, 1000); // Delay reload to allow UI updates
                        },
                        error: function () {
                            showNotification('Error updating task', 'error');
                        }
                    });
                },
                eventClick: function (event) {
                    window.eventToDelete = event;
                    $('#deleteModal').show();
                }
            });

            // Handle save button click in add task modal
            $('#saveTask').click(function () {
                var title = $('#taskTitle').val();
                var description = $('#taskDescription').val();

                if (title) {
                    var start = $.fullCalendar.formatDate(window.selectedStart, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(window.selectedEnd, "Y-MM-DD HH:mm:ss");

                    $.ajax({
                        url: "../db/insert.php",
                        type: "POST",
                        data: {
                            title: title,
                            description: description,
                            start: start,
                            end: end
                        },
                        success: function () {
                            $('#calendar').fullCalendar('refetchEvents');
                            $('#taskModal').hide();
                            showNotification('Task added successfully');
                            setTimeout(function () {
                                location.reload();
                            }, 1000); // Delay reload to allow UI updates
                        },
                        error: function () {
                            showNotification('Error adding task', 'error');
                        }
                    });
                } else {
                    showNotification('Please enter a task title and description', 'error');
                }
            });

            // Handle delete confirmation
            $('#confirmDelete').click(function () {
                var id = window.eventToDelete.id;
                $.ajax({
                    url: "../db/delete.php",
                    type: "POST",
                    data: { id: id },
                    success: function () {
                        $('#calendar').fullCalendar('refetchEvents');
                        $('#deleteModal').hide();
                        showNotification('Task removed successfully');
                        setTimeout(function () {
                            location.reload();
                        }, 1000); // Delay reload to allow UI updates
                    },
                    error: function () {
                        showNotification('Error removing task', 'error');
                    }
                });
            });

            // Close modals with close buttons
            $('.close-button, #cancelTask').click(function () {
                $('#taskModal').hide();
            });

            $('.close-button, #cancelDelete').click(function () {
                $('#deleteModal').hide();
            });

            // Close modals when clicking outside
            $(window).click(function (event) {
                if ($(event.target).is('#taskModal')) {
                    $('#taskModal').hide();
                }
                if ($(event.target).is('#deleteModal')) {
                    $('#deleteModal').hide();
                }
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