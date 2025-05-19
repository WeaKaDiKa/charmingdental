<?php if ($_SESSION['usertype'] == 'patient'):
        ?>

        <div class="sidebar">
                <ul class="nav w-100">
                        <?php if (isset($_SESSION['status']) && $_SESSION['status'] == "active"): ?>
                                <li class="<?= $navactive == "patDashboard" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patDashboard.php';"><i class="fas fa-tachometer-alt"></i>
                                                Dashboard</span></li>
                                <li class="<?= $navactive == "patAppointments" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patAppointments.php';"><i
                                                        class="fas fa-notes-medical"></i>
                                                Appointments</span></li>
                                <li class="<?= $navactive == "patCalendar" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patCalendar.php';"><i class="fas fa-calendar-alt"></i>
                                                Schedule an Appointment</span></li>
                                <li class="<?= $navactive == "patRecord" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patRecord.php';"><i class="fas fa-user"></i>
                                                Patient
                                                Record</span></li>


                                <li class="<?= $navactive == "patPolicy" ? "active" : "" ?> w-100"><span
                                                onclick="window.location.href='patPolicy.php';"><i class="fas fa-file-alt"></i>
                                                Policy</span>
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
                                        Dashboard</span></li>
                        <li class="<?= $navactive == "denAppointments" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='denAppointments.php';"><i
                                                class="fas fa-notes-medical"></i>
                                        Appointments</span></li>
                        <!-- <li><span onclick="window.location.href='denRequest.php';"><i class="fas fa-notes-medical"></i> Request Approval</span></li> -->
                        <li class="<?= $navactive == "denCalendar" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='denCalendar.php';"><i class="fas fa-notes-medical"></i>
                                        Calendar</span></li>
                        <li class="<?= $navactive == "denPatientlist" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='denPatientlist.php';"><i class="fas fa-users"></i>
                                        Patient
                                        List</span></li>
                        <!-- <li><span onclick="window.location.href='denPayment.php';"><i class="fas fa-credit-card"></i> Payments</span></li> -->
                </ul>
                <ul class="nav logout-nav">
                        <li class="w-100"><span onclick="showLogoutDialog();"><i class="fas fa-sign-out-alt"></i> Logout</span>
                        </li>
                </ul>
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
                                                class="fas fa-users"></i> User
                                        Management</span></li>
                        <li class="<?= $navactive == "recepCalendar" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepCalendar.php';"><i
                                                class="fas fa-calendar-alt"></i>
                                        Calendar</span></li>
                        <li class="<?= $navactive == "denRequest" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/denRequest.php';"><i
                                                class="fas fa-notes-medical"></i>
                                        Request
                                        Approval</span></li>
                        <li class="<?= $navactive == "denAppointments" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../dentist/denAppointments.php';"><i
                                                class="fas fa-notes-medical"></i>
                                        Appointments</span></li>
                        <!-- <li><span onclick="window.location.href='#';"><i class="fas fa-file-alt"></i>Services</span></li> -->
                        <!--    <li class="<?= $navactive == "recepPayment" ? "" : "" ?> w-100"><span
                                        onclick="window.location.href='recepPayment.php';"><i class="fas fa-credit-card"></i>
                                        Payments</span></li>-->
                        <li class="<?= $navactive == "recepSMS" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepSMS.php';"><i
                                                class="fa-solid fa-envelope"></i></i>
                                        Email Schedule</span></li>
                        <li class="<?= $navactive == "recepPolicy" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../receptionist_admin/recepPolicy.php';"><i
                                                class="fas fa-file-alt"></i>
                                        Policy</span></li>
                        <li class="<?= $navactive == "denPatientlist" ? "active" : "" ?> w-100"><span
                                        onclick="window.location.href='../dentist/denPatientlist.php';"><i
                                                class="fas fa-users"></i>
                                        Patient
                                        List</span></li>
                </ul>
                <ul class="nav logout-nav">
                        <li class="w-100"><span onclick="showLogoutDialog();"><i class="fas fa-sign-out-alt"></i> Logout</span>
                        </li>
                </ul>
        </div>


<?php endif; ?>