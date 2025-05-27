<?php
require_once '../db/config.php';
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Determine if we're viewing active or archived payments
$type = isset($_GET['type']) && $_GET['type'] === 'archived' ? 'archived' : 'active';
$archived = $type === 'archived' ? 1 : 0;

// Sort and pagination logic
$order = isset($_GET['order']) && $_GET['order'] === 'oldest' ? 'ASC' : 'DESC';
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]) : 1;
$offset = ($page - 1) * $limit;

// Fetch total count of records (active or archived)
$total_query = "SELECT COUNT(*) as total FROM payment WHERE archived = $archived";
$total_result = mysqli_query($db, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Query to fetch payments
$query = "SELECT id, name, date, treatment, price, status, archived, created_at 
          FROM payment 
          WHERE archived = $archived 
          ORDER BY created_at $order 
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
    <title><?php echo $type === 'archived' ? 'Archived Payments' : 'Active Payments'; ?></title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="adminStyles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/myjs.js"></script>
    <style>
        /* Add your custom CSS styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead {
            background-color: #f8d7da;
        }

        table th,
        table td {
            text-align: left;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ddd;
        }

        table th {
            color: #721c24;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #fdf2f4;
        }

        table tr:nth-child(odd) {
            background-color: #fff;
        }

        table tr:hover {
            background-color: #fdecea;
        }

        .view-btn {
            background-color: #da9393;
            color: white;
            border: none;
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .view-btn:hover {
            background-color: #f8b7b1;
        }

        .add-payment {
            background-color: #f8b7b1;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-payment:hover {
            background-color: #f1948a;
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

        .sort {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .sort label {
            margin-right: 10px;
        }

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

        .top-header img {
            width: 55px;
            height: 50px;
            margin-right: 10px;
            border-radius: 20%;
        }

        .custom-dropdown {
            width: 200px;
            padding: 6px;
            max-width: 400px;
            /* Adjust the max width as needed */
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: white;
            cursor: pointer;
            appearance: none;
            /* Removes default browser styling */
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        /* Ensure all select elements inside form have the same styling */
        .form-group select {
            width: 100%;
            max-width: 400px;
            /* Adjust this value based on your design */
        }

        /* Add arrow indicator */
        .custom-dropdown {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="gray" d="M7 10l5 5 5-5H7z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }

        /* Style the dropdown on focus */
        .custom-dropdown:focus {
            border-color: #007bff;
            outline: none;
        }

        /* Adjust the dropdown list */
        .custom-dropdown option {
            font-size: 16px;
            padding: 10px;
        }
    </style>
</head>

<body>
    <?php require_once "../db/header.php" ?>
    <div class="main-wrapper">
        <?php
        $navactive = "recepPayment";

        require_once "../db/nav.php" ?>
        <div class="main-content">
            <div class="user-management">
                <header>
                    <h1>Manage Payments</h1>
                    <!-- <h2><?php echo $type === 'archived' ? 'Archived Payments' : 'Active Payments'; ?></h2> -->
                    <!-- <div class="sort">
                        <label for="sort">Sort by:</label>
                        <select id="sort" onchange="sortPayments()">
                            <option value="Newest" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Newest</option>
                            <option value="Oldest" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Oldest</option>
                        </select>
                    </div> -->
                    <?php if ($type === 'active'): ?>
                        <button class="add-payment" id="openAddPaymentModal">+ Add Payment</button>
                    <?php endif; ?>
                    <!-- <button class="add-payment" onclick="toggleView('<?php echo $type === 'archived' ? 'active' : 'archived'; ?>')">
                        View <?php echo $type === 'archived' ? 'Active' : 'Archived'; ?> Payments
                    </button> -->
                </header>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Treatment and Price (₱)</th>
                            <!-- <th>Price</th> -->
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="paymentTableBody">
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr data-id="<?php echo $row['id']; ?>">
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['treatment']); ?></td>
                                <!-- <td><?php echo '₱' . htmlspecialchars($row['price']); ?></td> -->
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <?php if ($archived): ?>
                                        <button class="view-btn unarchive-btn"
                                            data-id="<?php echo $row['id']; ?>">Unarchive</button>
                                    <?php else: ?>
                                        <button type="button" class="view-btn editbtn">Edit</button>
                                        <!-- <button class="view-btn archive-btn" data-id="<?php echo $row['id']; ?>">Archive</button> -->
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="recepPayment.php?page=<?php echo $i; ?>&order=<?php echo $order; ?>&type=<?php echo $type; ?>"
                            class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
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
    <!-- Add Payment Modal -->
    <div class="modal" id="addPaymentModal">
        <div class="modal-content">
            <form id="paymentForm" method="POST">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" placeholder="Name" id="name" autocomplete="name" required>
                </div>

                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" required>
                </div>

                <div class="form-group">
                    <label for="treatment">Treatment:</label>
                    <input type="text" placeholder="Treatment" id="treatment" required>
                </div>

                <!-- <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" placeholder="Price" id="price" required>
                </div> -->

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" required>
                        <option value="">Select Status</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>

                <button type="submit" id="btn-add" method="POST">Add Payment</button>
                <button type="button" class="close-modal">Close</button>
            </form>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal" id="editmodal">
        <div class="modal-content">
            <form action="../includes/updatecode.php" method="POST">
                <input type="hidden" name="update_id" id="edit_id">

                <div class="form-group">
                    <label for="edit_name">Name:</label>
                    <input type="text" name="name" placeholder="Name" id="edit_name" autocomplete="name" required>
                </div>

                <div class="form-group">
                    <label for="edit_date">Date:</label>
                    <input type="date" name="date" id="edit_date" required>
                </div>

                <div class="form-group">
                    <label for="edit_treatment">Treatment:</label>
                    <input type="text" name="treatment" placeholder="Treatment" id="edit_treatment" required>
                </div>

                <!-- <div class="form-group">
                    <label for="edit_price">Price:</label>
                    <input type="number" name="price" placeholder="Price" id="edit_price" required>
                </div> -->

                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select name="status" id="edit_status" required>
                        <option value="">Select Status</option>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>

                <button type="submit" name="updatedata">Update</button>
                <button type="button" class="close-modal">Close</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Add dialog HTML to the page
            $('body').append(`
                <div id="unarchive-dialog" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; min-width: 300px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3 style="margin: 0 0 15px 0;">Confirm Unarchive</h3>
                        <p style="margin: 0 0 20px 0;">Are you sure you want to unarchive this payment?</p>
                        <div style="text-align: right;">
                            <button id="cancel-unarchive" style="margin-right: 10px; padding: 8px 15px; border: none; background: #e0e0e0; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button id="confirm-unarchive" style="padding: 8px 15px; border: none; background: #3b82f6; color: white; border-radius: 4px; cursor: pointer;">OK</button>
                        </div>
                    </div>
                </div>
            `);

            // Handle unarchive button click
            $(document).ready(function () {
                // Add both dialog HTMLs to the page
                $('body').append(`
                <div id="unarchive-dialog" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; min-width: 300px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3 style="margin: 0 0 15px 0;">Confirm Unarchive</h3>
                        <p style="margin: 0 0 20px 0;">Are you sure you want to unarchive this payment?</p>
                        <div style="text-align: right;">
                            <button id="cancel-unarchive" style="margin-right: 10px; padding: 8px 15px; border: none; background: #e0e0e0; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button id="confirm-unarchive" style="padding: 8px 15px; border: none; background: #3b82f6; color: white; border-radius: 4px; cursor: pointer;">OK</button>
                        </div>
                    </div>
                </div>

                <div id="archive-dialog" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; min-width: 300px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3 style="margin: 0 0 15px 0;">Confirm Archive</h3>
                        <p style="margin: 0 0 20px 0;">Are you sure you want to archive this payment?</p>
                        <div style="text-align: right;">
                            <button id="cancel-archive" style="margin-right: 10px; padding: 8px 15px; border: none; background: #e0e0e0; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button id="confirm-archive" style="padding: 8px 15px; border: none; background: #3b82f6; color: white; border-radius: 4px; cursor: pointer;">OK</button>
                        </div>
                    </div>
                </div>
            `);

                // Handle unarchive button click
                $('.unarchive-btn').on('click', function () {
                    const paymentId = $(this).data('id');
                    const dialog = $('#unarchive-dialog');

                    dialog.fadeIn(200);

                    $('#cancel-unarchive').off('click').on('click', function () {
                        dialog.fadeOut(200);
                    });

                    $('#confirm-unarchive').off('click').on('click', function () {
                        $.ajax({
                            url: '../db/unarchivePayment.php',
                            method: 'POST',
                            data: { id: paymentId },
                            success: function (response) {
                                try {
                                    if (typeof response === 'string') {
                                        response = JSON.parse(response); // Ensure JSON response
                                    }

                                    if (response.success) {
                                        location.reload(); // Auto reload on success
                                    } else {
                                        console.error('Failed to unarchive the payment:', response.message || 'Unknown error.');
                                    }
                                } catch (error) {
                                    console.error('Error parsing response:', error);
                                }
                            },
                            error: function () {
                                console.error('Error while processing the request.');
                            }
                        });
                        dialog.fadeOut(200);
                    });

                    dialog.off('click').on('click', function (e) {
                        if (e.target === this) {
                            dialog.fadeOut(200);
                        }
                    });
                });

                // Handle archive button click with new dialog
                $('.archive-btn').on('click', function () {
                    const paymentId = $(this).data('id');
                    const dialog = $('#archive-dialog');

                    dialog.fadeIn(200);

                    $('#cancel-archive').off('click').on('click', function () {
                        dialog.fadeOut(200);
                    });

                    $('#confirm-archive').off('click').on('click', function () {
                        $.ajax({
                            url: '../db/archivePayment.php',
                            method: 'POST',
                            data: { id: paymentId },
                            success: function (response) {
                                try {
                                    if (typeof response === 'string') {
                                        response = JSON.parse(response); // Ensure JSON response
                                    }

                                    if (response.success) {
                                        location.reload(); // Auto reloadS on success
                                    } else {
                                        console.error('Failed to archive the payment:', response.message || 'Unknown error.');
                                    }
                                } catch (error) {
                                    console.error('Error parsing response:', error);
                                }
                            },
                            error: function () {
                                console.error('Error while processing the request.');
                            }
                        });
                        dialog.fadeOut(200);
                    });

                    dialog.off('click').on('click', function (e) {
                        if (e.target === this) {
                            dialog.fadeOut(200);
                        }
                    });
                });
            });

            // Add Payment Modal
            $('#openAddPaymentModal').on('click', function () {
                $('#addPaymentModal').addClass('active');
            });

            // Add this to your existing script section
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
            // Edit button click
            $('.editbtn').on('click', function () {
                var row = $(this).closest('tr');
                var id = row.data('id');
                var name = row.find('td:eq(0)').text().trim();
                var date = row.find('td:eq(1)').text().trim();
                var treatment = row.find('td:eq(2)').text().trim();
                var price = row.find('td:eq(4)').text().trim().replace('₱', '').replace(',', '');
                var status = row.find('td:eq(3)').text().trim();

                $('#editmodal').addClass('active');
                $('#edit_id').val(id);
                $('#edit_name').val(name);
                $('#edit_date').val(date);
                $('#edit_treatment').val(treatment);
                $('#edit_price').val(price);
                $('#edit_status').val(status);
            });

            // Close modals
            $('.close-modal').on('click', function () {
                $('.modal').removeClass('active');
            });

            // Close modal when clicking outside
            $(document).mouseup(function (e) {
                var modal = $('.modal-content');
                if (!modal.is(e.target) && modal.has(e.target).length === 0) {
                    $('.modal').removeClass('active');
                }
            });


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

        document.addEventListener('DOMContentLoaded', function () {
            setInterval(fetchCurrentTime, 1000);
            fetchCurrentTime();
        });
        // Sort function
        function sortPayments() {
            var sortSelect = document.getElementById('sort');
            var selectedValue = sortSelect.value.toLowerCase();
            window.location.href = '?order=' + selectedValue + '&page=1&type=<?php echo $type; ?>';
        }

        // Toggle view function
        function toggleView(viewType) {
            window.location.href = '?type=' + viewType + '&order=<?php echo $order; ?>&page=1';
        }
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