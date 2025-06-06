<?php
require_once('../db/db_users.php');
require_once '../db/sendmail.php';
$registration_successful = isset($_SESSION['registration_success']) && $_SESSION['registration_success'];
unset($_SESSION['registration_success']); // Clear the flag after using it

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otpresend'])) {
    $otp = generateOTP();
    $email = $_SESSION['otpemail'];
    $expiryotp = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    $sql = "UPDATE users SET otp = ?, expiryotp = ? WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $otp, $expiryotp, $email);
        if ($stmt->execute()) {
            $stmt->close();
            // Success message below
            setModalMessage("Success", "OTP has been resent successfully.", "success");
        } else {
            setModalMessage("Database Error", "Error executing update: " . $stmt->error, "danger");
        }
    } else {
        setModalMessage("Preparation Error", "Failed to prepare OTP resend query: " . $db->error, "danger");
    }

    // Fetch user details for email
    $sql = "SELECT first_name, last_name FROM users WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $stmt->bind_result($firstName, $lastName);
            $stmt->fetch();
            $stmt->close();
            // Send email with expiry info
            $message = "Here is your OTP. Use it to activate your account after signing in: <strong>$otp</strong><br><br>";
            $message .= "This OTP will expire in <strong>5 minutes</strong> (at " . date("g:i A", strtotime($expiryotp)) . ").";

            sendmail($email, "$firstName $lastName", "Confirm your email", $message);
        } else {
            setModalMessage("Database Error", "Error fetching user details: " . $stmt->error, "danger");
        }
    } else {
        setModalMessage("Preparation Error", "Failed to prepare user fetch query: " . $db->error, "danger");
    }

    header('location: patSignup.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otpsubmit'])) {
    $inputOTP = $_POST['otp'];
    $id = $_SESSION['otpemail'];

    // Fetch stored OTP and expiry
    $sql = "SELECT otp, expiryotp FROM users WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($storedOTP, $expiryOTP);
            if ($stmt->fetch()) {
                $stmt->close();
                $currentTime = date("Y-m-d H:i:s");

                if ($currentTime > $expiryOTP) {
                    // OTP expired — generate and send new
                    $newOTP = rand(100000, 999999);
                    $newExpiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

                    $updateOtpSql = "UPDATE users SET otp = ?, expiryotp = ? WHERE email = ?";
                    $updateStmt = $db->prepare($updateOtpSql);
                    if ($updateStmt) {
                        $updateStmt->bind_param("sss", $newOTP, $newExpiry, $id);
                        if ($updateStmt->execute()) {
                            $updateStmt->close();

                            // Get user info again
                            $sql = "SELECT first_name, last_name FROM users WHERE email = ?";
                            $stmt = $db->prepare($sql);
                            if ($stmt) {
                                $stmt->bind_param("s", $id);
                                if ($stmt->execute()) {
                                    $stmt->bind_result($firstName, $lastName);
                                    $stmt->fetch();
                                    $stmt->close();
                                    $message = "Here is your new OTP: <strong>$newOTP</strong><br><br>";
                                    $message .= "This OTP will expire in <strong>5 minutes</strong> (at " . date("g:i A", strtotime($newExpiry)) . ").";
                                    sendmail($id, "$firstName $lastName", "New OTP", $message);

                                    setModalMessage("OTP Expired", "A new OTP has been sent to your email.", "warning");
                                } else {
                                    setModalMessage("Database Error", "Error fetching user details: " . $stmt->error, "danger");
                                }
                            } else {
                                setModalMessage("Preparation Error", "Error preparing fetch user details: " . $db->error, "danger");
                            }
                        } else {
                            setModalMessage("Database Error", "Error updating OTP: " . $updateStmt->error, "danger");
                        }
                    } else {
                        setModalMessage("Preparation Error", "Error preparing OTP update: " . $db->error, "danger");
                    }
                } else {
                    // OTP match check
                    if ($inputOTP == $storedOTP) {
                        $updateSql = "UPDATE users SET status = 'active' WHERE email = ?";
                        $updateStmt = $db->prepare($updateSql);
                        if ($updateStmt) {
                            $updateStmt->bind_param("s", $id);
                            if ($updateStmt->execute()) {
                                $updateStmt->close();
                                setModalMessage("Success", "User verified successfully.", "success");

                                unset($_SESSION['otpmode']);
                                unset($_SESSION['otpemail']);
                                header("location: patLogin.php");
                                exit();
                            } else {
                                setModalMessage("Database Error", "Error updating user status: " . $updateStmt->error, "danger");
                            }
                        } else {
                            setModalMessage("Preparation Error", "Error preparing user update query: " . $db->error, "danger");
                        }
                    } else {
                        setModalMessage("OTP Error", "Invalid OTP entered.", "danger");
                    }
                }
            } else {
                setModalMessage("Verification Error", "User not found or invalid email.", "warning");
            }
        } else {
            setModalMessage("Database Error", "Error executing OTP fetch: " . $stmt->error, "danger");
        }
    } else {
        setModalMessage("Preparation Error", "Error preparing OTP fetch query: " . $db->error, "danger");
    }

    header('location: patSignup.php');
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="patSignup.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            border-radius: 8px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            padding: 20px 30px;
            animation: fadeIn 0.5s ease-out;
        }

        .popup .header {
            background-color: #4CAF50;
            border-radius: 8px 8px 0 0;
            padding: 20px 0;
            color: white;
        }

        .popup .header .icon {
            font-size: 50px;
            margin-bottom: 10px;
        }

        .popup .message {
            margin: 20px 0;
            color: #555;
            font-size: 16px;
        }

        .popup .continue-btn {
            display: flex;
            /* Use flexbox for centering */
            align-items: center;
            /* Center text vertically */
            justify-content: center;
            /* Center text horizontally */
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 30px;
            /* Adjusted padding for better alignment */
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: fit-content;
            /* Prevents button from being unnecessarily wide */
            margin: 0 auto;
            /* Centers the button within its parent container */
        }

        .popup .continue-btn:hover {
            background-color: #45a049;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }
    </style>
</head>

<body>
    <div id="overlay" class="overlay"></div>
    <div id="successPopup" class="popup">
        <div class="header">
            <div class="icon">✔</div>
            <div>Success</div>
        </div>
        <div class="message">Congratulations, your account has been successfully created.</div>
        <button class="continue-btn" onclick="closePopup()">Continue</button>
    </div>
    <div class="wrapper">
        <div class="logo-text">
            <img src="../img/pfp.jpg" alt="Logo">
            <div>
                <h1>CHARMING SMILE</h1>
                <p>DENTAL CLINIC</p>
            </div>
        </div>
        <p class="form-heading">Sign up into your account</p>
        <form action="patSignup.php" method="post">
            <div class="row">
                <div class="input-group col">
                    <label for="first-name">First Name <span class="text-danger">*</span></label>
                    <input type="text" id="first-name" name="first-name" placeholder="Enter your name.."
                        value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>">
                    <?php if (!empty($errors['first-name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['first-name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group col">
                    <label for="middle-name">Middle Name <span class="text-danger"></span></label>
                    <input type="text" id="middle-name" name="middle-name" placeholder="Enter your name.."
                        value="<?php echo isset($middleName) ? htmlspecialchars($middleName) : ''; ?>">
                </div>
                <div class="input-group col">
                    <label for="last-name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" id="last-name" name="last-name" placeholder="Enter your name.."
                        value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>">
                    <?php if (!empty($errors['last-name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['last-name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group col">
                    <label for="address">Address <span class="text-danger">*</span></label>
                    <input type="text" id="address" name="address" placeholder="Enter your address..."
                        style="width: 100%;" value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                    <?php if (!empty($errors['address'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['address']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group col">
                    <label for="birthdate">Birthdate: <span class="text-danger">*</span></label>
                    <input type="date" id="birthdate" name="birthdate"
                        max="<?php echo date('Y-m-d', strtotime('-2 years')); ?>"
                        value="<?php echo isset($birthdate) ? htmlspecialchars($birthdate) : ''; ?>">
                    <?php if (!empty($errors['birthdate'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group col">
                    <label for="gender">Gender <span class="text-danger">*</span></label>
                    <select id="gender" name="gender">
                        <option value="">Select your gender...</option>
                        <option value="Male" <?php echo (isset($gender) && $gender == 'Male') ? 'selected' : ''; ?>>Male
                        </option>
                        <option value="Female" <?php echo (isset($gender) && $gender == 'Female') ? 'selected' : ''; ?>>
                            Female</option>
                    </select>
                    <?php if (!empty($errors['gender'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['gender']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group col">
                    <label for="mobile">Mobile No. <span class="text-danger">*</span></label>
                    <input type="text" id="mobile" name="mobile" placeholder="+63- 912 345 6789"
                        value="<?php echo isset($mobile) ? htmlspecialchars($mobile) : '+63'; ?>">
                    <?php if (!empty($errors['mobile'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['mobile']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group col">
                    <label for="email">Email Address <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address..."
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="input-group col">
                    <label for="emergencyname">Emergency Contact <span class="text-danger">*</span></label>
                    <input type="text" id="emergencyname" name="emergencyname"
                        placeholder="Enter your emergency contact person..."
                        value="<?php echo isset($emergencyname) ? htmlspecialchars($emergencyname) : ''; ?>">
                    <?php if (!empty($errors['emergencyname'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['emergencyname']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="input-group col">
                    <label for="emergencycontact">Emergency Mobile No. <span class="text-danger">*</span></label>
                    <input type="text" id="emergencycontact" name="emergencycontact" placeholder="+63- 912 345 6789"
                        value="<?php echo isset($emergencycontact) ? htmlspecialchars($emergencycontact) : '+63'; ?>">
                    <?php if (!empty($errors['emergencycontact'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['emergencycontact']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="input-group col">
                    <label for="username">Username <span class="text-danger">*</span></label>
                    <input type="text" id="username" name="username" placeholder="Enter your username"
                        value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    <?php if (!empty($errors['username'])): ?>
                        <div class='error-message'><?php echo htmlspecialchars($errors['username']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group col">
                    <label for="password">Password <span class="text-danger">*</span></label>
                    <input type="password" id="password" name="password" placeholder='xxxxxxxxxx'>
                    <?php if (!empty($errors['password'])): ?>
                        <div class='error-message'><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>

                </div>
                <div class='input-group col'>
                    <label for='confirm-password'>Confirm Password <span class="text-danger">*</span></label>
                    <input type='password' id='confirm-password' name='confirm-password' placeholder='xxxxxxxxxx'>
                    <?php if (!empty($errors['confirm-password'])): ?>
                        <div class='error-message'><?php echo htmlspecialchars($errors['confirm-password']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <ul class="password-requirements" style="font-size: 0.9em; margin-top: 5px; padding-left: 20px;">
                <li>Password must be at least 8 characters long.</li>
                <li>Must contain at least one uppercase letter (A-Z).</li>
                <li>Must contain at least one lowercase letter (a-z).</li>
                <li>Must include at least one number (0-9).</li>
                <li>Must include at least one special character.</li>
            </ul>
            <button type="submit" name="signup">Sign Up</button>
        </form>

    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">OTP Verification</h5>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <p>Please enter the OTP sent to your registered number.</p>
                        <input type="text" id="otp" class="form-control" name="otp" placeholder="Enter OTP"
                            maxlength="6">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="otpsubmit">Verify</button>

                        <button type="submit" name="otpresend" class="btn btn-secondary">
                            Resend OTP
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show the modal when the page loads
        document.addEventListener("DOMContentLoaded", function () {
            var otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
            <?php if (isset($_SESSION['otpmode'])) {
                $otpmode = $_SESSION['otpmode'];
            } else {
                $otpmode = false;
            } ?>
            <?= $otpmode ? "otpModal.show();" : "" ?>
        });


    </script>

</body>

</html>