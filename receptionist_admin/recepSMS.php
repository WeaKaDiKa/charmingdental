<?php
require_once '../db/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="adminStyles.css">

    <style>
        .user-management {
            padding: 2rem;
        }

        .user-management h2 {
            color: #8B4513;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .sms-description {
            display: flex;
            justify-content: center;
            /* Centers horizontally */
            margin-bottom: 30px;
            background: white;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 95%;
            margin: auto;
            /* Ensures the container itself is centered in the parent */
        }

        .sms-description h4 {
            font-size: 32px;
            /* Reduce the font size */
            margin: 0;
            /* Remove extra margin around the text */
            line-height: 1.2;
            /* Adjust line height for better compactness */
            color: #965757;
        }

        .form-container {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .form-box {
            margin-top: 30px;
            background: white;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .form-box h3 {
            color: #965757;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #8B4513;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 95%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .send-btn {
            display: flex;
            margin-top: 1rem;
            align-self: center;
            background: #007bff;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            justify-content: center;
        }

        .send-btn:hover {
            background: #0056b3;
        }


        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-content button {
            margin-top: 20px;
            padding: 10px 30px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal-content button:hover {
            background-color: #45a049;
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
    </style>

</head>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Sidebar -->
        <?php
        $navactive = "recepSMS";

        require_once "../db/nav.php" ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between my-3">
                        <h4>Email Schedule</h4>
                        <button class="btn btn-sm btn-primary" id="addScheduleBtn">Add Schedule</button>
                    </div>

                    <div class="overflow-x-scroll">

                        <table id="emailschedTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient Name</th>
                                    <th>Frequency</th>
                                    <th>Message</th>
                                    <th>Start</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "
            SELECT emailsched.id, users.first_name, users.last_name, emailsched.frequency, emailsched.message, emailsched.start 
            FROM emailsched 
            JOIN users ON users.id = emailsched.patientid 
            ORDER BY emailsched.start DESC
        ";
                                $result = $db->query($query);

                                if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?= $row['id']; ?></td>
                                            <td><?= $row['first_name'] . ' ' . $row['last_name']; ?></td>
                                            <td><?= ucfirst($row['frequency']); ?></td>
                                            <td><?= $row['message']; ?></td>
                                            <td><?= $row['start']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-btn" data-id="<?= $row['id']; ?>"
                                                    data-frequency="<?= $row['frequency']; ?>"
                                                    data-message="<?= $row['message']; ?>" data-start="<?= $row['start']; ?>">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                    endwhile;
                                    ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <script>
                            $(document).ready(function () {
                                $('#emailschedTable').DataTable({
                                    responsive: true,
                                    pageLength: 10,
                                    ordering: true,
                                    columnDefs: [
                                        { orderable: false, targets: 5 } // Disable sorting on 'Actions' column
                                    ]
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Email Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addScheduleForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Patient</label>
                            <select name="patientid" id="patient-select" class="form-select" required>
                                <option value="">Select a patient</option>
                                <?php
                                include 'db.php';
                                $query = "SELECT id, first_name, last_name FROM users ORDER BY first_name ASC";
                                $result = $db->query($query);
                                while ($row = $result->fetch_assoc()):
                                    ?>
                                    <option value="<?= $row['id']; ?>"><?= $row['first_name'] . ' ' . $row['last_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" id="frequency-select" class="form-select" required>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="3months">Every 3 Months</option>
                                <option value="6months">Every 6 Months</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Date & Time</label>
                            <input type="date" name="start" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Email Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" id="edit-frequency" class="form-select" required>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="3months">Every 3 Months</option>
                                <option value="6months">Every 6 Months</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" id="edit-message" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start</label>
                            <input type="date" name="start" id="edit-start" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record?
                    <input type="hidden" id="delete-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Open Edit Modal and Populate Data
            // Show the Add Schedule Modal

            document.getElementById("addScheduleBtn").addEventListener("click", function () {
                let addModal = new bootstrap.Modal(document.getElementById("addScheduleModal"));
                addModal.show();
            });

            // Handle Form Submission
            document.getElementById("addScheduleForm").addEventListener("submit", function (event) {
                event.preventDefault();
                let formData = new FormData(this);

                fetch("add_emailsched.php", {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload();
                        }
                    });
            });
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function () {
                    document.getElementById("edit-id").value = this.getAttribute("data-id");
                    document.getElementById("edit-frequency").value = this.getAttribute("data-frequency");
                    document.getElementById("edit-message").value = this.getAttribute("data-message");
                    document.getElementById("edit-start").value = this.getAttribute("data-start");

                    let editModal = new bootstrap.Modal(document.getElementById("editModal"));
                    editModal.show();
                });
            });

            // Handle Edit Form Submission
            document.getElementById("editForm").addEventListener("submit", function (event) {
                event.preventDefault();
                let formData = new FormData(this);

                fetch("update_emailsched.php", {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload();
                        }
                    });
            });

            // Open Delete Modal
            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", function () {
                    document.getElementById("delete-id").value = this.getAttribute("data-id");
                    let deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
                    deleteModal.show();
                });
            });

            // Handle Delete Confirmation
            document.getElementById("confirm-delete").addEventListener("click", function () {
                let id = document.getElementById("delete-id").value;

                fetch("delete_emailsched.php?id=" + id, {
                    method: "GET"
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload();
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


        // Function to close the success modal
        function closeModal() {
            $('#successModal').css('display', 'none');
        }

        // Close modal when clicking outside
        $(window).click(function (event) {
            var modal = $('#successModal')[0];
            if (event.target == modal) {
                closeModal();
            }
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