<?php
// Database connection code at the top of your file
require_once '../db/config.php';

// Allowed tab values
$allowedTabs = ['upcoming', 'rescheduled', 'completed', 'cancelled', 'rejected'];

// Get tab from GET and validate it
$activeTab = isset($_GET['tab']) && in_array($_GET['tab'], $allowedTabs)
    ? $_GET['tab']
    : 'upcoming';

$query = "SELECT id, patient_id, notes, patient_name, treatment, appointment_time, appointment_date, status
          FROM approved_requests WHERE status = 'upcoming';";

$resultupcoming = mysqli_query($db, $query);

if (!$resultupcoming) {
    die("Query Error: " . mysqli_error($db));
}

$query = "SELECT id, patient_id, notes, patient_name, treatment, appointment_time, appointment_date, status
          FROM approved_requests WHERE status = 'completed';";

$resultcompleted = mysqli_query($db, $query);

if (!$resultcompleted) {
    die("Query Error: " . mysqli_error($db));
}


$query = "SELECT id, patient_id, notes, patient_name, treatment, appointment_time, appointment_date, status
          FROM approved_requests WHERE status = 'rescheduled';";

$resultrescheduled = mysqli_query($db, $query);

if (!$resultrescheduled) {
    die("Query Error: " . mysqli_error($db));
}


$query = "SELECT id, patient_id, notes, patient_name, treatment, appointment_time, appointment_date, status
          FROM approved_requests WHERE status = 'cancelled';";

$resultcancelled = mysqli_query($db, $query);

if (!$resultcancelled) {
    die("Query Error: " . mysqli_error($db));
}

$queryreject = "SELECT id, patient_id, patient_name, treatment, appointment_time, appointment_date, status
                FROM rejected_requests";

$resultreject = mysqli_query($db, $queryreject);

