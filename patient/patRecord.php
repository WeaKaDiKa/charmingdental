<?php

include '../db/config.php';

if (!isset($_SESSION['id'])) {
    header('location: patLogin.php');
    exit();
}
// Debugging: Check if the database connection is valid
if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

$userId = $_SESSION['id'];
$id = $_SESSION['id'];
// Handle profile picture update
if (isset($_POST['update_profile_pic'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = $_FILES['profile_picture']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['message'] = "Invalid image type. Only JPG and PNG allowed.";
            header("Location: patRecord.php");
            exit();
        }

        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $newFilename = "uploads/profile_" . time() . "_" . uniqid() . "." . $ext;

        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $newFilename)) {
            $_SESSION['message'] = "Failed to upload profile picture.";
            header("Location: patRecord.php");
            exit();
        }

        $updateQuery = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bind_param("si", $newFilename, $userId);
        $updateStmt->execute();

        $_SESSION['message'] = "Profile picture updated successfully.";
        header("Location: patRecord.php");
        exit();
    } else {
        $_SESSION['message'] = "No file uploaded or upload error.";
        header("Location: patRecord.php");
        exit();
    }
}

if (isset($_POST['update_password'])) {
    // Fetch current password hash
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['message'] = "User not found.";
        header("Location: patRecord.php");
        exit();
    }

    $current = trim($_POST['current_password'] ?? '');
    $new = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($current === '' || $new === '' || $confirm === '') {
        $_SESSION['message'] = "All password fields are required to change password.";
        header("Location: patRecord.php");
        exit();
    }

    if (!password_verify($current, $user['password'])) {
        $_SESSION['message'] = "Current password is incorrect.";
        header("Location: patRecord.php");
        exit();
    }

    if ($new !== $confirm) {
        $_SESSION['message'] = "New passwords do not match.";
        header("Location: patRecord.php");
        exit();
    }

    $pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    if (!preg_match($pattern, $new)) {
        $_SESSION['message'] = "Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.";
        header("Location: patRecord.php");
        exit();
    }

    $hashedNewPassword = password_hash($new, PASSWORD_DEFAULT);

    $passQuery = "UPDATE users SET password = ? WHERE id = ?";
    $passStmt = $db->prepare($passQuery);
    $passStmt->bind_param("si", $hashedNewPassword, $userId);

    if ($passStmt->execute()) {
        $_SESSION['message'] = "Password updated successfully.";
    } else {
        $_SESSION['message'] = "Failed to update password. Please try again.";
    }

    header("Location: patRecord.php");
    exit();
}

// Initialize variables with default values
$firstName = $middleName = $lastName = $email = $gender = $birthdate = $mobile = $address = '';
$treatment = $appointment_time = $appointment_date = $dentist_name = ''; // Initialize appointment-related variables

// Query 1: Fetch user details from the `users` table
$query1 = "SELECT first_name, middle_name, emergencyname,emergencycontact,last_name, email, gender, birthdate, mobile, profile_picture, address FROM users WHERE id = ?";
$stmt1 = mysqli_prepare($db, $query1);
if (!$stmt1) {
    die("Failed to prepare statement: " . mysqli_error($db));
}

// Bind parameters for Query 1
if (!mysqli_stmt_bind_param($stmt1, "i", $id)) {
    die("Failed to bind parameters: " . mysqli_stmt_error($stmt1));
}

// Execute Query 1
if (!mysqli_stmt_execute($stmt1)) {
    die("Failed to execute statement: " . mysqli_stmt_error($stmt1));
}

// Get result for Query 1
$result1 = mysqli_stmt_get_result($stmt1);

if ($user = mysqli_fetch_assoc($result1)) {
    $firstName = htmlspecialchars($user['first_name']);
    $middleName = htmlspecialchars($user['middle_name']);
    $lastName = htmlspecialchars($user['last_name']);
    $email = htmlspecialchars($user['email']);
    $gender = htmlspecialchars($user['gender']);
    $birthdate = htmlspecialchars($user['birthdate']);
    $mobile = htmlspecialchars($user['mobile']);
    $address = htmlspecialchars($user['address']);
    $profilepic = htmlspecialchars($user['profile_picture']);
    $emergencyname = htmlspecialchars($user['emergencyname']);
    $emergencycontact = htmlspecialchars($user['emergencycontact']);

} else {
    echo "No user found.";
}

// Close the first statement
mysqli_stmt_close($stmt1);

// Query 2: Fetch appointment details from the `approved_requests` table
$query2 = "SELECT treatment, appointment_time, appointment_date, dentist_name FROM approved_requests WHERE username = ?";
$stmt2 = mysqli_prepare($db, $query2);
if (!$stmt2) {
    die("Failed to prepare statement: " . mysqli_error($db));
}

// Bind parameters for Query 2
if (!mysqli_stmt_bind_param($stmt2, "s", $email)) { // Assuming `username` in `approved_requests` is the same as `email` in `users`
    die("Failed to bind parameters: " . mysqli_stmt_error($stmt2));
}

// Execute Query 2
if (!mysqli_stmt_execute($stmt2)) {
    die("Failed to execute statement: " . mysqli_stmt_error($stmt2));
}

// Get result for Query 2
$result2 = mysqli_stmt_get_result($stmt2);

if ($appointment = mysqli_fetch_assoc($result2)) {
    $treatment = htmlspecialchars($appointment['treatment']);
    $appointment_time = htmlspecialchars($appointment['appointment_time']);
    $appointment_date = htmlspecialchars($appointment['appointment_date']);
    $dentist_name = htmlspecialchars($appointment['dentist_name']);
} else {
    // No appointment found, but variables are already initialized with empty strings
}

