<?php if ($_SESSION['usertype'] == 'patient'):
        ?>

        <div class="sidebar">
                <ul class="nav w-100">
                        <?php if (isset($_SESSION['status']) && $_SESSION['status'] == "active"): ?>
                                <li class="<?= $navactive == "patDashboard" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patDashboard.php';"><i class="fas fa-tachometer-alt"></i>
                                                Home</span></li>
                                <li class="<?= $navactive == "patCalendar" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patCalendar.php';"><i class="fas fa-calendar-alt"></i>
                                                Schedule an Appointment</span></li>
                                <li class="<?= $navactive == "patAppointments" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patAppointments.php';"><i
                                                        class="fas fa-notes-medical"></i>
                                                My Appointments</span></li>
                                <li class="<?= $navactive == "patRecord" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patRecord.php';"><i class="fas fa-user"></i>
                                                Personal Info & Medical Record</span></li>

                                <li class="<?= $navactive == "patPolicy" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patPolicy.php';"><i class="fas fa-file-alt"></i>
                                                Clinic Policies</span>
                                </li>
                        <?php endif; ?>
                </ul>
                <ul class="nav logout-nav">
                        <li class="w-100"><span onclick="showLogoutDialog();"><i class="fas fa-sign-out-alt"></i> Logout</span>
                        </li>
                </ul>
        </div>
<?php elseif ($_SESSION['usertype'] == 'dentist'):
        ?>

        <div class="sidebar">
                <ul class="nav">
                        <li class="<?= $navactive == "denDashboard" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='denDashboard.php';"><i class="fas fa-tachometer-alt"></i>
                                        Home</span></li>
                        <li class="<?= $navactive == "denAppointments" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='denAppointments.php';"><i class="fas fa-calendar" aria-hidden="true"></i> Patient Appointments</span></li>
                        <!-- <li><span onclick="window.location.href='denRequest.php';"><i class="fas fa-notes-medical"></i> Request Approval</span></li> -->
                        <li class="<?= $navactive == "denCalendar" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='denCalendar.php';"><i class="fas fa-calendar-alt"></i>
                                        Appointment Calendar</span></li>
                        <li class="<?= $navactive == "denPatientlist" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='denPatientlist.php';"><i class="fas fa-users"></i>
                                        Patient Records</span></li>
                        <!-- <li><span onclick="window.location.href='denPayment.php';"><i class="fas fa-credit-card"></i> Payments</span></li> -->
                </ul>
                <ul class="nav logout-nav">
                        <li class="w-100"><span onclick="showLogoutDialog();"><i class="fas fa-sign-out-alt"></i> Logout</span>
                        </li>
                </ul>
                <?php if ($navactive == "denCalendar"): ?>
                    <div class="legend">
                        <div class="legend-item">
                                <span class="legend-color legend-primary" style="width:16px; height:16px; background:#007bff; border-radius:3px; display:inline-block;"></span>
                                Upcoming Appointment
                        </div>
                        <div class="legend-item">
                                <span class="legend-color legend-danger" style="width:16px; height:16px; background:#dc3545; border-radius:3px; display:inline-block;"></span>
                                Cancelled Appointment
                        </div>
                        <div class="legend-item">
                                <span class="legend-color legend-secondary" style="width:16px; height:16px; background:#6c757d; border-radius:3px; display:inline-block;"></span>
                                Completed Appointment
                        </div>
                     </div>
                <?php endif; ?>
        </div>



<?php elseif ($_SESSION['usertype'] == 'clinic_receptionist'):
        ?>
        <div class="sidebar">
                <ul class="nav">
                        <li class="<?= $navactive == "recepDashboard" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepDashboard.php';"><i
                                                class="fas fa-tachometer-alt"></i> Dashboard</span></li>
                        <li class="<?= $navactive == "recepManagement" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepManagement.php';"><i
                                                class="fas fa-users"></i> Manage Users</span></li>
                        <li class="<?= $navactive == "recepCalendar" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepCalendar.php';"><i
                                                class="fas fa-calendar-alt"></i>
                                        Calendar</span></li>
                        <li class="<?= $navactive == "denRequest" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/denRequest.php';"><i
                                                class="fas fa-notes-medical"></i>
                                        Approve Requests</span></li>
                        <li class="<?= $navactive == "denAppointments" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../dentist/denAppointments.php';"><i
                                                class="fas fa-notes-medical"></i>
                                        View Appointments</span></li>
                        <!-- <li><span onclick="window.location.href='#';"><i class="fas fa-file-alt"></i>Services</span></li> -->
                        <!--    <li class="<?= $navactive == "recepPayment" ? "" : "" ?> w-100"><span
                                        onclick="window.location.href='recepPayment.php';"><i class="fas fa-credit-card"></i>
                                        Payments</span></li>-->
                        <li class="<?= $navactive == "recepSMS" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepSMS.php';"><i
                                                class="fa-solid fa-envelope"></i></i>
                                        Email Scheduling</span></li>
                        <li class="<?= $navactive == "recepPolicy" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepPolicy.php';"><i
                                                class="fas fa-file-alt"></i>
                                        Update Clinic Policy</span></li>
                        <li class="<?= $navactive == "denPatientlist" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../dentist/denPatientlist.php';"><i
                                                class="fas fa-users"></i>
                                        Patients</span></li>
                </ul>
                <ul class="nav logout-nav">
                        <li class="w-100"><span onclick="showLogoutDialog();"><i class="fas fa-sign-out-alt"></i> Logout</span>
                        </li>
                </ul>
                <?php if ($navactive == "recepCalendar"): ?>
                    <div class="legend">
                        <div class="legend-item">
                                <span class="legend-color legend-primary" style="width:16px; height:16px; background:#007bff; border-radius:3px; display:inline-block;"></span>
                                Upcoming Appointment
                        </div>
                        <div class="legend-item">
                                <span class="legend-color legend-danger" style="width:16px; height:16px; background:#dc3545; border-radius:3px; display:inline-block;"></span>
                                Cancelled Appointment
                        </div>
                        <div class="legend-item">
                                <span class="legend-color legend-secondary" style="width:16px; height:16px; background:#6c757d; border-radius:3px; display:inline-block;"></span>
                                Completed Appointment
                        </div>
                     </div>
                <?php endif; ?>
        </div>


<?php endif; ?>