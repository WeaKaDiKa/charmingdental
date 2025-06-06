<?php
require_once('../db/db_users.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="patLogin.css">


</head>

<body>
    <div class="wrapper">
        <form action="patLogin.php" method="post">
            <!-- Add a flex container for the logo and text -->
            <div class="logo-text">
                <img src="../img/pfp.jpg" alt="Logo" class="profile-pic">
                <div>
                    <h1>CHARMING SMILE</h1>
                    <p> DENTAL CLINIC </p>
                </div>
            </div>
            <p1>Login into your account</p1>

            <!-- alert box -->
            <?php if (!empty($errors['login'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($errors['login']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>



            <!-- Username field -->
            <p>Username</p>
            <div class="input-box">
                <input type="text" name="username" id="username" placeholder="Enter your username" required>
                <img src="user.png" alt="User Icon" class="icon">
                <?php if (!empty($errors['username'])): ?>
                    <div class='error-message'><?php echo htmlspecialchars($errors['username']); ?></div>
                <?php endif; ?>
            </div>
            <!-- Password field -->
            <p>Password</p>
            <div class="input-box" style="position: relative;">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <?php if (!empty($errors['password'])): ?>
                    <div class='error-message'><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?>
                <!-- Toggle icon button -->
                <img src="password.png" alt="Show Password" id="togglePassword" class="icon"
                    style="cursor: pointer; position: absolute; right: px; top: 50%; transform: translateY(-50%);" />
            </div>


            <!-- Forgot password and buttons -->
            <div class="extras">
                <a href="#" class="forgot-password" data-bs-toggle="modal" data-bs-target="#emailModal">Forgot
                    password?</a>
            </div>
            <button type="submit" id="login" name="login" class="btn login-btn">Login</button>
            <div class="divider">
                <span>OR</span>
            </div>
            <button type="button" class="btn signup-btn" onclick="signupRedirect()">Sign Up</button>
        </form>
    </div>
    <?php
    if (!empty($_SESSION['modal'])):
        $modal = $_SESSION['modal'];
        $modalType = $modal['type'] ?? 'info';
        $modalTitle = $modal['title'] ?? 'Notice';
        $modalMessage = $modal['message'] ?? '';
        //  unset($_SESSION['modal']);
        ?>
        <!-- Modal HTML -->
        <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-<?php echo $modalType; ?>">
                    <div class="modal-header bg-<?php echo $modalType; ?> text-white">
                        <h5 class="modal-title" id="alertModalLabel"><?php echo $modalTitle; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo $modalMessage; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-<?php echo $modalType; ?>" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auto show script -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
                alertModal.show();
            });
        </script>
    <?php endif; ?>
    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Enter your email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn login-btn">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">Enter OTP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="otpForm">
                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP</label>
                            <input type="text" class="form-control" id="otp" name="otp" required>
                        </div>
                        <button type="submit" class="btn login-btn">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- New Password Modal -->
    <div class="modal fade" id="newPasswordModal" tabindex="-1" aria-labelledby="newPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPasswordModalLabel">Enter New Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newPasswordForm">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Handle email form submission
            $('#emailForm').on('submit', function (e) {
                e.preventDefault();
                var email = $('#email').val();
                $.ajax({
                    url: 'process_email.php',
                    type: 'POST',
                    data: { email: email },
                    success: function (response) {
                        // Close email modal
                        $('#emailModal').modal('hide');
                        // Open OTP modal
                        $('#otpModal').modal('show');
                    },
                    error: function (xhr, status, error) {
                        alert('Error: ' + error);
                    }
                });
            });

            // Handle OTP form submission
            $('#otpForm').on('submit', function (e) {
                e.preventDefault();
                var otp = $('#otp').val();
                $.ajax({
                    url: 'process_otp.php',
                    type: 'POST',
                    data: { otp: otp },
                    success: function (response) {
                        if (response.includes("OTP confirmed.")) {
                            // Close OTP modal
                            $('#otpModal').modal('hide');
                            // Open New Password modal
                            $('#newPasswordModal').modal('show');
                        } else {
                            alert(response);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert('Error: ' + error);
                    }
                });
            });

            // Handle new password form submission
            $('#newPasswordForm').on('submit', function (e) {
                e.preventDefault();
                var newPassword = $('#newPassword').val();
                $.ajax({
                    url: 'update_password.php',
                    type: 'POST',
                    data: { password: newPassword },
                    success: function (response) {
                        alert(response);
                        $('#newPasswordModal').modal('hide');
                    },
                    error: function (xhr, status, error) {
                        alert('Error: ' + error);
                    }
                });
            });
        });
    </script>

    <script>

        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle the icon image and alt text
            if (type === 'text') {
                togglePassword.src = 'hide-password.png'; // icon for hiding password
                togglePassword.alt = 'Hide Password';
            } else {
                togglePassword.src = 'password.png'; // icon for showing password
                togglePassword.alt = 'Show Password';
            }
        });

        function signupRedirect() {
            window.location.href = "patSignup.php"; // Replace with your signup page
        }
    </script>
</body>

</html>