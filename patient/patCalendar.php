<?php
require_once('../db/db_patient_appointments.php');

// Comprehensive session check
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header('location: patLogin.php');
    exit();
}
// Count submitted appointments in appointments table
$checkSql1 = "SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'submitted'";
$stmtCheck1 = $conn->prepare($checkSql1);
$stmtCheck1->bind_param("i", $userId);
$stmtCheck1->execute();
$stmtCheck1->bind_result($submittedCount);
$stmtCheck1->fetch();
$stmtCheck1->close();

// Count approved appointments in approved_requests table
$checkSql2 = "SELECT COUNT(*) FROM approved_requests WHERE patient_id = ? AND status = 'upcoming'";
$stmtCheck2 = $conn->prepare($checkSql2);
$stmtCheck2->bind_param("i", $userId);
$stmtCheck2->execute();
$stmtCheck2->bind_result($approvedCount);
$stmtCheck2->fetch();
$stmtCheck2->close();

$activeCount = $submittedCount + $approvedCount;
if ($activeCount > 0) {
    // Show policy reminder and exit before rendering form
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointments - Charming Smile Dental Clinic</title>
        <?php require_once "../db/head.php" ?>
        <link rel="stylesheet" href="patAppointments.css">
        <link rel="stylesheet" href="main.css">

        <title>Booking Policy Reminder</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f9f9f9;
            }

            .policy-reminder {
                max-width: 500px;
                margin: 80px auto;
                padding: 40px 30px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
                text-align: center;
            }

            .policy-reminder h2 {
                color: #d9534f;
            }

            .policy-reminder p {
                font-size: 1.1em;
            }

            p {
                max-width: 600px;
                /* Limit width for better readability */
                margin: 20px auto;
                /* Center horizontally with vertical spacing */
                font-size: 16px;
                /* Comfortable font size */
                line-height: 1.6;
                /* Spacing between lines */
                color: #333333;
                /* Dark gray text for good contrast */
                text-align: left;
                /* Align text to the left */
                font-family: Arial, sans-serif;
                /* Clean font */
            }

            p strong,
            p b {
                font-weight: 700;
                /* Make sure strong/b are bold */
                color: #000000;
                /* Optional: make bold text fully black */
                text-align: center;
            }
        </style>
    </head>

    <body>
        <!-- Top Header -->
        <?php require_once "../db/header.php" ?>

        <!-- Main Wrapper -->
        <div class="main-wrapper overflow-hidden">
            <script>step = 1;</script>
            <!-- Sidebar -->
            <?php
            $navactive = "patCalendar";
            require_once "../db/nav.php" ?>
            <div class="policy-reminder">
                <h2>Booking Policy Reminder</h2>
                <p>
                    You are only allowed to have <strong>one active appointment</strong> at a time.<br> Please wait until
                    your current appointment is <b>completed, cancelled, or rejected</b> before booking another.<br><br>
                    Thank you for your understanding!
                </p>
            </div>
        </div>
    </body>

    </html>
    <?php
    exit();
} else {
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointments - Charming Smile Dental Clinic</title>
        <?php require_once "../db/head.php" ?>
        <link rel="stylesheet" href="patAppointments.css">
        <link rel="stylesheet" href="main.css">
        <style>
            select {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
                background-color: #fff;
                font-size: 16px;
                color: #333;
                cursor: pointer;
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
            }

            /* Hover and focus effects for dropdowns */
            select:hover,
            select:focus {
                border-color: #007BFF;
                box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
                outline: none;
            }

            /* Styles for the dropdown options */
            select option {
                padding: 10px;
                background-color: #fff;
                color: #333;
            }

            /* Styles for the Submit button */
            #submit {
                width: 15%;
                padding: 12px;
                margin: 20px auto 0 auto;
                /* Centers the button horizontally */
                display: block;
                /* Ensures margin auto works */
                background-color: #da9393;
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: background-color 0.3s ease, transform 0.2s ease;
            }


            /* Hover effect for the Submit button */
            #submit:hover {
                background-color: #d99e9e;
                transform: scale(1.02);
            }

            /* Active effect for the Submit button */
            #submit:active {
                background-color: #004080;
                transform: scale(0.98);
            }

            /* Styles for the labels */
            label {
                display: block;
                margin-top: 10px;
                font-size: 16px;
                font-weight: bold;
                color: #333;
            }

            .month-selector select {
                width: auto;
                margin-right: 10px;
            }

            /* Styles for the time slot dropdown */
            #timeSlotsContainer select {
                width: 100%;
                margin-top: 10px;
            }

            h2 {
                margin-bottom: 20px;
            }

            /* Month and Year Selector Styling */
            .month-selector {
                margin-bottom: 20px;
                display: flex;
                align-items: center;
            }

            /* Calendar Styling */
            #calendar {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 10px;
                margin-top: 20px;
            }

            .day {
                width: 80px;
                /* Adjusted width for smaller containers */
                height: 80px;
                /* Adjusted height for smaller containers */
                display: flex;
                margin-left: 24%;
                justify-content: center;
                align-items: center;
                cursor: pointer;
                border-radius: 8px;
                transition: background-color 0.3s ease, transform 0.2s ease;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                font-size: 18px;
                /* Slightly larger font for better visibility */
            }

            .day:hover {
                transform: scale(1.05);
                /* Slight zoom effect on hover */
            }

            .available {
                background-color: #90ee90;
                /* Light green for available dates */
            }

            .unavailable {
                background-color: #ffcccb;
                /* Light red for unavailable dates */
                cursor: not-allowed;
                /* Change cursor to indicate unavailability */
            }

            .selected {
                border: 2px solid #007BFF !important;
                background-color: rgb(81, 163, 81) !important;
                /* Blue border for selected date */
                box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
            }

            /* Time Slot Styling */
            .time-slot {
                margin-top: 20px;
            }

            .time-slot button {
                margin-right: 10px;
                padding: 10px 15px;
                border-radius: 5px;
                border: none;
                cursor: pointer;
                transition: background-color 0.3s ease, transform 0.2s ease;
            }

            .time-slot button.available {
                background-color: #90ee90;
                /* Light green for available times */
            }

            .time-slot button.unavailable {
                background-color: #ffcccb;
                /* Light red for unavailable times */
            }

            .time-slot button:hover {
                transform: scale(1.05);
                /* Slight zoom effect on hover */
            }

            /* Day Labels Styling */
            .day-labels {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                text-align: center;
                margin-bottom: -10px;
                /* Adjust spacing between labels and calendar */
                font-weight: bold;
                color: #007BFF;
            }

            /* Summary section */
            #summary {
                background-color: #FFE4E1;
                padding: 2rem;
                border-radius: 8px;
                margin: 2rem 0;
            }

            #summary h3 {
                color: #8B4513;
                margin-top: 0;
                margin-bottom: 1.5rem;
                font-size: 1.5rem;
            }

            #summary p {
                margin: 0.75rem 0;
                color: #A0522D;
                font-size: 1.1rem;
            }

            #summary span {
                font-weight: 600;
                color: #8B4513;
            }

            /* Next Button Styling */
            .next-button {
                margin-top: 20px;
                padding: 10px 15px;
                border-radius: 5px;
                border: none;
                background-color: #007BFF;
                /* Blue background for the button */
                color: white;
                cursor: pointer
            }

            .next-button:hover {
                opacity: .8
            }


            .top-header img {
                width: 55px;
                height: 50px;
                margin-right: 10px;
                border-radius: 20%;
            }

            .custom-popup {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
            }

            .custom-popup-content {
                background-color: #fff;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 400px;
                text-align: center;
                border-radius: 10px;
            }

            .close-popup {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }

            .close-popup:hover {
                color: #000;
            }

            .color-legend {
                display: flex;
                gap: 15px;
                margin-top: 10px;
                font-size: 16px;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .legend-box {
                width: 20px;
                height: 20px;
                display: inline-block;
                border-radius: 4px;
            }

            .unavailable {
                background-color: #f8a8a8;
                /* Match the pink color */
                border: 1px solid #d87373;
            }

            .available {
                background-color: #90ee90;
                /* Match the green color */
                border: 1px solid #4caf50;
            }
        </style>
    </head>

    <body>
        <!-- Top Header -->
        <?php require_once "../db/header.php" ?>

        <!-- Main Wrapper -->
        <div class="main-wrapper overflow-hidden">
            <script>step = 1;</script>
            <!-- Sidebar -->
            <?php
            $navactive = "patCalendar";
            require_once "../db/nav.php" ?>
            <div class="main-content overflow-hidden">
                <div class="card">
                    <div class="card-body">
                        <div class="appointments-header">
                            <h2>Appointment Lists</h2>
                        </div>
                        <form id="appointmentForm" enctype="multipart/form-data" method="POST">
                            <div class="main-content overflow-hidden" id="stepone">
                                <div>
                                    <label for="dentist">Choose a Dentist (Required Field):</label>
                                    <select name="dentist" id="dentist" onchange="updateSlots()">
                                        <option value="" selected disabled>Select a dentist</option>
                                        <?php foreach ($dentists as $dentist): ?>
                                            <option value="<?php echo htmlspecialchars($dentist['name']); ?>">
                                                <?php echo htmlspecialchars($dentist['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <label for="service">Choose a Service (Required Field):</label>
                                <select name="service" id="service" onchange="updateSlots()">
                                    <option value="" selected disabled>Select a service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option data-cost="<?php echo htmlspecialchars($service['rate']); ?>"
                                            value="<?php echo htmlspecialchars($service['name']); ?> <?php echo htmlspecialchars($service['rate']); ?>"
                                            data-duration="<?php echo htmlspecialchars($service['duration']); ?>">
                                            <?php echo htmlspecialchars($service['name']); ?> -
                                            <?php echo htmlspecialchars($service['rate']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="navigation-buttons mt-3">
                                    <button type="button" class="btn btn-primary" id="nextStepOne" onclick="nextStep(2)"
                                        disabled>Next</button>
                                </div>

                            </div>

                            <script>
                                const dentistSelect = document.getElementById('dentist');
                                const serviceSelects = document.getElementById('service');
                                const nextButton = document.getElementById('nextStepOne');

                                function validateStepOne() {
                                    if (dentistSelect.value && serviceSelects.value) {
                                        nextButton.disabled = false;
                                    } else {
                                        nextButton.disabled = true;
                                    }
                                    var select = document.getElementById('service');
                                    var selectedOption = select.options[select.selectedIndex];

                                    if (selectedOption) {
                                        var cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
                                        var discount = cost * 0.15; // 15% of the cost

                                        document.getElementById('costDisplay').innerHTML = discount.toFixed(2);
                                    }
                                }

                                // Listen for changes
                                dentistSelect.addEventListener('change', validateStepOne);
                                serviceSelects.addEventListener('change', validateStepOne);

                            </script>
                            <div class="main-content overflow-x-scroll" id="steptwo" style="display:none">
                                <h2>Select Date</h2>

                                <div class="month-selector  d-flex gap-2 flex-col flex-md-row">
                                    <label for="yearSelect">Choose a year:</label>
                                    <select id="yearSelect">
                                        <option value="2025">2025</option>
                                    </select>

                                    <label for="monthSelect">Choose a month:</label>
                                    <select id="monthSelect"></select>
                                    <div class="color-legend">
                                        <span class="legend-item">
                                            <span class="legend-box unavailable"></span> Unavailable Days
                                        </span>
                                        <span class="legend-item">
                                            <span class="legend-box available"></span> Available Days
                                        </span>
                                    </div>
                                </div>

                                <!-- Day Labels -->
                                <div class="day-labels">
                                    <span>Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>

                                <div id="calendar" class="m-2"></div>
                                <br>


                                <div class="time-slot" id="timeSlotsContainer" style="display:none;">
                                    <h3>Select a Time Slot:</h3>
                                    <label for="appointment_time">Choose a time slot:</label>
                                    <select name="appointment_time" id="appointment_time">
                                        <option value="">Select a time slot</option>
                                        <!-- Options will be populated by JavaScript -->
                                    </select>
                                </div>
                                <div class="navigation-buttons mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Previous</button>
                                    <button type="button" class="btn btn-primary" id="nextStepTwo" onclick="nextStep(3)"
                                        disabled>Next</button>

                                </div>

                            </div>
                            <script>
                                const appointmentTime = document.getElementById('appointment_time');
                                const nextStepTwo = document.getElementById('nextStepTwo');

                                function validateStepTwo() {
                                    if (appointmentTime.value) {
                                        nextStepTwo.disabled = false;
                                    } else {
                                        nextStepTwo.disabled = true;
                                    }
                                }

                                appointmentTime.addEventListener('change', validateStepTwo);

                            </script>
                            <div class="main-content overflow-hidden" id="stepthree" style="display:none">
                                <?php
                                $userid = $_SESSION['id'];
                                $sql = "SELECT disease, recent_surgery, current_disease FROM medical WHERE usersid = ? ORDER BY medid DESC LIMIT 1";
                                $stmt = $conn->prepare($sql);
                                if ($stmt) {
                                    $stmt->bind_param("i", $userid);
                                    $stmt->execute();
                                    $stmt->bind_result($disease, $recent_surgery, $current_disease);
                                    $stmt->fetch();
                                    $stmt->close();
                                } else {
                                    echo "<script>alert('Error preparing the statement: " . $conn->error . "');</script>";
                                }
                                ?>

                                <h3>Health Declaration Form</h3>

                                <!-- 1. History of Present Disease or Allergies -->
                                <div class="mb-2">
                                    <label>Do you have any history of present disease or allergies?</label>
                                    <input type="radio" name="hasDisease" value="yes"> Yes
                                    <input type="radio" name="hasDisease" value="no"> No
                                </div>
                                <div class="mb-3" id="diseaseField" style="display:none;">
                                    <label for="disease" class="form-label">If yes, please specify:</label>
                                    <textarea class="form-control" id="disease" name="disease"
                                        rows="3"><?php echo htmlspecialchars($disease ?? ''); ?></textarea>
                                </div>

                                <!-- 2. Recent Surgery -->
                                <div class="mb-2">
                                    <label>Have you undergone any recent surgery?</label>
                                    <input type="radio" name="hasSurgery" value="yes"> Yes
                                    <input type="radio" name="hasSurgery" value="no"> No
                                </div>
                                <div class="mb-3" id="surgeryField" style="display:none;">
                                    <label for="recent_surgery" class="form-label">If yes, please specify:</label>
                                    <input type="text" class="form-control" id="recent_surgery" name="recent_surgery"
                                        value="<?php echo htmlspecialchars($recent_surgery ?? ''); ?>">
                                </div>

                                <!-- 3. Current Disease -->
                                <div class="mb-2">
                                    <label>Do you have any current diseases (e.g., hypertension, diabetes)?</label>
                                    <input type="radio" name="hasCurrentDisease" value="yes"> Yes
                                    <input type="radio" name="hasCurrentDisease" value="no"> No
                                </div>
                                <div class="mb-3" id="currentDiseaseField" style="display:none;">
                                    <label for="current_disease" class="form-label">If yes, please specify:</label>
                                    <input type="text" class="form-control" id="current_disease" name="current_disease"
                                        value="<?php echo htmlspecialchars($current_disease ?? ''); ?>">
                                </div>

                                <!-- Medical Certificate -->
                                <div class="mb-3" id="medicalCertDiv">
                                    <label for="medical_certificate" class="form-label">Medical Certificate (approving you
                                        are in
                                        good condition to undergo such treatment)</label>
                                    <input type="file" class="form-control" id="medical_certificate"
                                        name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png">
                                </div>


                                <div class="navigation-buttons mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
                                    <button type="button" class="btn btn-primary" id="nextStepThree"
                                        onclick="nextStep(4)">Next</button>
                                </div>
                            </div>

                            <script>
                                const disease = document.getElementById('disease');
                                const recentSurgery = document.getElementById('recent_surgery');
                                const currentDisease = document.getElementById('current_disease');
                                const medicalCert = document.getElementById('medical_certificate');
                                const nextStepThree = document.getElementById('nextStepThree');

                                const diseaseField = document.getElementById('diseaseField');
                                const surgeryField = document.getElementById('surgeryField');
                                const currentDiseaseField = document.getElementById('currentDiseaseField');

                                function handleRadioToggle() {
                                    // Disease toggle
                                    if (document.querySelector('input[name="hasDisease"]:checked').value === 'yes') {
                                        diseaseField.style.display = 'block';
                                        disease.value = '';
                                    } else {
                                        diseaseField.style.display = 'none';
                                        disease.value = 'N/A';
                                    }

                                    // Surgery toggle
                                    if (document.querySelector('input[name="hasSurgery"]:checked').value === 'yes') {
                                        surgeryField.style.display = 'block';
                                        recentSurgery.value = '';
                                    } else {
                                        surgeryField.style.display = 'none';
                                        recentSurgery.value = 'N/A';
                                    }

                                    // Current disease toggle
                                    if (document.querySelector('input[name="hasCurrentDisease"]:checked').value === 'yes') {
                                        currentDiseaseField.style.display = 'block';
                                        currentDisease.value = '';
                                    } else {
                                        currentDiseaseField.style.display = 'none';
                                        currentDisease.value = 'N/A';
                                    }

                                    // Show/hide medical cert based on all "no"
                                    const allNo =
                                        document.querySelector('input[name="hasDisease"]:checked').value === 'no' &&
                                        document.querySelector('input[name="hasSurgery"]:checked').value === 'no' &&
                                        document.querySelector('input[name="hasCurrentDisease"]:checked').value === 'no';

                                    medicalCertDiv.style.display = allNo ? 'none' : 'block';

                                    if (allNo) {
                                        medicalCert.value = '';
                                    }

                                    validateStepThree();
                                }


                                function validateStepThree() {
                                    const hasDisease = document.querySelector('input[name="hasDisease"]:checked').value === 'yes';
                                    const hasSurgery = document.querySelector('input[name="hasSurgery"]:checked').value === 'yes';
                                    const hasCurrentDisease = document.querySelector('input[name="hasCurrentDisease"]:checked').value === 'yes';

                                    const allNo = !hasDisease && !hasSurgery && !hasCurrentDisease;

                                    const filledFields =
                                        disease.value.trim() !== '' &&
                                        recentSurgery.value.trim() !== '' &&
                                        currentDisease.value.trim() !== '';

                                    const hasMedicalCert = medicalCert.files.length > 0;

                                    // Enable if:
                                    // 1. All answers are no (no file required), OR
                                    // 2. All text inputs are filled AND medical cert uploaded
                                    if (allNo || (filledFields && hasMedicalCert)) {
                                        nextStepThree.disabled = false;
                                    } else {
                                        nextStepThree.disabled = true;
                                    }
                                }

                                // Add event listeners for radio buttons
                                document.querySelectorAll('input[name="hasDisease"]').forEach(radio => radio.addEventListener('change', handleRadioToggle));
                                document.querySelectorAll('input[name="hasSurgery"]').forEach(radio => radio.addEventListener('change', handleRadioToggle));
                                document.querySelectorAll('input[name="hasCurrentDisease"]').forEach(radio => radio.addEventListener('change', handleRadioToggle));

                                // Input listeners
                                [disease, recentSurgery, currentDisease].forEach(input => input.addEventListener('input', validateStepThree));
                                medicalCert.addEventListener('change', validateStepThree);

                                // Initial trigger in case "No" is pre-selected
                                handleRadioToggle();

                                // Medical cert container

                            </script>

                            <div class="main- overflow-hidden" id="stepfour" style="display:none">
                                <h3>Will someone accompany you?</h3>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="hasCompanion">
                                    <label class="form-check-label" for="hasCompanion">Yes</label>
                                </div>

                                <div id="fieldContainer" style="display:none">
                                    <h4>Companion</h4>
                                    <div class="field-set">
                                        <div class="mb-3">
                                            <label for="name1" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name1" name="name[]">
                                        </div>
                                        <div class="mb-3">
                                            <label for="relationship1" class="form-label">Relationship to Patient</label>
                                            <input type="text" class="form-control" id="relationship1" name="relationship[]"
                                                placeholder="e.g., Spouse, Parent, Friend">
                                        </div>
                                    </div>
                                </div>


                                <button type="button" class="btn btn-secondary" id="addMoreFields" disabled>Add
                                    More</button>
                                <div class="navigation-buttons mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Previous</button>
                                    <button type="button" class="btn btn-primary" id="nextStepFour"
                                        onclick="nextStep(5)">Next</button>
                                </div>
                            </div>

                            <script>
                                const hasCompanion = document.getElementById('hasCompanion');
                                const fieldContainer = document.getElementById('fieldContainer');
                                const name1 = document.getElementById('name1');
                                const gender1 = document.getElementById('gender1');
                                const age1 = document.getElementById('age1');
                                const addMoreBtn = document.getElementById('addMoreFields');
                                const nextStepFour = document.getElementById('nextStepFour');

                                function validateStepFour() {
                                    if (
                                        name1.value.trim() !== '' &&
                                        gender1.value !== '' &&
                                        age1.value.trim() !== ''
                                    ) {
                                        addMoreBtn.disabled = false;
                                        nextStepFour.disabled = false;
                                    } else {
                                        addMoreBtn.disabled = true;
                                        nextStepFour.disabled = true;
                                    }
                                }

                                // Toggle companion fields
                                hasCompanion.addEventListener('change', () => {
                                    if (hasCompanion.checked) {
                                        fieldContainer.style.display = 'block';
                                        validateStepFour(); // Initial validation
                                    } else {
                                        fieldContainer.style.display = 'none';
                                        addMoreBtn.disabled = true;
                                        nextStepFour.disabled = false; // Allow to proceed if no companion
                                    }
                                });

                                // Validate on input changes
                                [name1, gender1, age1].forEach(input => {
                                    input.addEventListener('input', validateStepFour);
                                    input.addEventListener('change', validateStepFour);
                                });
                            </script>

                            <div class="main-content overflow-hidden" id="stepfive" style="display:none">

                                <h3>Downpayment</h3>
                                <div class="row">
                                    <div class="col-lg-8 px-5 mt-3">
                                        <p>To continue, please send the following amount as <b>15% downpayment</b> for our
                                            services
                                            to our
                                            GCash account using the QR. Upload the
                                            receipt in the form below.</p>

                                        <h5 id="costlabel">Please pay <span class="fw-bold">PHP<span
                                                    id="costDisplay"></span></span>
                                        </h5>
                                        <div class="mb-3">
                                            <label for="proofimg" class="form-label">GCash Payment Receipt</label>
                                            <input type="file" class="form-control" id="proofimg" name="proofimg"
                                                accept=".jpg,.jpeg,.png">
                                        </div>
                                        <div class="mb-3">
                                            <label for="refnum" class="form-label">Reference Number</label>
                                            <input type="text" class="form-control" id="refnum" name="refnum"
                                                placeholder="Enter reference number">
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <img src="https://crfaphilippines.org/wp-content/uploads/2023/02/bsgc-gcash-qr.jpg"
                                                class="w-75">
                                        </div>
                                    </div>

                                </div>
                                <div class="navigation-buttons mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(4)">Previous</button>
                                    <button type="button" class="btn btn-primary" id="nextStepFive" onclick="nextStep(6)"
                                        disabled>Next</button>

                                </div>

                            </div>


                            <script>
                                const proofImg = document.getElementById('proofimg');
                                const refNum = document.getElementById('refnum');
                                const nextStepFive = document.getElementById('nextStepFive');

                                function validateStepFive() {
                                    const hasImage = proofImg.files.length > 0;
                                    const hasRef = refNum.value.trim() !== '';

                                    nextStepFive.disabled = !(hasImage && hasRef);
                                }

                                proofImg.addEventListener('change', validateStepFive);
                                refNum.addEventListener('input', validateStepFive);
                            </script>
                            <div class="main-content overflow-hidden" id="stepsix" style="display:none">
                                <div id="summary">

                                    <h3>Summary of Your Selection</h3>
                                    <!-- Changed to include the value properly in the input -->
                                    <input type="hidden" id="username" name="username"
                                        value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">
                                    <p><strong>Dentist:</strong> <span id="dentistSummary"></span></p>
                                    <p><strong>Service:</strong> <span id="serviceSummary"></span></p>
                                    <p><strong>Date:</strong> <span id="dateSummary"></span></p>
                                    <p><strong>Time:</strong> <span id="timeSummary"></span></p>
                                </div>
                                <div class="navigation-buttons mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(5)">Previous</button>
                                    <button type="submit" class="btn btn-success" id="submit">Submit</button>
                                </div>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for unavailable time slot -->
        <div class="modal" id="unavailableModal">
            <div class="modal-header">
                Time Slot Unavailable
            </div>
            <div class="modal-body">
                The selected time slot is not available. Please choose another time.
            </div>
            <div class="modal-footer">
                <button id="closeModalButton">Close</button>
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
        <div id="popupModal" class="custom-popup">
            <div class="custom-popup-content">
                <span id="closePopup" class="close-popup">&times;</span>
                <p id="popupMessage"></p>
            </div>
        </div>
        <!-- JavaScript to handle month/year selection and date/time selection -->
        <script>

            function showStep(step) {
                const steps = document.querySelectorAll('.main-content');
                steps.forEach((el, index) => {
                    el.style.display = (index === step - 1) ? 'block' : 'none';
                });
            }

            function nextStep(step) {
                showStep(step);
            }

            function prevStep(step) {
                showStep(step);
            }
            document.addEventListener('DOMContentLoaded', function () {
                const maxFields = 3;
                let currentFieldCount = 1;
                const addMoreFieldsButton = document.getElementById('addMoreFields');
                const fieldContainer = document.getElementById('fieldContainer');

                function checkFields() {
                    const lastNameField = document.querySelector(`#name${currentFieldCount}`);
                    const lastRelationshipField = document.querySelector(`#relationship${currentFieldCount}`);

                    if (lastNameField.value && lastRelationshipField.value) {
                        addMoreFieldsButton.disabled = false;
                    } else {
                        addMoreFieldsButton.disabled = true;
                    }
                }

                document.querySelectorAll('input, select').forEach(field => {
                    field.addEventListener('input', checkFields);
                });

                addMoreFieldsButton.addEventListener('click', function () {
                    if (currentFieldCount < maxFields) {
                        currentFieldCount++;
                        const fieldSet = document.createElement('div');
                        fieldSet.classList.add('field-set');
                        fieldSet.innerHTML = `
                <div class="mb-3">
                    <label for="name${currentFieldCount}" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name${currentFieldCount}" name="name[]" required>
                </div>
                <div class="mb-3">
                    <label for="relationship${currentFieldCount}" class="form-label">Relationship to the Patient</label>
                    <input type="number" class="form-control" id="relationship${currentFieldCount}" name="relationship[]" required>
                </div>
            `;
                        fieldContainer.appendChild(fieldSet);
                        document.querySelectorAll('input, select').forEach(field => {
                            field.addEventListener('input', checkFields);
                        });

                        if (currentFieldCount === maxFields) {
                            addMoreFieldsButton.disabled = true;
                        } else {
                            addMoreFieldsButton.disabled = true;
                        }
                    }

                });
            });
            document.getElementById('appointmentForm').addEventListener('submit', function (e) {
                e.preventDefault();

                // Changed to use .value instead of .textContent for input element
                const username = document.getElementById('username').value;
                const dentist = document.getElementById('dentistSummary').textContent;
                const service = document.getElementById('serviceSummary').textContent;
                const date = document.getElementById('dateSummary').textContent;
                const time = document.getElementById('timeSummary').textContent;

                // Add validation
                if (!username) {
                    console.error('Username not found');
                    alert('Error: Username not found. Please try logging in again.');
                    return;
                }

                const formData = new FormData();
                formData.append('username', username);
                formData.append('dentist', dentist);
                formData.append('service', service);
                formData.append('date', date);
                formData.append('time', time);

                // Add debug logging
                console.log('Sending data:', {
                    username,
                    dentist,
                    service,
                    date,
                    time
                });

                fetch('send_email.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(text => {
                        console.log('Raw response:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parsing error:', e);
                            throw new Error('Invalid JSON response from server');
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('appointmentForm').submit();
                        } else {
                            throw new Error(data.message || 'Unknown error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            const daysContainer = document.getElementById('calendar');
            const monthSelect = document.getElementById('monthSelect');
            const yearSelect = document.getElementById('yearSelect');
            const service = document.getElementById('service');
            const appointment_time = document.getElementById('appointment_time');
            const selectedInfo = document.getElementById('selectedInfo');
            const displayDateTime = document.getElementById('displayDateTime');
            //  const nextButton = document.getElementById('nextButton');


            // Modal elements
            const unavailableModal = document.getElementById('unavailableModal');
            const closeModalButton = document.getElementById('closeModalButton');

            // Populate the month dropdown with only the months of the year
            function populateMonths() {

                <?php
                $currentmonth = date('n');
                $months = []; // Initialize an empty array
            

                for ($i = $currentmonth; $i <= 12; $i++) {
                    $months[] = date('F', mktime(0, 0, 0, $i, 1));

                }
                echo "let months = " . json_encode($months) . ";";

                ?>
                //   const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const monthcurrent = <?= $currentmonth ?>;


                for (let i = 0; i <= months.length - 1; i++) { // All months
                    const option = document.createElement('option');
                    option.value = i + monthcurrent - 1; // Month index
                    option.textContent = months[i];
                    monthSelect.appendChild(option);
                }
            }
            // Add the event listener for service change
            document.getElementById('service').addEventListener('change', updateSlots);
            // Automatically load calendar when month or year is selected
            function loadCalendar() {
                const selectedMonth = parseInt(monthSelect.value);
                const selectedYear = parseInt(yearSelect.value);
                loadCalendarDays(selectedYear, selectedMonth);
            }

            monthSelect.addEventListener('change', loadCalendar);
            yearSelect.addEventListener('change', loadCalendar);

            function loadCalendarDays(year, month) {
                // Clear previous days
                daysContainer.innerHTML = '';

                // Get today's date
                const today = new Date();
                today.setDate(today.getDate() - 1);

                // Get the number of days in the selected month
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                // Get the first day of the month to determine alignment
                const firstDayOfWeek = new Date(year, month, 1).getDay(); // Get day of week (0-6)

                // Generate empty divs for days before the first day of the month (if any)
                for (let i = 0; i < firstDayOfWeek; i++) {
                    const emptyDiv = document.createElement('div');
                    daysContainer.appendChild(emptyDiv); // Empty space before first day
                }

                // Generate day elements
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);

                    // Check if the date is before today
                    if (date < today) {
                        const dayDiv = document.createElement('div');
                        dayDiv.classList.add('day', 'unavailable'); // Mark previous dates as unavailable
                        dayDiv.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                        dayDiv.textContent = day;

                        daysContainer.appendChild(dayDiv); // Add to calendar as unavailable

                    } else { // For today and future dates
                        const dayDiv = document.createElement('div');
                        dayDiv.classList.add('day', 'available'); // Assuming all future days are available
                        dayDiv.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                        dayDiv.textContent = day;

                        // Add click event to each day
                        dayDiv.addEventListener('click', function () {
                            if (!this.classList.contains('unavailable')) { // Only allow selection if it's not unavailable
                                // Remove selected class from all days
                                const allDays = document.querySelectorAll('.day');
                                allDays.forEach(d => d.classList.remove('selected'));

                                // Add selected class to the clicked day
                                this.classList.add('selected');

                                // Show time slots if the day is available
                                timeSlotsContainer.style.display = 'block';
                                // nextButton.style.display = 'block'; // Show next button when a date is selected
                            }
                        });

                        daysContainer.appendChild(dayDiv); // Add to calendar as available
                    }
                }
            }

            const start_time = '08:00'; // Using 24-hour format for clarity
            const end_time = '17:00'; // Using 24-hour format for clarity

            /*             function getAvailableTimeSlots(startTime, endTime, duration) {
                            const slots = [];
                            let currentTime = new Date(`1970-01-01T${startTime}:00`);
                            const endTimeDate = new Date(`1970-01-01T${endTime}:00`);
 
                            while (currentTime < endTimeDate) {
                                let nextTime = new Date(currentTime.getTime() + duration * 60000);
                                if (nextTime <= endTimeDate) {
                                    // Format the time slot
                                    slots.push(`${currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${nextTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`);
                                }
                                currentTime = nextTime; // Move to the next time slot
                            }
                            return slots; // Return the array of available time slots
                        }
             */
            /* 
                        function updateSlots() {
                            const serviceSelect = document.getElementById('service');
                            const appointmentSelect = document.getElementById('appointment_time');
                            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
 
                            if (!selectedOption.value) {
                                // If no service is selected, clear the appointment time slots
                                appointmentSelect.innerHTML = '<option value="">Select a time slot</option>';
                                return;
                            }
 
                            const duration = parseInt(selectedOption.getAttribute('data-duration'));
 
                            // Debugging log
                            console.log("Selected Duration:", duration);
 
                            // Clear existing options
                            appointmentSelect.innerHTML = '<option value="">Select a time slot</option>';
 
                            // Get available slots
                            const availableSlots = getAvailableTimeSlots(start_time, end_time, duration);
 
                            // Debugging log for available slots
                            console.log("Available Slots:", availableSlots);
 
                            // Populate appointment time slots
                            availableSlots.forEach(slot => {
                                const option = document.createElement('option');
                                option.value = slot;
                                option.textContent = slot;
                                appointmentSelect.appendChild(option);
                            });
                        } */

            function updateSlots() {
                const serviceSelect = document.getElementById('service');
                const appointmentSelect = document.getElementById('appointment_time');
                const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];

                if (!selectedOption.value) {
                    appointmentSelect.innerHTML = '<option value="">Select a time slot</option>';
                    return;
                }

                const duration = parseInt(selectedOption.getAttribute('data-duration'));
                const selectedDate = document.querySelector('.day.selected') ? document.querySelector('.day.selected').dataset.date : null;

                if (!selectedDate) {
                    appointmentSelect.innerHTML = '<option value="">Select a date first</option>';
                    return;
                }

                appointmentSelect.innerHTML = '<option value="">Loading available slots...</option>';

                // AJAX request to fetch available slots
                fetch('get_available_slots.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `date=${selectedDate}&duration=${duration}`
                })
                    .then(response => response.text())
                    .then(text => {
                        console.log("Raw Response:", text);
                        try {
                            const availableSlots = JSON.parse(text);
                            console.log("Available Slots:", availableSlots);


                            appointmentSelect.innerHTML = '<option value="">Select a time slot</option>';


                            availableSlots.forEach(slot => {
                                const option = document.createElement('option');
                                option.value = slot;
                                option.textContent = slot;
                                appointmentSelect.appendChild(option);
                            });
                        } catch (error) {
                            console.error("Error parsing JSON:", error, "\nServer Response:", text);
                        }
                    })
                    .catch(error => console.error('Error fetching available slots:', error));

            }


            function fetchCurrentTime() {
                $.ajax({
                    url: '../db/current_timezone.php',
                    method: 'GET',
                    success: function (data) {
                        $('#datetime').html(data);
                    },
                    error: function () {
                        console.error('Error fetching time.');
                    }
                });
            }

            setInterval(fetchCurrentTime, 1000);
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


            // Load initial calendar for year only (2025)
            populateMonths();
            loadCalendarDays(2025, new Date().getMonth()); // Load calendar for year only

            document.getElementById('dentist').addEventListener('change', updateSummary);
            document.getElementById('service').addEventListener('change', updateSummary);
            document.getElementById('appointment_time').addEventListener('change', updateSummary);

            // Event listener for selecting a date
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('available')) {
                    const allDays = document.querySelectorAll('.day');
                    allDays.forEach(day => day.classList.remove('selected'));
                    e.target.classList.add('selected');
                    updateSlots();
                    updateSummary();
                }
            });

            function updateSummary() {
                const dentist = document.getElementById('dentist').value || 'Not selected';
                const service = document.getElementById('service').options[document.getElementById('service').selectedIndex].text || 'Not selected';
                const date = document.querySelector('.day.selected') ? document.querySelector('.day.selected').dataset.date : 'Not selected';
                const time = document.getElementById('appointment_time').value || 'Not selected';

                document.getElementById('dentistSummary').innerText = dentist;
                document.getElementById('serviceSummary').innerText = service;
                document.getElementById('dateSummary').innerText = date;
                document.getElementById('timeSummary').innerText = time;

                // Show summary only if all fields are selected
                if (dentist !== 'Not selected' && service !== 'Not selected' && date !== 'Not selected' && time !== 'Not selected') {
                    document.getElementById('summary').style.display = 'block';
                } else {
                    //       document.getElementById('summary').style.display = 'none';
                }
            }

            document.getElementById('appointmentForm').addEventListener('submit', function (event) {
                event.preventDefault();

                const selectedDentist = document.getElementById('dentist').value;
                const selectedService = document.getElementById('service').value;
                const selectedDate = document.querySelector('.day.selected') ? document.querySelector('.day.selected').dataset.date : null;
                const selectedTime = document.getElementById('appointment_time').value;

                if (!selectedDentist || !selectedService || !selectedDate || !selectedTime) {
                    displayPopupMessage("Please select all appointment details before submitting.", false);
                    return;
                }

                const formData = new FormData(this);
                formData.append('dentist', selectedDentist);
                formData.append('service', selectedService);
                formData.append('appointment_date', selectedDate);
                formData.append('appointment_time', selectedTime);

                fetch('patCalendar.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        displayPopupMessage("Appointment booked successfully! You’ll be emailed once the appointment approves or rejects.", true);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        displayPopupMessage("Error booking appointment. Please try again.", false);
                    });
            });

            // Function to display the pop-up message
            function displayPopupMessage(message, isSuccess) {
                const popupModal = document.getElementById('popupModal');
                const popupMessage = document.getElementById('popupMessage');
                popupMessage.textContent = message;
                popupModal.style.display = 'block';

                // Close the pop-up when the close button is clicked
                document.getElementById('closePopup').onclick = function () {
                    popupModal.style.display = 'none';
                    if (isSuccess) {
                        window.location.href = 'patDashboard.php';
                    }
                };

                // Close the pop-up when clicking outside the modal
                window.onclick = function (event) {
                    if (event.target === popupModal) {
                        popupModal.style.display = 'none';
                        if (isSuccess) {
                            window.location.href = 'patDashboard.php';
                        }
                    }
                };
            }

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

        <!-- Modal for unavailable time slot -->
        <div class="modal" id="unavailableModal">
            <div class="modal-header">
                Time Slot Unavailable
            </div>
            <div class="modal-body">
                The selected time slot is not available. Please choose another time.
            </div>
            <div class="modal-footer">
                <button id="closeModalButton">Close</button>
            </div>
        </div>

    </body>

    </html>
<?php } ?>