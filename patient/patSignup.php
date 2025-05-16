<?php
require_once('../db/db_users.php');

$registration_successful = isset($_SESSION['registration_success']) && $_SESSION['registration_success'];
unset($_SESSION['registration_success']); // Clear the flag after using it


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otpresend'])) {
    $otp = generateOTP();
    $email = $_SESSION['otpemail'];

    // Update OTP in the database
    $sql = "UPDATE users SET otp = ? WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $otp, $email);
        if ($stmt->execute()) {
            $_SESSION['success'] = "OTP resend successful";
        }
        $stmt->close();
    }
    // Fetch user details for email
    $sql = "SELECT first_name, last_name FROM users WHERE email = ?";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($firstName, $lastName);
        $stmt->fetch();
        $stmt->close();
    }


    // Send email
    require_once '../db/sendmail.php';
    $message = "Here is your OTP. Use it to activate your account after signing in. <strong>" . $otp . "</strong>";
    sendmail($email, $firstName . " " . $lastName, "Confirm your email", $message);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otpsubmit'])) {
    $inputOTP = $_POST['otp'];
    $id = $_SESSION['otpemail'];

    // Database query to fetch the stored OTP
    $sql = "SELECT otp FROM users WHERE email = ?";

    // Prepare and bind
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $id);

        // Execute the query
        if ($stmt->execute()) {
            // Bind the result
            $stmt->bind_result($storedOTP);
            if ($stmt->fetch()) {
                // Check if the input OTP matches the stored OTP
                if ($inputOTP == $storedOTP) {
                    // Close the statement before running another query
                    $stmt->close();

                    // OTP is valid, update user status to active
                    $updateSql = "UPDATE users SET status = 'active' WHERE email = ?";
                    $updateStmt = $db->prepare($updateSql);
                    if ($updateStmt) {
                        $updateStmt->bind_param("s", $id);
                        if ($updateStmt->execute()) {
                            $_SESSION['success'] = "OTP confirmed";
                            $_SESSION['status'] = "active";
                            unset($_SESSION['otpmode']);
                            unset($_SESSION['otpemail']);
                            header("location: patLogin.php");
                            exit();
                        } else {
                            echo "Error updating user status: " . $updateStmt->error;
                        }
                        $updateStmt->close();
                    } else {
                        echo "<script>alert('Error preparing the update statement: " . $db->error . "');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid OTP.');</script>";
                    // Close the statement as the fetch is complete
                    $stmt->close();
                }
            } else {
                echo "<script>alert('User not found.');</script>";
                // Close the statement as the fetch is complete
                $stmt->close();
            }
        } else {
            echo "Error executing query: " . $stmt->error;
            // Close the statement as the fetch is complete
            $stmt->close();
        }
    } else {
        echo "Error preparing the statement: " . $db->error;
    }
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
            <img src="pfp.jpg" alt="Logo">
            <div>
                <h1>CHARMING SMILE</h1>
                <p>DENTAL CLINIC</p>
            </div>
        </div>
        <p1 class="form-heading">Sign up into your account</p1>
        <form action="patSignup.php" method="post">
            <div class="row">
                <div class="input-group">
                    <label for="first-name">First Name :</label>
                    <input type="text" id="first-name" name="first-name" placeholder="Enter your name.."
                        value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>">
                    <?php if (!empty($errors['first-name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['first-name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="middle-name">Middle Name :</label>
                    <input type="text" id="middle-name" name="middle-name" placeholder="Enter your name.."
                        value="<?php echo isset($middleName) ? htmlspecialchars($middleName) : ''; ?>">
                </div>
                <div class="input-group">
                    <label for="last-name">Last Name :</label>
                    <input type="text" id="last-name" name="last-name" placeholder="Enter your name.."
                        value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>">
                    <?php if (!empty($errors['last-name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['last-name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" placeholder="Enter your address..."
                        value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                    <?php if (!empty($errors['address'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['address']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="birthdate">Birthdate:</label>
                    <input type="date" id="birthdate" name="birthdate"
                        value="<?php echo isset($birthdate) ? htmlspecialchars($birthdate) : ''; ?>">
                    <?php if (!empty($errors['birthdate'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="gender">Gender:</label>
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
                <div class="input-group">
                    <label for="mobile">Mobile No. :</label>
                    <input type="text" id="mobile" name="mobile" placeholder="+63- 912 345 6789"
                        value="<?php echo isset($mobile) ? htmlspecialchars($mobile) : ''; ?>">
                    <?php if (!empty($errors['mobile'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['mobile']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address..."
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">

                <div class="input-group">
                    <label for="emergencyname">Emergency Contact:</label>
                    <input type="text" id="emergencyname" name="emergencyname"
                        placeholder="Enter your emergency contact person..."
                        value="<?php echo isset($emergencyname) ? htmlspecialchars($emergencyname) : ''; ?>">
                    <?php if (!empty($errors['emergencyname'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['emergencyname']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="input-group">
                    <label for="emergencycontact">Emergency Mobile No. :</label>
                    <input type="text" id="emergencycontact" name="emergencycontact" placeholder="+63- 912 345 6789"
                        value="<?php echo isset($emergencycontact) ? htmlspecialchars($emergencycontact) : ''; ?>">
                    <?php if (!empty($errors['emergencycontact'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['emergencycontact']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="input-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username"
                        value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    <?php if (!empty($errors['username'])): ?>
                        <div class='error-message'><?php echo htmlspecialchars($errors['username']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="password">Password :</label>
                    <input type="password" id="password" name="password" placeholder='xxxxxxxxxx'>
                    <?php if (!empty($errors['password'])): ?>
                        <div class='error-message'><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>
                </div>
                <div class='input-group'>
                    <label for='confirm-password'>Confirm Password :</label>
                    <input type='password' id='confirm-password' name='confirm-password' placeholder='xxxxxxxxxx'>
                    <?php if (!empty($errors['confirm-password'])): ?>
                        <div class='error-message'><?php echo htmlspecialchars($errors['confirm-password']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" name="signup">Sign Up</button>
        </form>

    </div>
    <script>
        function closePopup() {
            const popup = document.getElementById('successPopup');
            const overlay = document.getElementById('overlay');
            popup.style.display = 'none';
            overlay.style.display = 'none';
            window.location.href = 'patLogin.php';
        }

        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($registration_successful): ?>
                const popup = document.getElementById('successPopup');
                const overlay = document.getElementById('overlay');
                if (popup && overlay) {
                    popup.style.display = 'block';
                    overlay.style.display = 'block';
                }
            <?php endif; ?>
        });
    </script>
    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
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