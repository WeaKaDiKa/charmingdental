<?php
require_once '../db/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Charming Smile Dental Clinic</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="patStyles.css">
    <script src="AdminScript.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .payment-history {
            padding: 20px;
        }

        .payment-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .payment-header h1 {
            color: #8b4513;
            margin: 0;
            font-size: 24px;
        }

        .payment-controls {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
            padding: 0 20px;
        }

        .sort-dropdown {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #8b4513;
            background: white;
        }

        .payment-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #f8d7da;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            color: #8b4513;
            font-weight: 600;
        }

        td {
            color: #333;
        }

        .view-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .view-btn:hover {
            background-color: #0056b3;
        }

        .status {
            color: #28a745;
            font-weight: 500;
        }

        .price {
            font-weight: 500;
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
        $navactive = "patPayment";
        require_once "../db/nav.php" ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="payment-history">
                <div class="payment-header">
                    <h1>Payment History</h1>
                </div>


                <div class="payment-table">
                    <div class="payment-controls">
                        <select class="sort-dropdown">
                            <option>Sort by : Newest</option>
                            <option>Sort by : Oldest</option>
                            <option>Sort by : Price</option>
                            <option>Sort by : Name</option>
                        </select>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Appointment ID</th>
                                <th>Date</th>
                                <th>Treatment</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>A001</td>
                                <td>2024-09-02</td>
                                <td>Tooth Filling</td>
                                <td class="price">P700</td>
                                <td class="status">Paid</td>
                                <td>
                                    <button class="view-btn">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.dropdown-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    const dropdownContainer = this.nextElementSibling;
                    const isDisplayed = dropdownContainer.style.display === 'block';
                    dropdownContainer.style.display = isDisplayed ? 'none' : 'block';
                    this.classList.toggle('active', !isDisplayed);
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