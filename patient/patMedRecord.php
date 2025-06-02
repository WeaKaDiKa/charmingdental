<?php
session_start();
include '../db/config.php'; // Database connection ($db)

if (!isset($_SESSION['id'])) {
    header("Location: patLogin.php");
    exit();
}

$userid = $_SESSION['id'];

// Initialize errors array
$errors = [];

// Fetch existing medical record
$sql = "SELECT * FROM medical WHERE usersid = ? ORDER BY medid DESC LIMIT 1";
$stmt = $db->prepare($sql);
$medical = null;
if ($stmt) {
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $medical = $result->fetch_assoc();
    $stmt->close();
}

// Prepare current diseases array for checkbox pre-fill
$currentDiseases = [];
if ($medical && !empty($medical['current_disease']) && $medical['current_disease'] !== 'N/A') {
    $currentDiseases = array_map('trim', explode(',', $medical['current_disease']));
}

// Add this line to define $hasSubmitted
$hasSubmitted = !empty($medical);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_health'])) {
    // Required radio buttons validation
    $requiredRadios = ['hasDisease', 'hasSurgery', 'hasCurrentDisease', 'takes_medications'];
    foreach ($requiredRadios as $radio) {
        if (!isset($_POST[$radio]) || !in_array($_POST[$radio], ['yes', 'no'])) {
            $errors[$radio] = "Please answer the question.";
        }

    }

    // Retrieve and trim inputs
    $hasDisease = $_POST['hasDisease'] ?? 'no';
    $hasSurgery = $_POST['hasSurgery'] ?? 'no';
    $hasCurrentDisease = $_POST['hasCurrentDisease'] ?? 'no';
    $takes_medications = $_POST['takes_medications'] ?? 'no';

    $disease = ($hasDisease === 'no') ? 'N/A' : trim($_POST['disease'] ?? '');
    $recent_surgery = ($hasSurgery === 'no') ? 'N/A' : trim($_POST['recent_surgery'] ?? '');


   if ($hasCurrentDisease === 'no') {
        $current_disease_str = 'N/A';
        $current_disease = [];
        $current_disease_other = '';
    } else {
        $current_disease = $_POST['current_disease'] ?? [];
        if (!is_array($current_disease)) {
            $current_disease = [];
        }
        $current_disease_other = trim($_POST['current_disease_other'] ?? '');

        // Validate "Other" field BEFORE appending it
        if (in_array('Other', $current_disease) && $current_disease_other === '') {
            $errors['current_disease_other'] = "Please specify your 'Other' current disease.";
        }

        // Only append 'Other' if not empty
        if (in_array('Other', $current_disease) && !empty($current_disease_other)) {
            $current_disease[] = $current_disease_other;
        }

        $current_disease_str = implode(', ', $current_disease);

        // Also validate that at least one disease is selected
        if (empty($current_disease)) {
            $errors['current_disease'] = "Please select at least one current disease.";
        }
    }

    $medications = ($takes_medications === 'no') ? 'N/A' : trim($_POST['medications'] ?? '');

    // Begin validations

    // Disease required if hasDisease = yes
    if ($hasDisease === 'yes' && $disease === '') {
        $errors['disease'] = "Please specify your allergies.";
    }

    if ($hasSurgery === 'yes') {
        $recentSurgeryInputEmpty = trim($recent_surgery) === '';
        $existingRecentSurgeryEmpty = empty($medical['recent_surgery']) || $medical['recent_surgery'] === 'N/A';

        if ($recentSurgeryInputEmpty && $existingRecentSurgeryEmpty) {
            $errors['recent_surgery'] = "Please specify your recent surgery details.";
        }
    }

    // Current diseases validation if hasCurrentDisease = yes
    if ($hasCurrentDisease === 'yes') {
        if (empty($current_disease)) {
            $errors['current_disease'] = "Please select at least one current disease.";
        }
        if (in_array('Other', $current_disease) && $current_disease_other === '') {
            $errors['current_disease'] = "Please specify your 'Other' current disease.";
        }
    }

    // Medications required if takes_medications = yes
    if ($takes_medications === 'yes' && $medications === '') {
        $errors['takes_medications'] = "Please list your medications or select 'No' if none.";
    }

  
    $existingFileUploaded = !empty($medical['medcertlink']);
    $medcertlink = ''; 

    if (isset($_FILES['medical_certificate']) && $_FILES['medical_certificate']['size'] > 0) {
        // User is uploading a new file - validate it
        $fileError = $_FILES['medical_certificate']['error'];
        if ($fileError === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            $fileTmpPath = $_FILES['medical_certificate']['tmp_name'];
            $fileType = mime_content_type($fileTmpPath);

            if (in_array($fileType, $allowedTypes)) {
                $target_dir = "../uploads/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                $file_name = basename($_FILES["medical_certificate"]["name"]);
                $uniquefile = uniqid() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $file_name);
                $target_file = $target_dir . $uniquefile;

                if (move_uploaded_file($fileTmpPath, $target_file)) {
                    $medcertlink = $uniquefile; // New uploaded file
                } else {
                    $errors['medical_certificate'] = "Failed to upload medical certificate.";
                }
            } else {
                $errors['medical_certificate'] = "Invalid file type for medical certificate.";
            }
        } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
            $errors['medical_certificate'] = "File upload error code: " . $fileError;
        }
    } else {
        // No new file uploaded
        if ($existingFileUploaded) {
            // Keep the existing file name
            $medcertlink = $medical['medcertlink'];
        } else {
            // No existing file either - show error
            $errors['medical_certificate'] = "Please upload a medical certificate file.";
        }
    }


    // If no errors, insert or update database
    if (empty($errors)) {
        if ($medical) {
            // Update existing record
            $sql = "UPDATE medical SET disease=?, recent_surgery=?, current_disease=?, medications=?, medcertlink=?, updated_at=NOW() WHERE medid=?";
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssssi", $disease, $recent_surgery, $current_disease_str, $medications, $medcertlink, $medical['medid']);
                $stmt->execute();
                $stmt->close();
                $_SESSION['success'] = "Health declaration updated successfully.";
            } else {
                $errors[] = "Database error: " . $db->error;
            }
        } else {
            // Insert new record
            $sql = "INSERT INTO medical (usersid, disease, recent_surgery, current_disease, medications, medcertlink, dateuploaded) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("isssss", $userid, $disease, $recent_surgery, $current_disease_str, $medications, $medcertlink);
                $stmt->execute();
                $stmt->close();
                $_SESSION['success'] = "Health declaration submitted successfully.";
            } else {
                $errors[] = "Database error: " . $db->error;
            }
        }

        // Redirect on success to avoid resubmission
        if (empty($errors)) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical and Dental Record</title>
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

        h2 {
            margin-bottom: 20px;
        }


        .selected {
            border: 2px solid #007BFF !important;
            background-color: rgb(81, 163, 81) !important;
            /* Blue border for selected date */
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
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

        .disease-checkboxes {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 columns */
            gap: 0.5rem 1.5rem; /* row gap, column gap */
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
        .disease-checkboxes {
            grid-template-columns: repeat(2, 1fr); /* 2 columns on tablets */
        }
        }
        @media (max-width: 480px) {
        .disease-checkboxes {
            grid-template-columns: 1fr; /* 1 column on mobile */
        }
        }

        .modal-dialog-scrollable .modal-body {
            scrollbar-width: thin;               /* Firefox */
            scrollbar-color: #4a90e2 #f0f0f0;   /* Firefox */
        }

        /* WebKit browsers */
        .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
            width: 12px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
            background-color: #4a90e2;
            border-radius: 10px;
            border: 3px solid #f0f0f0;
        }

        .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
            background-color: #357ABD;
        }

        .error-message {
            color: #D8000C;       /* Red color */
            font-size: 12px;      /* Small text */
            margin-top: 4px;      /* Small spacing above */
            font-weight: normal;  /* Normal weight for subtlety */
        }

        input[required] + label::after,
        select[required] + label::after,
        textarea[required] + label::after {
            content: " *";
            color: red;
            font-weight: bold;
        }

    .form-check-input {
        width: 1.25rem !important;
        height: 1.25rem !important;
    }
    </style>
