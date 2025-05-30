<?php
require_once('../db/db_users_employee.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="employeeLogin.css">
    <!-- css for user type -->
   
</head>

<body>
    <div class="wrapper">
        <form action="employeelogin.php" method="post">
            <!-- Add a flex container for the logo and text -->
            <div class="logo-text">
                <img src="../img/pfp.jpg" alt="Profile Picture" class="profile-pic">
                <div>
                    <h1>CHARMING SMILE</h1>
                    <p>DENTAL CLINIC</p>
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

            <?php if (isset($_SESSION['success'])) {
                echo '<div class="success-message">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
                }
            ?>

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
            <div class="input-box">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <img src="password.png" alt="Password Icon" class="icon">
                <?php if (!empty($errors['password'])): ?>
                    <div class='error-message'><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <!-- Forgot password and buttons -->
            <div class="extras">
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>
            <button type="submit" id="login" name="login" class="btn login-btn">Login Now</button>
        </form>
    </div>
    <script>
        function signupRedirect() {
            window.location.href = "employeeSignup.php"; // Replace with your signup page
        }
    </script>
</body>

</html>