if (!$resultreject) {
    die("Query Error: " . mysqli_error($db));
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
//$showActionColumn = !in_array($status, ['completed', 'cancelled']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (empty($_POST['id']) || empty($_POST['notes'])) {
        echo "<script>alert('ID and Notes are required');</script>";
        exit;
    }

    $id = intval($_POST['id']); // Convert to integer to prevent SQL injection
    $notes = trim($_POST['notes']); // Trim spaces
    $status = "completed";
    // Check if ID is valid
    if ($id <= 0) {
        echo "<script>alert('Invalid ID');</script>";
        exit;
    }

    // Prepare and execute update query
    $stmt = $db->prepare("UPDATE approved_requests SET notes = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $notes, $status, $id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<script>alert('Request updated successfully!'); window.location.href='denAppointments.php';</script>";
    } else {
        echo "<script>alert('Failed to update record or no changes made');</script>";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dentist Appointments</title>
    <link rel="stylesheet" href="denAppointments.css">
    <?php require_once "../db/head.php" ?>

    <script src="recepScript.js" defer></script>
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
        $navactive = "denAppointments";

        require_once "../db/nav.php" ?>

        <div class="main-content overflow-hidden">
            <div class="card">
                <div class="card-body">
                    <div class="table-header">
                        <div>
                            <h2>List of Appointments</h2>

                        </div>
                    </div>
                    <div class="appointments-tabs overflow-x-scroll overflow-y-hidden" style="margin-bottom:10px;">
                        <a href="?tab=upcoming"
                            class="tab <?php echo ($_GET['tab'] ?? 'upcoming') == 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                        <a href="?tab=rescheduled"
                            class="tab <?php echo ($_GET['tab'] ?? '') == 'rescheduled' ? 'active' : ''; ?>">Re-scheduled</a>
                        <a href="?tab=completed"
                            class="tab <?php echo ($_GET['tab'] ?? '') == 'completed' ? 'active' : ''; ?>">Completed</a>
                        <a href="?tab=cancelled"
                            class="tab <?php echo ($_GET['tab'] ?? '') == 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                        <?php if ($_SESSION['usertype'] == 'clinic_receptionist'): ?>
                            <a href="?tab=rejected"
                                class="tab <?php echo ($_GET['tab'] ?? '') == 'rejected' ? 'active' : ''; ?>">Rejected</a>
                        <?php endif; ?>
                    </div>
                    <?php if ($activeTab == 'upcoming'): ?>
                        <div class="print-section mb-3">
                            <form class="d-flex align-items-center flex-column flex-md-row gap-2"
                                onsubmit="printFilteredTable('<?php echo $activeTab; ?>'); return false;">
                                <div class="d-flex gap-2">
                                    <label for="from-<?php echo $activeTab; ?>">From: </label>
                                    <input type="date" id="from-<?php echo $activeTab; ?>" name="from">

                                    <label for="to-<?php echo $activeTab; ?>">To: </label>
                                    <input type="date" id="to-<?php echo $activeTab; ?>" name="to">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-sm">Print</button>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        onclick="saveTableToPDF('<?php echo $tab; ?>')">Save to PDF</button>
                                </div>
                            </form>
                        </div>
                        <div class="overflow-x-scroll d-flex">
                            <table id="appointment-approve-upcoming">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Patient No.</th>
                                        <th>Patient Name</th>
                                        <th>Treatment</th>

                                        <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                            <th class="action-column">Action</th>
                                        <?php endif; ?>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultupcoming)): ?>
                                        <?php
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;



                                        /*                         // Check if appointment has ended
                                                                if (strtotime($appointmentEndDateTime) < strtotime($currentDateTime)) {
                                                                    $status = 'completed';
                                                                } */

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>
                                            <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                                <td class="action-buttons">
                                                    <!--    <button class="complete-btn" data-id="<?//php// echo $row['id']; ?>">Mark as Done</button>
                                     <button class="archive-btn">Decline</button> -->
                                                    <button class="complete-btn btn btn-primary" data-id="<?= $row['id']; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#completeModal">
                                                        Mark as Done
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($activeTab == 'rescheduled'): ?>
                        <div class="print-section mb-3">
                            <form class="d-flex align-items-center flex-column flex-md-row gap-2"
                                onsubmit="printFilteredTable('<?php echo $activeTab; ?>'); return false;">
                                <div class="d-flex gap-2">
                                    <label for="from-<?php echo $activeTab; ?>">From: </label>
                                    <input type="date" id="from-<?php echo $activeTab; ?>" name="from">

                                    <label for="to-<?php echo $activeTab; ?>">To: </label>
                                    <input type="date" id="to-<?php echo $activeTab; ?>" name="to">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-sm">Print</button>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        onclick="saveTableToPDF('<?php echo $tab; ?>')">Save to PDF</button>
                                </div>
                            </form>
                        </div>
                        <div class="overflow-x-scroll d-flex">
                            <table id="appointment-approve-rescheduled">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Patient No.</th>
                                        <th>Patient Name</th>
                                        <th>Treatment</th>

                                        <?php if ($_SESSION['usertype'] == 'dentist'): ?>
                                            <th class="action-column">Action</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultrescheduled)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Default status is upcoming
                                

                                        // Check if appointment has ended
                                        // if (strtotime($appointmentEndDateTime) < strtotime($currentDateTime)) {
                                        //     $status = 'completed';
                                        // }
                                
                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>
                                            <?php if ($_SESSION['usertype'] == 'dentist'): ?>

                                                <td class="action-buttons">
                                                    <!--    <button class="complete-btn" data-id="<?//php// echo $row['id']; ?>">Mark as Done</button>
                                     <button class="archive-btn">Decline</button> -->
                                                    <button class="complete-btn btn btn-primary" data-id="<?= $row['id']; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#completeModal">
                                                        Mark as Done
                                                    </button>
                                                </td>

                                            <?php endif; ?>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>

                            </table>
                        </div>
                    <?php elseif ($activeTab == 'completed'): ?>
                        <div class="print-section mb-3">
                            <form class="d-flex align-items-center flex-column flex-md-row gap-2"
                                onsubmit="printFilteredTable('<?php echo $activeTab; ?>'); return false;">
                                <div class="d-flex gap-2">
                                    <label for="from-<?php echo $activeTab; ?>">From: </label>
                                    <input type="date" id="from-<?php echo $activeTab; ?>" name="from">

                                    <label for="to-<?php echo $activeTab; ?>">To: </label>
                                    <input type="date" id="to-<?php echo $activeTab; ?>" name="to">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-sm">Print</button>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        onclick="saveTableToPDF('<?php echo $tab; ?>')">Save to PDF</button>
                                </div>
                            </form>
                        </div>
                        <div class="overflow-x-scroll d-flex">
                            <table id="appointment-approve-completed">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Patient No.</th>
                                        <th>Patient Name</th>
                                        <th>Treatment</th>

                                        <th class="reason-column">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultcompleted)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>

                                            <td class="reason-note">
                                                <?php echo htmlspecialchars($row['notes']); ?>
                                            </td>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($activeTab == 'cancelled'): ?>
                        <div class="print-section mb-3">
                            <form class="d-flex align-items-center flex-column flex-md-row gap-2"
                                onsubmit="printFilteredTable('<?php echo $activeTab; ?>'); return false;">
                                <div class="d-flex gap-2">
                                    <label for="from-<?php echo $activeTab; ?>">From: </label>
                                    <input type="date" id="from-<?php echo $activeTab; ?>" name="from">

                                    <label for="to-<?php echo $activeTab; ?>">To: </label>
                                    <input type="date" id="to-<?php echo $activeTab; ?>" name="to">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-sm">Print</button>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        onclick="saveTableToPDF('<?php echo $tab; ?>')">Save to PDF</button>
                                </div>
                            </form>
                        </div>
                        <div class="overflow-x-scroll d-flex">
                            <table id="appointment-approve-cancelled">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Name</th>
                                        <th>Treatment</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultcancelled)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;


                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>


                                        </tr>
                                    <?php endwhile; ?>


                                    <?php while ($row = mysqli_fetch_assoc($resultreject)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($activeTab == 'rejected'): ?>
                        <div class="print-section mb-3">
                            <form class="d-flex align-items-center flex-column flex-md-row gap-2"
                                onsubmit="printFilteredTable('<?php echo $activeTab; ?>'); return false;">
                                <div class="d-flex gap-2">
                                    <label for="from-<?php echo $activeTab; ?>">From: </label>
                                    <input type="date" id="from-<?php echo $activeTab; ?>" name="from">

                                    <label for="to-<?php echo $activeTab; ?>">To: </label>
                                    <input type="date" id="to-<?php echo $activeTab; ?>" name="to">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-sm">Print</button>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        onclick="saveTableToPDF('<?php echo $tab; ?>')">Save to PDF</button>
                                </div>
                            </form>
                        </div>
                        <div class="overflow-x-scroll d-flex">
                            <table id="appointment-rejected">
                                <thead>
                                    <tr>
                                        <th>Appointment No.</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Name</th>
                                        <th>Treatment</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($resultreject)): ?>
                                        <?php
                                        // Determine appointment status
                                        $appointmentDateTime = $row['appointment_date'] . ' ' . $row['appointment_time'];
                                        $currentDateTime = date('Y-m-d H:i:s');

                                        // Get the end time of the appointment
                                        $appointmentEndTime = date('H:i:s', strtotime($row['appointment_time'] . ' +30 minutes'));
                                        $appointmentEndDateTime = $row['appointment_date'] . ' ' . $appointmentEndTime;

                                        // Check for manual status updates from the database
                                        if (!empty($row['status'])) {
                                            $status = strtolower($row['status']);
                                        }
                                        ?>
                                        <tr class="appointment-row" data-status="<?php echo $status; ?>">
                                            <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['treatment']); ?></td>

                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <script>
                        $(document).ready(function () {
                            $('#appointment-approve-upcoming').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],
                                "columnDefs": [
                                    { "orderable": false, "targets": "action-column" }

                                ]
                            });
                            $('#appointment-approve-rescheduled').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],
                                "columnDefs": [
                                    { "orderable": false, "targets": "action-column" }

                                ]
                            });
                            $('#appointment-approve-completed').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],

                            });
                            $('#appointment-approve-cancelled').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],

                            });
                            $('#appointment-rejected').DataTable({
                                paging: true,

                                searching: true,
                                order: [[1, 'asc']],

                            });
                        });

                        function printFilteredTable(tab) {
                            const fromDate = document.getElementById(`from-${tab}`).value;
                            const toDate = document.getElementById(`to-${tab}`).value;
                            const table = document.getElementById(`appointment-approve-${tab}`) || document.getElementById(`appointment-rejected`);

                            if (!table) return;

                            const rows = table.querySelectorAll('tbody tr');
                            const filteredRows = [];

                            const from = fromDate ? new Date(fromDate) : null;
                            const to = toDate ? new Date(toDate) : null;

                            rows.forEach(row => {
                                const dateCell = row.querySelector(`td:nth-child(2)`);
                                if (!dateCell) return;

                                const date = new Date(dateCell.textContent);

                                if ((from && date < from) || (to && date > to)) {
                                    row.style.display = 'none';
                                } else {
                                    row.style.display = '';
                                    filteredRows.push(row.cloneNode(true));
                                }
                            });

                            const printWindow = window.open('', '_blank');
                            printWindow.document.write('<html><head><title>Print Appointments</title>');
                            printWindow.document.write('<style>table{width:100%; border-collapse:collapse;} th, td{border:1px solid #000;padding:8px;text-align:left;}</style>');
                            printWindow.document.write('</head><body>');
                            printWindow.document.write(`<h2>Appointments (${tab})</h2>`);
                            printWindow.document.write('<table>' + table.querySelector('thead').outerHTML + '<tbody>');
                            filteredRows.forEach(row => printWindow.document.write(row.outerHTML));
                            printWindow.document.write('</tbody></table>');
                            printWindow.document.write('</body></html>');
                            printWindow.document.close();
                            printWindow.print();
                        }

                        async function saveTableToPDF(tab) {
                            const fromDate = document.getElementById(`from-${tab}`).value;
                            const toDate = document.getElementById(`to-${tab}`).value;
                            const table = document.getElementById(`appointment-approve-${tab}`);
                            if (!table) return;

                            const rows = table.querySelectorAll('tbody tr');
                            const from = fromDate ? new Date(fromDate) : null;
                            const to = toDate ? new Date(toDate) : null;

                            rows.forEach(row => {
                                const dateCell = row.querySelector(`td:nth-child(2)`);
                                if (!dateCell) return;
                                const date = new Date(dateCell.textContent);
                                row.style.display = (!from || date >= from) && (!to || date <= to) ? '' : 'none';
                            });

                            const clone = table.cloneNode(true);
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

                            pdf.text(`${tab.charAt(0).toUpperCase() + tab.slice(1)} Appointments`, 14, 10);
                            pdf.addImage(imgData, 'PNG', 10, 15, imgWidth, imgHeight);
                            pdf.save(`appointments_${tab}.pdf`);

                            document.body.removeChild(wrapper);
                        }
                    </script>

                </div>
            </div>
        </div>


    </div><!-- Complete Task Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalLabel">Mark as Done</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="completeForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="requestId">
                        <div class="mb-3">
                            <label for="dentalNotes" class="form-label">Dental Notes</label>
                            <textarea class="form-control" id="dentalNotes" name="notes" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
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
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".complete-btn").forEach(button => {
                button.addEventListener("click", function () {
                    let requestId = this.getAttribute("data-id");
                    document.getElementById("requestId").value = requestId;
                });
            });

        });

        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.complete-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');
                    row.setAttribute('data-status', 'completed');

                    // Update status in database via AJAX
                    const appointmentId = this.getAttribute('data-id');
                    updateAppointmentStatus(appointmentId, 'completed');

                    // Re-filter to show in correct tab
                    filterAppointments(document.querySelector('.tab.active').getAttribute('data-status'));
                });
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
        // Dropdown toggle functionality
        document.addEventListener('DOMContentLoaded', function () {
            var dropdownButtons = document.querySelectorAll('.dropdown-btn');

            dropdownButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    this.classList.toggle('active');
                    var dropdownContainer = this.nextElementSibling;

                    // Toggle dropdown visibility
                    dropdownContainer.style.display = dropdownContainer.style.display === 'block' ? 'none' : 'block';
                });
            });
        });

        // Logout confirmation dialog
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
        };

    </script>

</body>

</html>