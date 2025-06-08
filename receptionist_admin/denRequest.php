<?php
// Database connection code at the top of your file
require_once '../db/config.php';

$query = "
SELECT 
    a.appointment_id, 
    CONCAT(u.first_name, ' ', u.last_name) AS p_name, 
    s.name AS service_name, 
    a.appointment_date, 
    CONCAT(
        DATE_FORMAT(STR_TO_DATE(a.appointment_time_start, '%H:%i:%s'), '%h:%i %p'),
        ' - ',
        DATE_FORMAT(STR_TO_DATE(a.appointment_time_end, '%H:%i:%s'), '%h:%i %p')
    ) AS appointment_time,
    u.email,
    a.transaction 
FROM appointments a
JOIN users u ON a.patient_id = u.id
JOIN services s ON a.service_id = s.id
WHERE a.status = 'submitted' || a.status = 'rescheduled'
";


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
    <title>Request Approval</title>
    <link rel="stylesheet" href="denRequest.css">
    <?php require_once "../db/head.php" ?>
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

    .btn-cancel {
        background-color: #6c757d;
        color: white;
    }

    .top-header img {
        width: 55px;
        height: 50px;
        margin-right: 10px;
        border-radius: 20%;
    }


    .btn-confirm {
        background-color: green;
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
        margin-right: 10px;
    }

    .btn-cancel {
        background-color: red;
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
    }

    /* Modal Text */
    #reject-modal-text {
        font-size: 14px;
        margin-bottom: 15px;
        color: #666;
    }

    /* Rejection Text Box */
    #rejectionReason {

        padding: 10px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 5px;
        outline: none;
        margin-bottom: 15px;
        transition: border-color 0.3s;
    }

    /* Hover and Focus for Input */
    #rejectionReason:focus {
        border-color: #d9534f;
        /* Red tone for rejection */
        box-shadow: 0 0 5px rgba(217, 83, 79, 0.5);
    }

    /* Pop-up Message Styling */
    .popup {
        display: none;
        /* Hidden by default */
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        text-align: center;
        z-index: 2000;
        width: 300px;
        animation: fadeIn 0.3s ease-in-out;
    }

    /* Pop-up Text */
    .popup p {
        font-size: 16px;
        color: #333;
        margin-bottom: 10px;
    }

    /* OK Button */
    #popupCloseBtn {
        padding: 8px 15px;
        background: #d9534f;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.3s;
    }

    #popupCloseBtn:hover {
        background: #c9302c;
    }

    /* Fade-in animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translate(-50%, -55%);
        }

        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }

    .hidden {
        display: none;
    }

    .modal {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal-content {

        background-color: white;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
</style>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper overflow-hidden">
        <!-- Sidebar Menu -->
        <?php
        $navactive = "denRequest";

        require_once "../db/nav.php" ?>
        <!-- Request Approval Content -->
        <div class="main-content overflow-hidden">
            <div class="card">
                <div class="card-body">
                    <div class="table-header">
                        <div>
                            <h2>Request Approval</h2>
                        </div>
                        <!-- <div class="search-bar">
                    <input type="text" placeholder="Search by name">
                </div> -->
                    </div>
                    <div class="overflow-x-scroll mt-3">

                        <table id="approvalTable" class="table table-striped table-bordered approval-table">
                            <thead>
                                <tr>
                                    <th>Appointment No.</th>
                                    <th>Name</th>
                                    <th>Treatment and Price (₱)</th>
                                    <th>Time</th>
                                    <th>Date</th>
                                    <th>GCash Reference</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)):
                                        $queryreceipt = "SELECT proofimg FROM downpayment WHERE appointmentid = " . $row['appointment_id'] . " LIMIT 1";
                                        $resultreceipt = mysqli_query($db, $queryreceipt);
                                        ?>
                                        <tr data-id="<?= $row['appointment_id']; ?>">
                                            <td><?= htmlspecialchars($row['appointment_id']); ?></td>
                                            <td><?= htmlspecialchars($row['p_name']); ?></td>
                                            <td><?= htmlspecialchars($row['service_name']); ?></td>
                                            <td><?= htmlspecialchars($row['appointment_time']); ?></td>
                                            <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?= htmlspecialchars($row['transaction']); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn accept-btn" onclick="acceptRequest(this)">Accept</button>
                                                    <button class="btn reject-btn" onclick="rejectRequest(this)">Reject</button>
                                                    <?php while ($downpayment = mysqli_fetch_assoc($resultreceipt)): ?>
                                                        <button class="btn btn-primary btn-view" data-bs-toggle="modal"
                                                            data-bs-target="#viewModal"
                                                            data-proofimg="../uploads/payment/<?= $downpayment['proofimg']; ?>">
                                                            View
                                                        </button>
                                                    <?php endwhile; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <script>
                            $(document).ready(function () {
                                $('#approvalTable').DataTable({
                                    responsive: true,
                                    pageLength: 10,
                                    ordering: true,
                                    columnDefs: [
                                        { orderable: false, targets: 6 } // Disable sorting on 'Action' column
                                    ]
                                });
                            });
                        </script>
                
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewMedicalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-1">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMedicalLabel">Appointment Additional Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 class="form-label">Payment Receipt</h6>
                        <div class="d-flex align-items-center justify-content-center">
                            <img id="receipt" class="w-75"></img>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".btn-view").forEach(button => {
                button.addEventListener("click", function () {
                    let proofimg = this.getAttribute("data-proofimg");

                    document.getElementById("receipt").src = proofimg;
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
    <!-- Confirm Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content w-50">
            <h3>Confirm Appointment</h3>
            <p id="modal-text"></p>
            <button id="confirmBtn" class="btn-confirm">Confirm</button>
            <button id="cancelBtn" class="btn-cancel">Cancel</button>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content  w-50">
            <h3>Reject Appointment</h3>
            <p id="reject-modal-text"></p>
            <input type="text" id="rejectionReason" placeholder="Enter reason for rejection">
            <button id="rejectConfirmBtn" class="btn-confirm">Confirm</button>
            <button id="rejectCancelBtn" class="btn-cancel">Cancel</button>
        </div>
    </div>
    <div id="popupMessage" class="popup">
        <p id="popupText"></p>
        <button id="popupCloseBtn">OK</button>
    </div>
    <script>
        let currentRow, appointmentId, patientName, treatment, appointmentTime, appointmentDate, email;

        function acceptRequest(button) {
            currentRow = button.closest('tr');
            appointmentId = currentRow.getAttribute('data-id');
            patientName = currentRow.cells[1].innerText;
            treatment = currentRow.cells[2].innerText;
            appointmentTime = currentRow.cells[3].innerText;
            appointmentDate = currentRow.cells[4].innerText;
            email = currentRow.cells[5].innerText;

            // Set modal text and display the modal
            document.getElementById('modal-text').innerText =
                `Do you want to accept this appointment?\n\nPatient: ${patientName}\nTreatment: ${treatment}\nTime: ${appointmentTime}\nDate: ${appointmentDate}`;
            document.getElementById('confirmModal').style.display = 'flex';

            // Send email when accepting appointment
            const formData = new FormData();
            formData.append('appointmentId', appointmentId);
            formData.append('patientName', patientName);
            formData.append('treatment', treatment);
            formData.append('appointmentTime', appointmentTime);
            formData.append('appointmentDate', appointmentDate);
            formData.append('email', email);

            fetch('send_approved_email.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(text => {
                    console.log('Raw response:', text);
                    return JSON.parse(text);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }


        // Wait for DOM to be fully loaded before attaching event listeners
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById('confirmBtn').addEventListener('click', function () {
                if (!appointmentId) {
                    alert('No appointment selected.');
                    return;
                }

                $.ajax({
                    url: 'accept_request.php', // Path to your PHP script
                    type: 'POST',
                    data: {
                        id: appointmentId,
                        name: patientName,
                        treatment: treatment,
                        time: appointmentTime,
                        date: appointmentDate
                    },
                    success: function (response) {
                        currentRow.remove(); // Remove the row on success
                    },
                    error: function () {
                    }
                });

                document.getElementById('confirmModal').style.display = 'none';
            });

            document.getElementById('cancelBtn').addEventListener('click', function () {
                document.getElementById('confirmModal').style.display = 'none';
            });
        });


        function rejectRequest(button) {
            console.log('rejectRequest called');
            currentRow = button.closest('tr');

            if (!currentRow) {
                console.error('Error: Unable to find parent row.');
                return;
            }

            appointmentId = currentRow.getAttribute('data-id');
            patientName = currentRow.cells[1]?.innerText || 'N/A';
            treatment = currentRow.cells[2]?.innerText || 'N/A';
            appointmentTime = currentRow.cells[3]?.innerText || 'N/A';
            appointmentDate = currentRow.cells[4]?.innerText || 'N/A';
            email = currentRow.cells[5]?.innerText || 'N/A';

            console.log('Extracted values:', {
                appointmentId,
                patientName,
                treatment,
                appointmentTime,
                appointmentDate,
                email
            });

            if (!appointmentId) {
                console.error('Error: No appointment ID found.');
                return;
            }

            if (!email || email === 'N/A') {
                console.error('Error: No valid email found.');
                showPopupMessage('Error: No valid email address found for this appointment.');
                return;
            }

            // Set modal text and display the modal
            document.getElementById('reject-modal-text').innerText =
                `Do you want to reject this appointment?
            \nPatient: ${patientName}\nTreatment: ${treatment}\nTime: ${appointmentTime}\nDate: ${appointmentDate}`;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        document.addEventListener("DOMContentLoaded", function () {
            console.log('DOM loaded');

            // Attach event listeners for the reject modal
            let rejectConfirmBtn = document.getElementById('rejectConfirmBtn');
            let rejectCancelBtn = document.getElementById('rejectCancelBtn');
            let rejectionReasonInput = document.getElementById('rejectionReason');

            if (!rejectConfirmBtn || !rejectCancelBtn || !rejectionReasonInput) {
                console.error('Error: Modal elements not found in the DOM.');
                return;
            }

            rejectConfirmBtn.addEventListener('click', function () {
                console.log('Confirm button clicked');

                if (!appointmentId) {
                    showPopupMessage('No appointment selected.');
                    return;
                }

                let rejectionReason = rejectionReasonInput.value.trim();
                if (!rejectionReason) {
                    showPopupMessage('Please enter a reason for rejection.');
                    return;
                }

                console.log('Sending rejection request with email:', email);

                // Send data to PHP script using AJAX
                $.ajax({
                    url: 'reject_request.php',
                    type: 'POST',
                    data: {
                        id: appointmentId,
                        name: patientName,
                        treatment: treatment,
                        time: appointmentTime,
                        date: appointmentDate,
                        email: email, // Added email to the request
                        reason: rejectionReason
                    },
                    success: function (response) {
                        console.log('Server response:', response);
                        try {
                            const result = JSON.parse(response);
                            if (result.status === 'success') {
                                currentRow.remove();
                                showPopupMessage('Appointment rejected and email sent successfully.');
                            } else {
                                showPopupMessage(result.message || 'Error rejecting appointment.');
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            showPopupMessage('Error processing server response.');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        showPopupMessage('Error rejecting appointment: ' + error);
                    }
                });

                // Clear the rejection reason and hide the modal
                rejectionReasonInput.value = '';
                document.getElementById('rejectModal').style.display = 'none';
            });

            rejectCancelBtn.addEventListener('click', function () {
                console.log('Cancel button clicked');
                rejectionReasonInput.value = '';
                document.getElementById('rejectModal').style.display = 'none';
            });
        });

        function showPopupMessage(message) {
            let popup = document.getElementById('popupMessage');
            let popupText = document.getElementById('popupText');
            let popupCloseBtn = document.getElementById('popupCloseBtn');

            popupText.textContent = message;
            popup.style.display = 'block';

            popupCloseBtn.onclick = function () {
                popup.style.display = 'none';
            };
        }

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