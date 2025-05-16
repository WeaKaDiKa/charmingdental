<?php
require_once('../db/db_users_employee.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link rel="stylesheet" href="empSignup.css">
</head>
<body>
    <div class="wrapper">
        <div class="logo-text">
            <img src="pfp.jpg" alt="Logo">
            <div>
                <h1>CHARMING SMILE</h1>
                <p>DENTAL CLINIC</p>
            </div>
        </div>
        

        <p1 class="form-heading">Sign up into your account</p1>

        <form action="employeeSignup.php" method="post"> 
            <div class="row">
                <div class="input-group">
                    <label for="first-name">First Name :</label>
                    <input type="text" id="first-name" name="first-name" placeholder="Enter your name.." value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>">
                    <?php if (!empty($errors['first-name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['first-name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="middle-name">Middle Name :</label>
                    <input type="text" id="middle-name" name="middle-name" placeholder="Enter your name.." value="<?php echo isset($middleName) ? htmlspecialchars($middleName) : ''; ?>">
                </div>
                <div class="input-group">
                    <label for="last-name">Last Name :</label>
                    <input type="text" id="last-name" name="last-name" placeholder="Enter your name.." value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>">
                    <?php if (!empty($errors['last-name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['last-name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" placeholder="Enter your address..." value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                    <?php if (!empty($errors['address'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['address']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="birthdate">Birthdate:</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?php echo isset($birthdate) ? htmlspecialchars($birthdate) : ''; ?>">
                    <?php if (!empty($errors['birthdate'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="">Select your gender...</option>
                        <option value="Male" <?php echo (isset($gender) && $gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($gender) && $gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                    <?php if (!empty($errors['gender'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['gender']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="mobile">Mobile No. :</label>
                    <input type="text" id="mobile" name="mobile" placeholder="+63- 912 345 6789" value="<?php echo isset($mobile) ? htmlspecialchars($mobile) : ''; ?>">
                    <?php if (!empty($errors['mobile'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['mobile']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address..." value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
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
                <div class='input-group'>
                    <label for="usertype">User Type:</label>
                    <select name="usertype" id="usertype" required>
                        <option value="">Select User Type</option>
                        <option value="admin">Admin</option>
                        <option value="dentist">Dentist</option>
                        <option value="clinic_receptionist">Clinic Receptionist</option>
                    </select>
                    <?php if (!empty($errors['usertype'])): ?>
                        <div class='error-message'><?php echo htmlspecialchars($errors['usertype']); ?></div>
                    <?php endif; ?>
                    </div>
                </div>
                <button type='submit' name='signup'>Sign Up</button>    
            </div>  
        </form>

    </>
</body>
</html>
