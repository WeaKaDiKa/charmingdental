<?php
require_once '../db/config.php';
$type = isset($_GET['type']) ? $_GET['type'] : 'active';
// Handle user registration
if (isset($_POST['signup'])) {
    // Get form data and sanitize inputs
    $firstName = mysqli_real_escape_string($db, trim($_POST['first-name']));
    $middleName = mysqli_real_escape_string($db, trim($_POST['middle-name']));
    $lastName = mysqli_real_escape_string($db, trim($_POST['last-name']));
    $address = mysqli_real_escape_string($db, trim($_POST['address']));
    $birthdate = mysqli_real_escape_string($db, $_POST['birthdate']);
    $gender = mysqli_real_escape_string($db, $_POST['gender']);
    $mobile = mysqli_real_escape_string($db, trim($_POST['mobile']));
    $email = mysqli_real_escape_string($db, trim($_POST['email']));
    $username = mysqli_real_escape_string($db, trim($_POST['username']));
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($db, $_POST['confirm-password']);
    $usertype = mysqli_real_escape_string($db, $_POST['usertype']);

    // Enhanced validation
    $errors = array();

    // Required fields validation
    if (empty($firstName))
        $errors[] = "First name is required";
    if (empty($lastName))
        $errors[] = "Last name is required";
    if (empty($username))
        $errors[] = "Username is required";
    if (empty($email))
        $errors[] = "Email is required";
    if (empty($password))
        $errors[] = "Password is required";
    if (empty($usertype))
        $errors[] = "User type is required";

    // Email format validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Password validation
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }
    }

    // Mobile number validation
    if (!empty($mobile)) {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) !== 10 && strlen($mobile) !== 11) {
            $errors[] = "Invalid mobile number format";
        }
    }

    // Check if username or email already exists
    $check_query = "SELECT * FROM users_employee WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if ($user['username'] === $username) {
            $errors[] = "Username already exists";
        }
        if ($user['email'] === $email) {
            $errors[] = "Email already exists";
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = 1; // 1 for active user
        $created_at = date('Y-m-d H:i:s');

        $insert_query = "INSERT INTO users_employee (first_name, middle_name, last_name, 
                        address, birthdate, gender, mobile, email, username, password, 
                        usertype, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($db, $insert_query);
        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssssss",
            $firstName,
            $middleName,
            $lastName,
            $address,
            $birthdate,
            $gender,
            $mobile,
            $email,
            $username,
            $hashed_password,
            $usertype,
            $status,
            $created_at
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "New user successfully created";
            header('Location: recepManagement.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($db);
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

// Fetch all users
$query = "SELECT id, CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name, 
          username, email, mobile, created_at, usertype, 'employee' as user_table 
          FROM users_employee
          UNION ALL 
          SELECT id, CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name, 
          username, email, mobile, created_at, usertype, 'client' as user_table 
          FROM users";
$result = mysqli_query($db, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> <!-- For current_timezone -->
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="adminStyles.css">

</head>
<style>
    /* General Body Styling */
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        color: #5C3A31;
        /* Brown text color */
        background-color: #f9f1f1;
        /* Light pink background */
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
        /* Semi-transparent black */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #c44a4a;
        border-radius: 10px;
        width: 50%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Close Button */
    .close-button {
        color: #c44a4a;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-button:hover,
    .close-button:focus {
        color: #872f2f;
        text-decoration: none;
    }

    /* Form Styling */
    .modal-content form .input-group {
        margin-bottom: 15px;
    }

    .modal-content form .input-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .modal-content form .input-group input,
    .modal-content form .input-group select {
        width: 97%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    .modal-content form button {
        background-color: #c44a4a;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .modal-content form button:hover {
        background-color: #a63c3c;
    }

    /* Form Styles */
    label {

        margin-bottom: 5px;
        color: #5C3A31;
        /* Brown label color */
    }
/* 
    input[type="text"],
    input[type="password"] */
 /*    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
    } */

    button[type="button"] {
        background-color: #da9393;
        /* Light pink for button */
        color: white;
        /* White text */
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        /* Smooth transition */
    }

    button[type="button"]:hover {
        background-color: #ea7575;
        /* Darker pink on hover */
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

    /* User status styles */
    .action-button.disable {
        background-color: #dc3545;
        color: white;
    }

    .action-button.enable {
        background-color: #28a745;
        color: white;
    }

    .disabled-user {
        background-color: rgba(0, 0, 0, 0.05);
        color: #6c757d;
    }

    .disabled-user td {
        opacity: 0.7;
    }

    .action-button {
        padding: 6px 12px;
        margin: 2px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .action-button:hover {
        opacity: 0.9;
    }

    /* Styles for the Add User button */
    .add-user-button {
        background-color: #c44a4a;
        /* Match the pinkish-red theme */
        color: white;
        /* Ensure text is readable */
        border: none;
        border-radius: 5px;
        padding: 8px 16px;
        font-size: 14px;
        cursor: pointer;
        margin-left: 10px;
        /* Add some spacing from the header */
        transition: background-color 0.3s ease;
    }

    .add-user-button:hover {
        background-color: #a63c3c;
        /* Slightly darker shade on hover */
    }

    .add-user-button:active {
        background-color: #872f2f;
        /* Even darker shade when clicked */
    }

    .archive-button {
        background-color: #4A332F;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .archive-button:hover {
        background-color: #5A433F;
        transition: background-color 0.2s ease;
    }

    .pagination {
        margin-top: 20px;
        /* Adds space between the table and pagination */
        text-align: center;
        /* Centers the pagination controls */
    }

    .pagination a {
        text-decoration: none;
        padding: 10px 15px;
        margin: 0 5px;
        background-color: #f8d7da;
        color: #721c24;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .pagination a.active {
        background-color: #f1948a;
        color: white;
        font-weight: bold;
    }

    .pagination a:hover {
        background-color: #fdecea;
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

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Sidebar -->
        <?php
        $navactive = "recepManagement";

        require_once "../db/nav.php" ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="user-management">
                <h2>User Management
                    <button class="add-user-button" onclick="openAddUserModal()">+ Add User</button>
                    <!-- <button class="archive-button">Archives</button> -->
                </h2>
                <!-- Add User Modal -->
                <div id="addUserModal" class="modal">
                    <div class="modal-content">
                        <span class="close-button" onclick="closeAddUserModal()">&times;</span>
                        <h2>Add New User</h2>
                        <form action="recepManagement.php" method="post">
                            <div class="row">
                                <div class="input-group">
                                    <label for="first-name">First Name:</label>
                                    <input type="text" id="first-name" name="first-name"
                                        placeholder="Enter your name..." required>
                                </div>
                                <div class="input-group">
                                    <label for="middle-name">Middle Name:</label>
                                    <input type="text" id="middle-name" name="middle-name"
                                        placeholder="Enter your name...">
                                </div>
                                <div class="input-group">
                                    <label for="last-name">Last Name:</label>
                                    <input type="text" id="last-name" name="last-name" placeholder="Enter your name..."
                                        required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-group">
                                    <label for="address">Address:</label>
                                    <input type="text" id="address" name="address" placeholder="Enter your address..."
                                        required>
                                </div>
                                <div class="input-group">
                                    <label for="birthdate">Birthdate:</label>
                                    <input type="date" id="birthdate" name="birthdate" required>
                                </div>
                                <div class="input-group">
                                    <label for="gender">Gender:</label>
                                    <select id="gender" name="gender" required>
                                        <option value="">Select your gender...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-group">
                                    <label for="mobile">Mobile No.:</label>
                                    <input type="text" id="mobile" name="mobile" placeholder="+63- 912 345 6789"
                                        required>
                                </div>
                                <div class="input-group">
                                    <label for="email">Email Address:</label>
                                    <input type="email" id="email" name="email"
                                        placeholder="Enter your email address..." required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-group">
                                    <label for="username">Username:</label>
                                    <input type="text" id="username" name="username" placeholder="Enter your username"
                                        required>
                                </div>
                                <div class="input-group">
                                    <label for="password">Password:</label>
                                    <input type="password" id="password" name="password"
                                        placeholder="Enter your password" required>
                                </div>
                                <div class="input-group">
                                    <label for="confirm-password">Confirm Password:</label>
                                    <input type="password" id="confirm-password" name="confirm-password"
                                        placeholder="Confirm your password" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-group">
                                    <label for="usertype">User Type:</label>
                                    <select id="usertype" name="usertype" required>
                                        <option value="">Select User Type</option>
                                        <option value="dentist">Dentist</option>
                                        <option value="clinic_receptionist">Clinic Receptionist</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="signup">Add User</button>
                        </form>
                    </div>
                </div>
                <!-- Main User Table -->
                <div class="user-table">
                    <table id="userTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Contact No.</th>
                                <th>Date Created</th>
                                <th>User Type</th>
                                <!-- <th>Action</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($row['usertype']); ?></td>
                                    <!-- <td>
                                    <button class="action-button archive">Archive</button>
                                </td> -->
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <script>
                        $(document).ready(function () {
                            $('#userTable').DataTable({
                                responsive: true,
                                language: {
                                    searchPlaceholder: "Search users...",
                                    lengthMenu: "Show _MENU_ users",
                                    zeroRecords: "No matching users found",
                                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                                    infoEmpty: "No users available",
                                    infoFiltered: "(filtered from _MAX_ total users)"
                                }
                            });
                        });
                    </script>
                    <div class="pagination" id="paginationContainer"></div>
                </div>
                <!-- Close the database connection -->
                <?php mysqli_close($db); ?>
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
        document.addEventListener('DOMContentLoaded', () => {
            const rowsPerPage = 10; // Updated to display 10 rows per page
            const table = document.getElementById('userTable');
            const tbody = table.querySelector('tbody');
            const paginationContainer = document.getElementById('paginationContainer');
            const rows = tbody.querySelectorAll('tr');
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);

            function displayPage(page) {
                rows.forEach((row, index) => {
                    row.style.display = 'none';
                });

                const start = (page - 1) * rowsPerPage;
                const end = Math.min(start + rowsPerPage, totalRows);

                for (let i = start; i < end; i++) {
                    rows[i].style.display = '';
                }
            }

            function createPagination() {
                paginationContainer.innerHTML = '';
                for (let i = 1; i <= totalPages; i++) {
                    const link = document.createElement('a');
                    link.href = '#';
                    link.textContent = i;
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        document.querySelectorAll('.pagination a').forEach(a => a.classList.remove('active'));
                        link.classList.add('active');
                        displayPage(i);
                    });

                    if (i === 1) {
                        link.classList.add('active');
                    }

                    paginationContainer.appendChild(link);
                }
            }

            createPagination();
            displayPage(1); // Display the first page initially
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

        function openAddUserModal() {
            document.getElementById("addUserModal").style.display = "block";
        }

        function closeAddUserModal() {
            document.getElementById("addUserModal").style.display = "none";
        }

        // Close modal if clicked outside
        window.onclick = function (event) {
            var modal = document.getElementById("addUserModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>

</html>