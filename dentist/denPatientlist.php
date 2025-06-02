<?php
require_once '../db/config.php';

$query = "SELECT id, CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name, emergencyname, emergencycontact,gender, mobile, email, username
          FROM users";

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
    <title>Patient List</title>
    <link rel="stylesheet" href="denPatientlist.css">
    <?php require_once "../db/head.php" ?>

    <script src="recepScript.js" defer></script>
    <?php
    if (isset($_GET['message'])) {
        if ($_GET['message'] = 'dentalsaved'): ?>
            <script>alert('Dental Chart Saved');</script>
        <?php endif;
    }
    ?>
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

    <div class="main-wrapper overflow-hidden">
        <!-- Sidebar Menu -->
        <?php
        $navactive = "denPatientlist";

        require_once "../db/nav.php" ?>
        <div class="main-content overflow-hidden">
            <div class="card">
                <div class="card-body">
                    <div class="table-header">
                        <div>
                            <h2>Patient List</h2>
                            <!-- <div class="search-bar">
                        <input type="text" placeholder="Search by name">
                    </div> -->
                        </div>
                    </div>
                    <div class="mb-3 d-flex align-items-center flex-row justify-content-center gap-2">
                        <button class="btn btn-success btn-sm me-2" onclick="printUsersTable()">Print</button>
                        <button class="btn btn-secondary btn-sm" onclick="saveUsersTableToPDF()">Save to PDF</button>
                    </div>
                    <div class="overflow-x-scroll d-flex">
                        <table id="usersTable" class="display">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Phone No.</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Emergency Name</th>
                                    <th>Emergency Phone</th>
                                    <th class="no-print">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)):
                                    $querymedical = "SELECT * FROM medical WHERE usersid = " . $row['id'] . " ORDER BY dateuploaded DESC LIMIT 1";
                                    $resultmedical = mysqli_query($db, $querymedical);
                                    ?>
                                    <tr data-id="<?php echo $row['id']; ?>">
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo !empty($row['emergencyname']) ? htmlspecialchars($row['emergencyname']) : "N/A"; ?>
                                        </td>
                                        <td><?php echo !empty($row['emergencycontact']) ? htmlspecialchars($row['emergencycontact']) : "N/A"; ?>
                                        </td>
                                        <td class="no-print d-flex gap-2 justify-content-between">
                                            <?php if (mysqli_num_rows($resultmedical) > 0):
                                                while ($medical = mysqli_fetch_assoc($resultmedical)): ?>
                                                    <button class="btn btn-primary btn-view" data-bs-toggle="modal"
                                                        data-bs-target="#viewMedical"
                                                        data-disease="<?= htmlspecialchars($medical['disease'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-surgery="<?= htmlspecialchars($medical['recent_surgery'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-current="<?= htmlspecialchars($medical['current_disease'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-medcert="<?= htmlspecialchars($medical['medcertlink'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-upload="<?php
                                                        // handle a potentially NULL or empty date
                                                        echo htmlspecialchars(
                                                            !empty($medical['dateuploaded'])
                                                            ? date('F j, Y', strtotime($medical['dateuploaded']))
                                                            : '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>">
                                                        View Medical
                                                    </button>




                                                <?php endwhile; else: ?>

                                                <button class="btn btn-secondary " disabled>Unavailable</button>

                                            <?php endif; ?>
                                            <a class="btn btn-primary btn-dental"
                                                href="dentalchartconvert.php?patientid=<?= $row['id'] ?>">
                                                View Dental
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>

                            </tbody>
                        </table>
                    </div>
                    <script>
                        $(document).ready(function () {
                            $('#usersTable').DataTable({
                                "pageLength": 10,
                                "lengthMenu": [5, 10, 25, 50],
                                "order": [[0, "asc"]], // default sort by Name ascending
                                "columnDefs": [
                                    { "orderable": false, "targets": 7 } // disable sorting on 'Action' column
                                ]
                            });
                        });

                        function printUsersTable() {
                            const table = document.getElementById('usersTable');
                            if (!table) return;

                            const clone = table.cloneNode(true);
                            clone.querySelectorAll('.no-print').forEach(el => el.remove());

                            const printWindow = window.open('', '_blank');
                            printWindow.document.write('<html><head><title>Print Users</title>');
                            printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
                            printWindow.document.write(`
      <style>
        @page { size: landscape; }
        table {
          width: 100%;
          border-collapse: collapse;
        }
        th, td {
          border: 1px solid #000;
          padding: 8px;
        }
      </style>
    `);
                            printWindow.document.write('</head><body>');
                            printWindow.document.write('<h4>User Information</h4>');
                            printWindow.document.write(clone.outerHTML);
                            printWindow.document.write('</body></html>');
                            printWindow.document.close();
                            printWindow.print();
                        }

                        async function saveUsersTableToPDF() {
                            const table = document.getElementById('usersTable');
                            if (!table) return;

                            const clone = table.cloneNode(true);
                            clone.querySelectorAll('.no-print').forEach(el => el.remove());

                            const wrapper = document.createElement('div');
                            wrapper.appendChild(clone);
                            document.body.appendChild(wrapper);
                            wrapper.style.position = 'absolute';
                            wrapper.style.left = '-9999px';

                            const canvas = await html2canvas(wrapper);
                            const imgData = canvas.toDataURL('image/png');
                            const { jsPDF } = window.jspdf;
                            const pdf = new jsPDF('l', 'mm', 'a4');

                            const pageWidth = pdf.internal.pageSize.getWidth();
                            const imgWidth = pageWidth - 20;
                            const imgHeight = canvas.height * imgWidth / canvas.width;

                            pdf.text('User Information', 14, 10);
                            pdf.addImage(imgData, 'PNG', 10, 15, imgWidth, imgHeight);
                            pdf.save('user_information.pdf');

                            document.body.removeChild(wrapper);
                        }

                    </script>
                </div>
            </div>
        </div>
        <div class="modal fade" id="viewMedical" tabindex="-1" aria-labelledby="viewMedicalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewMedicalLabel">Medical History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="form-label">Date Uploaded</h6>
                            <p id="upload"></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="form-label">History of Present Disease or Allergies</h6>
                            <p id="disease"></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="form-label">Recent Surgery</h6>
                            <p id="surgery"></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="form-label">Current Disease</h6>
                            <p id="current"></p>
                        </div>
                        <a class="btn btn-primary" target="_blank" id="medcert">View Medical Certificate</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll(".btn-view").forEach(button => {
                    button.addEventListener("click", function () {
                        let disease = this.getAttribute("data-disease");
                        let surgery = this.getAttribute("data-surgery");
                        let current = this.getAttribute("data-current");
                        let upload = this.getAttribute("data-upload");
                        let medcert = this.getAttribute("data-medcert");
                        document.getElementById("disease").innerHTML = disease;
                        document.getElementById("surgery").innerHTML = surgery;
                        document.getElementById("current").innerHTML = current;
                        document.getElementById("upload").innerHTML = upload;
                        if (medcert === "") {
                            document.getElementById("medcert").style = "display:none;"
                        } else {
                            document.getElementById("medcert").style = "display:block;"
                            document.getElementById("medcert").href = "../uploads/" + medcert;
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