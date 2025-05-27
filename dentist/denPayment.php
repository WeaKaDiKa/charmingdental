<?php

require_once '../db/config.php';

// Sort logic
$order = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$orderSQL = $order === 'oldest' ? 'ASC' : 'DESC';

// Pagination logic
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]) : 1;
$offset = ($page - 1) * $limit;

// Get total rows for pagination
$totalRowsQuery = "SELECT COUNT(*) as total FROM payment WHERE archived = 0";
$totalRowsResult = mysqli_query($db, $totalRowsQuery);

if ($totalRowsResult) {
    $totalRows = mysqli_fetch_assoc($totalRowsResult)['total'];
    $total_pages = ceil($totalRows / $limit); // Calculate total pages
} else {
    die("Error fetching total rows: " . mysqli_error($db));
}

// Query to fetch paginated payments
$query = "SELECT id, name, date, treatment, price, status 
          FROM payment 
          WHERE archived = 0 
          ORDER BY created_at $orderSQL
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($db, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($db));
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="denPayment.css">
    <?php require_once "../db/head.php" ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="recepScript.js" defer></script>
</head>
<style>
    .pagination {
        display: flex;
        justify-content: center;
        margin: 20px 0;
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

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .modal-content input,
    .modal-content select {
        display: block;
        width: 95%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .modal-content button {
        padding: 10px;
        background-color: #f8b7b1;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .modal-content button:hover {
        background-color: #f1948a;
    }

    .close-modal {
        background-color: #dc3545;
        margin-top: 10px;
    }

    .close-modal:hover {
        background-color: #c82333;
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
        $navactive = "denPayment";

        require_once "../db/nav.php" ?>

        <!-- User Table -->
        <div class="user-table">
            <div class="table-header">
                <h2>MANAGE PAYMENTS</h2>
            </div>
            <div class="payment-content">
                <div class="header-actions">
                    <!-- <button class="add-payment-btn" id="openAddPaymentModal">+ Add Payment</button> -->
                    <div class="sort-dropdown">
                        <!-- <label for="sort">Sort by: </label>
                        <select id="sort" onchange="sortPayments()">
                            <option value="newest" <?php echo $order === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="oldest" <?php echo $order === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                        </select> -->
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Treatment</th>
                            <th>Price</th>
                            <th>Status</th>
                            <!--<th>Action</th>-->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['treatment']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['price']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <!--<td>
                                    <button type="button" class="view-btn editbtn">View</button>
                                </td>-->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="denPayment.php?page=<?php echo $i; ?>&order=<?php echo $order; ?>"
                            class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
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
        <div class="modal" id="addPaymentModal">
            <div class="modal-content">
                <form id="paymentForm" method="POST">
                    <input type="text" placeholder="Name" id="name" autocomplete="name" required>
                    <input type="date" id="date" required>
                    <input type="text" placeholder="Treatment" id="treatment" required>
                    <input type="number" placeholder="Price" id="price" required>
                    <select id="status" required>
                        <option value="">Select Status</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                    <button type="submit" id="btn-add" method="POST">Add Payment</button>
                    <button type="button" class="close-modal">Close</button>
                </form>
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

            $('#openAddPaymentModal').on('click', function () {
                $('#addPaymentModal').addClass('active');
            });

            $('.close-modal').on('click', function () {
                $('.modal').removeClass('active');
            });

            $(document).ready(function () {
                // Payment form submission
                $('#paymentForm').on('submit', function (e) {
                    e.preventDefault();

                    // Get form data
                    const formData = {
                        name: $('#name').val(),
                        date: $('#date').val(),
                        treatment: $('#treatment').val(),
                        price: $('#price').val(),
                        status: $('#status').val()
                    };

                    // Send AJAX request
                    $.ajax({
                        url: '../db/addPayment.php',
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            try {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    // Clear form
                                    $('#paymentForm')[0].reset();
                                    // Close modal
                                    $('#addPaymentModal').removeClass('active');
                                    // Reload page to show new data
                                    location.reload();
                                } else {
                                    alert('Error: ' + result.message);
                                }
                            } catch (e) {
                                alert('Error processing the request');
                            }
                        },
                        error: function () {
                            alert('Error: Could not connect to the server');
                        }
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                var dropdownButtons = document.querySelectorAll('.dropdown-btn');

                dropdownButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        // Toggle active class on the button
                        this.classList.toggle('active');

                        // Find the next sibling dropdown container
                        var dropdownContainer = this.nextElementSibling;

                        // Toggle dropdown visibility
                        if (dropdownContainer.style.display === 'block') {
                            dropdownContainer.style.display = 'none';
                        } else {
                            dropdownContainer.style.display = 'block';
                        }
                    });
                });
            });

            function sortPayments() {
                var sortSelect = document.getElementById('sort');
                var selectedValue = sortSelect.value;
                window.location.href = '?sort=' + selectedValue;
            }

            // Add event listener for the edit button
            document.querySelectorAll('.editbtn').forEach(button => {
                button.addEventListener('click', function () {
                    const row = this.closest('tr');
                    const id = row.dataset.id;
                    // Add your edit functionality here
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