</head>

<body>
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper overflow-hidden">
        <?php
        $navactive = "patMedRecord";
        require_once "../db/nav.php";
        ?>
        <div class="main-content overflow-hidden">
        <div class="card">
            <div class="card-body">
           <?php if (!$hasSubmitted): ?>
            <h3>Please fill out your Health Declaration Form before scheduling an appointment</h3>
            <p>
                    To ensure your safety and provide you with the best possible care, please answer the following questions truthfully and accurately. 
                    This information will be kept confidential and used solely for your medical assessment and treatment planning.
                    </p>
                    <p style="font-style: italic; font-size: 0.9em; color: #555;">
                    <strong>Disclaimer:</strong> The information provided in this form is for preliminary assessment purposes only and does not replace a full medical examination. 
                    Please consult your healthcare provider for a comprehensive evaluation. By submitting this form, you acknowledge that withholding or providing false information may affect your treatment outcomes.
                    </p>
             </p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#healthDeclarationModal">Fill Health Declaration</button>
        <?php else: ?>
            <h3>Health Declaration Form</h3>
            <ul class="list-group mb-3">
                <li class="list-group-item"><strong>Allergies:</strong> <?php echo htmlspecialchars($medical['disease']); ?></li>
                <li class="list-group-item"><strong>Recent Surgery:</strong> <?php echo htmlspecialchars($medical['recent_surgery']); ?></li>
                <li class="list-group-item"><strong>Current Medical Condition/s:</strong> <?php echo htmlspecialchars($medical['current_disease']); ?></li>
                <li class="list-group-item"><strong>Medications:</strong> <?php echo nl2br(htmlspecialchars($medical['medications'] ?? '')); ?></li>
                <li class="list-group-item"><strong>Medical Certificate:</strong>
                    <?php if (!empty($medical['medcertlink'])): ?>
                        <a href="../uploads/<?php echo htmlspecialchars($medical['medcertlink']); ?>" target="_blank">View Uploaded File</a>
                    <?php else: ?>
                        None uploaded
                    <?php endif; ?>
                </li>
            </ul>

            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#healthDeclarationModal">
                <?php echo $medical ? 'Update' : 'Fill'; ?> Health Declaration
            </button>
        <?php endif; ?>
    
    <!-- Modal for Health Declaration Form -->
    <div class="modal fade" id="healthDeclarationModal" tabindex="-1" aria-labelledby="healthDeclarationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form id="healthDeclarationForm" method="POST" enctype="multipart/form-data" class="modal-content" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title" id="healthDeclarationModalLabel">Health Declaration Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Disease/Allergies -->
                        <div class="mb-3">
                        <label>Do you have any allergies to medications, dental materials (e.g. latex), and other substances?:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hasDisease" id="hasDiseaseYes" value="yes" <?php echo (isset($_POST['hasDisease']) ? ($_POST['hasDisease'] === 'yes' ? 'checked' : '') : ($medical && $medical['disease'] !== 'N/A' ? 'checked' : '')); ?>>
                            <label class="form-check-label" for="hasDiseaseYes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hasDisease" id="hasDiseaseNo" value="no" <?php echo (isset($_POST['hasDisease']) ? ($_POST['hasDisease'] === 'no' ? 'checked' : '') : ($medical && $medical['disease'] === 'N/A' ? 'checked' : '')); ?>>
                            <label class="form-check-label" for="hasDiseaseNo">No</label>
                        </div>
                        <?php if (!empty($errors['hasDisease'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['hasDisease']); ?></div>
                         <?php endif; ?>
                        <div class="mb-3" id="diseaseField" style="display:none;">
                            <input type="text" name="disease" class="form-control" placeholder="Please specify" value="<?php echo htmlspecialchars($_POST['disease'] ?? ($medical['disease'] ?? '')); ?>" <?php echo (isset($_POST['hasDisease']) ? ($_POST['hasDisease'] === 'yes' ? '' : 'disabled') : ($medical && $medical['disease'] !== 'N/A' ? '' : 'disabled')); ?>>
                            <?php if (!empty($errors['disease'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['disease']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                       <!-- Current Diseases -->
                        <div class="mb-3">
                            <label class="form-label">Do you currently have any medical conditions?</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="hasCurrentDisease" id="hasCurrentDiseaseYes" value="yes"
                                    <?php echo (isset($_POST['hasCurrentDisease']) ? ($_POST['hasCurrentDisease'] === 'yes' ? 'checked' : '') : ($medical && $medical['current_disease'] !== 'N/A' ? 'checked' : '')); ?>>
                                <label class="form-check-label" for="hasCurrentDiseaseYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="hasCurrentDisease" id="hasCurrentDiseaseNo" value="no"
                                    <?php echo (isset($_POST['hasCurrentDisease']) ? ($_POST['hasCurrentDisease'] === 'no' ? 'checked' : '') : ($medical && $medical['current_disease'] === 'N/A' ? 'checked' : '')); ?>>
                                <label class="form-check-label" for="hasCurrentDiseaseNo">No</label>
                            </div>
                            <?php if (!empty($errors['hasCurrentDisease'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['hasCurrentDisease']); ?></div>
                            <?php endif; ?>
                            <?php
                            // Determine if the disease field should be shown
                            $showDiseaseField = false;
                            if (isset($_POST['hasCurrentDisease'])) {
                                $showDiseaseField = $_POST['hasCurrentDisease'] === 'yes';
                            } elseif ($medical && $medical['current_disease'] !== 'N/A') {
                                $showDiseaseField = true;
                            }
                            ?>

                            <div class="mb-3" id="currentDiseaseField" style="display:none;">
                                <label class="form-label">Please select:</label>
                                <div class="disease-checkboxes">
                                    <?php
                                    $possibleDiseases = [
                                        'Diabetes',
                                        'Hypertension (High Blood Pressure)',
                                        'Asthma',
                                        'Heart Disease',
                                        'Thyroid Disorder',
                                        'Bleeding Disorder',
                                        'Hepatitis (A, B, or C)',
                                        'HIV/AIDS',
                                        'Tuberculosis',
                                        'Epilepsy or Seizure Disorder',
                                        'Osteoporosis',
                                        'Other'
                                    ];
                                    foreach ($possibleDiseases as $diseaseOption) {
                                        $checked = '';
                                        if (isset($_POST['current_disease']) && is_array($_POST['current_disease'])) {
                                            $checked = in_array($diseaseOption, $_POST['current_disease']) ? 'checked' : '';
                                        } elseif (!empty($currentDiseases)) {
                                            $checked = in_array($diseaseOption, $currentDiseases) ? 'checked' : '';
                                        }
                                        echo '<div class="form-check form-check-inline">';
                                        echo '<input class="form-check-input" type="checkbox" name="current_disease[]" id="cd_' . htmlspecialchars($diseaseOption) . '" value="' . htmlspecialchars($diseaseOption) . '" ' . $checked . '>';
                                        echo '<label class="form-check-label" for="cd_' . htmlspecialchars($diseaseOption) . '">' . htmlspecialchars($diseaseOption) . '</label>';
                                        echo '</div>';
                                    }
                                    ?>
                                    <input type="text" name="current_disease_other" class="form-control mt-2" placeholder="If Other, please specify"
                                        value="<?php echo htmlspecialchars($_POST['current_disease_other'] ?? ''); ?>">
                                    <?php if (!empty($errors['current_disease'])): ?>
                                        <div class="error-message"><?php echo htmlspecialchars($errors['current_disease']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($errors['current_disease_other'])): ?>
                                        <div class="error-message"><?php echo htmlspecialchars($errors['current_disease_other']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
        
                        </div>
                        <!-- Medications -->
                        <div class="mb-3">
                            <label class="form-label">Do you currently take any medications? </label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="takes_medications" id="takesMedicationsYes" value="yes" <?php echo (isset($_POST['takes_medications']) ? ($_POST['takes_medications'] === 'yes' ? 'checked' : '') : ($medical && $medical['medications'] !== 'N/A' ? 'checked' : '')); ?>>
                                <label class="form-check-label" for="takesMedicationsYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="takes_medications" id="takesMedicationsNo" value="no" <?php echo (isset($_POST['takes_medications']) ? ($_POST['takes_medications'] === 'no' ? 'checked' : '') : ($medical && $medical['medications'] === 'N/A' ? 'checked' : '')); ?>>
                                <label class="form-check-label" for="takesMedicationsNo">No</label>
                            </div>
                            <div class="mb-3" id="medicationsField" style="display:none;">
                                <textarea name="medications" class="form-control" rows="3" placeholder="e.g., Aspirin 100mg daily" <?php echo (isset($_POST['takes_medications']) ? ($_POST['takes_medications'] === 'yes' ? '' : 'disabled') : ($medical && $medical['medications'] !== 'N/A' ? '' : 'disabled')); ?>>
                                    <?php echo htmlspecialchars($_POST['medications'] ?? $medical['medications'] ?? ''); ?>
                                </textarea>
                                <?php if (!empty($errors['takes_medications'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['takes_medications']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Surgery -->
                        <div class="mb-3">
                            <label class="mb-3">Have you had any recent surgery?</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="hasSurgery" id="hasSurgeryYes" value="yes" <?php echo (isset($_POST['recent_surgery']) ? ($_POST['recent_surgery'] === 'yes' ? 'checked' : '') : ($medical && $medical['recent_surgery'] !== 'N/A' ? 'checked' : '')); ?>>
                                <label class="form-check-label" for="hasSurgeryYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="hasSurgery" id="hasSurgeryNo" value="no" <?php echo (isset($_POST['recent_surgery']) ? ($_POST['recent_surgery'] === 'no' ? 'checked' : '') : ($medical && $medical['recent_surgery'] === 'N/A' ? 'checked' : '')); ?>>
                                <label class="form-check-label" for="hasSurgeryNo">No</label>
                            </div>
                            <?php if (!empty($errors['hasSurgery'])): ?>
                                 <div class="error-message"><?php echo htmlspecialchars($errors['hasSurgery']); ?></div>
                            <?php endif; ?>
                            <?php
                            // Determine if the surgery detail input should be shown
                                $showSurgeryField = (isset($_POST['hasSurgery']) && $_POST['hasSurgery'] === 'yes') || ($medical && $medical['recent_surgery'] !== 'N/A');
                            ?>

                            <div class="mb-3" id="surgeryField" style="display:none;">
                                <input type="text" name="recent_surgery" class="form-control mb-2" placeholder="Please specify" value="<?php echo htmlspecialchars($_POST['recent_surgery'] ?? ($medical['recent_surgery'] ?? '')); ?>" <?php echo (isset($_POST['hasSurgery']) ? ($_POST['hasSurgery'] === 'yes' ? '' : 'disabled') : ($medical && $medical['recent_surgery'] !== 'N/A' ? '' : 'disabled')); ?>>
                                <?php if (!empty($errors['recent_surgery'])): ?>
                                    <div class="error-message"><?php echo htmlspecialchars($errors['recent_surgery']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>


                        <!-- Medical Certificate Upload -->
                        <div class="mb-3" id="medicalCertDiv">
                            <label for="medical_certificate" class="form-label">Upload Medical Certificate (optional)</label>
                            <input type="file" name="medical_certificate" id="medical_certificate" accept=".jpeg,.jpg,.png,.pdf" class="form-control" />

                            <?php if (!empty($medical['medcertlink'])): ?>
                                <p>Current file: <a href="../uploads/<?php echo htmlspecialchars($medical['medcertlink']); ?>" target="_blank" rel="noopener noreferrer">View</a></p>
                            <?php else: ?>
                                <p class="text-danger">No medical certificate uploaded yet.</p>
                            <?php endif; ?>

                            <!-- Display error message if exists -->
                            <?php if (!empty($errors['medical_certificate'])): ?>
                                <div class="text-danger mt-2">
                                    <?php echo htmlspecialchars($errors['medical_certificate']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" name="submit_health" class="btn btn-primary" id="submitHealthBtn">
                                <?php echo $medical ? 'Update' : 'Submit'; ?>
                            </button>
                            <button type="button" class="btn btn-secondary" id="closeModalBtn" data-bs-dismiss="modal">Close</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
       document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('healthDeclarationForm');
        const submitBtn = document.getElementById('submitHealthBtn');
        const closeBtn = document.getElementById('closeModalBtn');

        if (!form) {
            console.error('Form element not found!');
            return;
        }

        // Function to toggle submit button enabled state
        function updateSubmitButtonState() {
            submitBtn.disabled = !form.checkValidity();
        }

        // Initial check on page load
        updateSubmitButtonState();

        // Listen for input changes to update submit button state
        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', updateSubmitButtonState);
            input.addEventListener('change', updateSubmitButtonState);
        });

        // Your existing toggleInput calls and other logic here...
        // Example:
        function toggleInput(radioName, inputSelector) {
            const radios = document.querySelectorAll(`input[name="${radioName}"]`);
            const input = document.querySelector(inputSelector);

            function updateInput() {
                const selected = document.querySelector(`input[name="${radioName}"]:checked`);
                if (selected && selected.value === 'yes') {
                    input.disabled = false;
                    input.parentElement.style.display = 'block';
                } else {
                    input.disabled = true;
                    input.value = '';
                    input.parentElement.style.display = 'none';
                }
                updateSubmitButtonState(); // Update submit button when toggling inputs
            }

            radios.forEach(radio => radio.addEventListener('change', updateInput));
            updateInput();
        }

        toggleInput('hasDisease', 'input[name="disease"]');

        // Add red asterisks to required labels (your existing code)...

        // Store initial form data for change detection
        const initialFormData = new FormData(form);

        // Helper: check if form fields have changed
        function isFormChanged() {
            const currentFormData = new FormData(form);
            for (const [key, value] of currentFormData.entries()) {
                if (initialFormData.get(key) !== value) return true;
            }
            return false;
        }

        // Helper: check if form is valid using HTML5 validation API
        function isFormValid() {
            return form.checkValidity();
        }

        // Prevent form submission if invalid (fallback)
        form.addEventListener('submit', function (e) {
            if (!isFormValid()) {
                e.preventDefault();
                e.stopPropagation();
                alert('Please fill all required fields before submitting.');
                form.classList.add('was-validated'); // Bootstrap validation styling
            }
        });

        // Confirm exit on Close button if form changed and invalid
        closeBtn.addEventListener('click', function (e) {
            if (isFormChanged() && !isFormValid()) {
                const confirmExit = confirm('Are you sure you want to exit? Your details will not be saved.');
                if (!confirmExit) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        });
    });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        // Toggle disease input
        function toggleInput(radioName, inputSelector) {
            const radios = document.querySelectorAll(`input[name="${radioName}"]`);
            const input = document.querySelector(inputSelector);
            function updateInput() {
                const selected = document.querySelector(`input[name="${radioName}"]:checked`);
                if (selected && selected.value === 'yes') {
                    input.removeAttribute('disabled');
                } else {
                    input.setAttribute('disabled', 'disabled');
                    if (input.tagName === 'TEXTAREA' || input.tagName === 'INPUT') {
                        input.value = '';
                    }
                }
            }
            radios.forEach(radio => {
                radio.addEventListener('change', updateInput);
            });
            updateInput();
        }
        toggleInput('hasDisease', 'input[name="disease"]');
        toggleInput('hasSurgery', 'input[name="recent_surgery"]');
        toggleInput('takes_medications', 'textarea[name="medications"]');

        // Current diseases checkboxes
        const hasCurrentDiseaseRadios = document.querySelectorAll('input[name="hasCurrentDisease"]');
        const currentDiseaseCheckboxes = document.getElementById('currentDiseaseCheckboxes');
        hasCurrentDiseaseRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'yes' && radio.checked) {
                    currentDiseaseCheckboxes.style.display = '';
                } else if (radio.value === 'no' && radio.checked) {
                    currentDiseaseCheckboxes.style.display = 'none';
                    currentDiseaseCheckboxes.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                    const otherInput = currentDiseaseCheckboxes.querySelector('input[name="current_disease_other"]');
                    if (otherInput) otherInput.value = '';
                }
            });
        });
        const selectedCurrentDisease = document.querySelector('input[name="hasCurrentDisease"]:checked');
        if (selectedCurrentDisease && selectedCurrentDisease.value === 'yes') {
            currentDiseaseCheckboxes.style.display = '';
        } else {
            currentDiseaseCheckboxes.style.display = 'none';
        }

        function toggleSurgeryRelatedFields() {
            const hasSurgery = document.querySelector('input[name="hasSurgery"]:checked')?.value === 'yes';
            document.getElementById('surgeryField').querySelector('input').disabled = !hasSurgery;
            if (!hasSurgery) {
                document.getElementById('recent_surgery').value = '';
            }
        }
        document.querySelectorAll('input[name="hasSurgery"]').forEach(radio => {
            radio.addEventListener('change', toggleSurgeryRelatedFields);
        });
        toggleSurgeryRelatedFields();
    });

    // Show/hide fields based on radio button selection
    function toggleField(radioName, fieldId, inputId) {
        const radios = document.querySelectorAll(`input[name="${radioName}"]`);
        const field = document.getElementById(fieldId);
        radios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.checked && radio.value === 'yes') {
            field.style.display = 'block';
            if (inputId) document.getElementById(inputId).value = '';
            } else if (radio.checked) {
            field.style.display = 'none';
            if (inputId) document.getElementById(inputId).value = 'N/A';
            }
            toggleMedicalCert();
        });
        });
    }
    
    function toggleMedicationsField() {
        const takesMed = document.querySelector('input[name="takes_medications"]:checked')?.value === 'yes';
        const medField = document.getElementById('medicationsField');
        const textarea = medField.querySelector('textarea');

        if (takesMed) {
            medField.style.display = 'block';
            textarea.disabled = false;
        } else {
            medField.style.display = 'none';
            textarea.disabled = true;
            textarea.value = '';
        }
    }

    document.querySelectorAll('input[name="takes_medications"]').forEach(radio => {
        radio.addEventListener('change', toggleMedicationsField);
    });

    window.addEventListener('DOMContentLoaded', toggleMedicationsField);


    function toggleMedicalCert() {
        const hasDisease = document.querySelector('input[name="hasDisease"]:checked').value === 'yes';
        const hasSurgery = document.querySelector('input[name="hasSurgery"]:checked').value === 'yes';
        const hasCurrent = document.querySelector('input[name="hasCurrentDisease"]:checked').value === 'yes';
        const medicalCertDiv = document.getElementById('medicalCertDiv');

        if (hasDisease || hasSurgery || hasCurrent) {
        medicalCertDiv.style.display = 'block';
        } else {
        medicalCertDiv.style.display = 'none';
        document.getElementById('medical_certificate').value = '';
        }
    }

    toggleField('hasDisease', 'diseaseField', 'disease');
    toggleField('hasSurgery', 'surgeryField', 'recent_surgery');
    toggleField('hasCurrentDisease', 'currentDiseaseField');

    // Initialize display on page load
    window.onload = () => {
        ['hasDisease', 'hasSurgery', 'hasCurrentDisease'].forEach(name => {
        const checked = document.querySelector(`input[name="${name}"]:checked`);
        if (checked && checked.value === 'yes') {
            document.getElementById(name === 'hasDisease' ? 'diseaseField' : name === 'hasSurgery' ? 'surgeryField' : 'currentDiseaseField').style.display = 'block';
        }
        });
        toggleMedicalCert();
    };

    document.querySelectorAll('input[required], select[required], textarea[required]').forEach(input => {
        const id = input.id;
        if (!id) return;
        const label = document.querySelector(`label[for="${id}"]`);
        if (label && !label.querySelector('.required-asterisk')) {
            const span = document.createElement('span');
            span.textContent = ' *';
            span.style.color = 'red';
            span.classList.add('required-asterisk');
            label.appendChild(span);
        }
    });


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

    </script>
    
</body>
</html>