// Close the second statement
mysqli_stmt_close($stmt2);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charming Smile Dental Clinic</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="patRecord.css">
    <link rel="stylesheet" href="main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper">
        <?php
        $navactive = "patRecord";
        require_once "../db/nav.php" ?>

        <div class="main-content">
            <div class="patient-details">
                <div class="patient-header">
                    <div class="patient-avatar">
                        <?php if (!empty($profilepic) && $profilepic !== 'pfp_default.jpg'): ?>
                            <img src="<?php echo htmlspecialchars($profilepic); ?>" alt="Profile Picture"
                                style="width: 80px; height: 80px; border-radius: 50%;">
                        <?php else: ?>
                            <img src="pfp_default.jpg" alt="Default Profile Picture"
                                style="width: 80px; height: 80px; border-radius: 50%;">
                        <?php endif; ?>
                    </div>

                    <div class="patient-info">
                        <h3><?php echo "$firstName $middleName $lastName"; ?></h3>
                        <p><?php echo $email; ?></p>
                    </div>
                </div>
                <div class="patient-info-grid">
                    <div><strong>Gender:</strong> <?php echo $gender; ?></div>
                    <div><strong>Birthdate:</strong> <?php echo $birthdate; ?></div>
                    <div><strong>Phone no.:</strong> <?php echo $mobile; ?></div>
                    <div><strong>Address:</strong> <?php echo $address; ?></div>
                    <?php if ($emergencyname != "" || $emergencyname != null): ?>
                        <div><strong>Emergency Contact:</strong> <?php echo $emergencyname; ?></div>

                        <div><strong>Phone Number:</strong> <?php echo $emergencycontact; ?></div>
                    <?php endif; ?>
                </div>

                <!-- <div class="appointment-card">
                    <p><strong>Treatment:</strong> <?php //echo !empty($treatment) ? $treatment : 'No treatment scheduled'; ?></p>
                    <p><strong>Dentist:</strong> <?php //echo !empty($dentist_name) ? $dentist_name : 'No dentist assigned'; ?></p>
                    <p><strong>Date:</strong> <?php //echo !empty($appointment_date) ? $appointment_date : 'No date scheduled'; ?></p>
                    <p><strong>Time:</strong> <?php //echo !empty($appointment_time) ? $appointment_time : 'No time scheduled'; ?></p>
                </div> -->
            </div>
            <!-- Change Profile Picture Form -->
            <form method="POST" enctype="multipart/form-data" class="container mt-4">
                <div class="patient-details">
                    <h4 class="form-label">Change Profile Picture</h4>
                    <input type="file" class="form-control" name="profile_picture" accept="image/*">
                    <div class="text-end mt-2">
                        <button type="submit" name="update_profile_pic" class="btn btn-primary">Save Profile Picture</button>
                    </div>
                </div>
            </form>

            <form method="POST" name="change_password_form" class="container mt-4">
                <div class="patient-details">
                    <h4 class="form-label">Change Password</h4>

                    <div class="mb-3 position-relative">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" />
                    <i class="fa-regular fa-eye toggle-password" data-target="current_password" style="position: absolute; top: 38px; right: 10px; cursor: pointer; color: black;"></i>
                    </div>

                    <div class="mb-3 position-relative">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" />
                    <i class="fa-regular fa-eye toggle-password" data-target="new_password" style="position: absolute; top: 38px; right: 10px; cursor: pointer; color: black;"></i>
                    </div>

                    <div class="mb-3 position-relative">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" />
                    <i class="fa-regular fa-eye toggle-password" data-target="confirm_password" style="position: absolute; top: 38px; right: 10px; cursor: pointer; color: black;"></i>
                    </div>

                    <div class="text-end">
                    <button type="submit" name="update_password" class="btn btn-primary">Save Password</button>
                    </div>
                </div>
            </form>


            <script>
                document.querySelector('form[name="change_password_form"]').addEventListener('submit', function (e) {
                    const current = document.getElementById('current_password');
                    const newPass = document.getElementById('new_password');
                    const confirm = document.getElementById('confirm_password');

                    const anyFilled = current.value || newPass.value || confirm.value;

                    if (anyFilled) {
                        if (!current.value || !newPass.value || !confirm.value) {
                            e.preventDefault();
                            alert("To change your password, all password fields must be filled.");
                            return false;
                        }
                    }
                });
            </script>

            <script>
                const inputFile = document.getElementById('profile_picture');
                const previewImage = document.getElementById('previewImage');

                inputFile.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Invalid image type. Only JPG and PNG allowed.');
                        this.value = ''; // Clear the input
                        previewImage.style.display = 'none';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                    } else {
                    previewImage.style.display = 'none';
                    }
                });

                /*// Optional: AJAX submission (uncomment if you want to submit without page reload)
                
                document.getElementById('profilePicForm').addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    fetch('patRecord.php', {
                    method: 'POST',
                    body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                    alert('Profile picture updated successfully.');
                    // Optionally reload or update UI here
                    })
                    .catch(error => {
                    alert('Error uploading profile picture.');
                    console.error(error);
                    });
                });
                
            </script>
            
            <script>
                    document.querySelectorAll('.toggle-password').forEach(icon => {
                        icon.addEventListener('click', () => {
                        const targetId = icon.getAttribute('data-target');
                        const input = document.getElementById(targetId);

                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            input.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                        });
                    });
                </script>    

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


        setInterval(fetchCurrentTime, 1000);
        fetchCurrentTime();

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