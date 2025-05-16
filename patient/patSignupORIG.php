<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link rel="stylesheet" href="patSignup.css">
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
        // Code for php
        <?php
        if (isset($_POST["signup"])) {
            // Retrieve form data
            $firstName = $_POST["first-name"];
            $middleName = $_POST["middle-name"];
            $lastName = $_POST["last-name"];
            $address = $_POST["address"];
            $birthdate = $_POST["birthdate"];
            $gender = $_POST["gender"];
            $mobile = $_POST["mobile"];
            $email = $_POST["email"];
            $username = $_POST["username"];
            $password = $_POST["password"];
            $passwordRepeat = $_POST["confirm-password"];

            // Initialize an array to hold error messages
            $errors = array();

            // Validate inputs
            if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password) || empty($passwordRepeat)) {
                array_push($errors, "All fields are required");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, "Email is not valid");
            }
            
            if (strlen($password) < 8) {
                array_push($errors, "Password must be at least 8 characters long");
            }
            
            if ($password !== $passwordRepeat) {
                array_push($errors, "Passwords do not match");
            }

            // Display errors or insert data
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            } else {
                // Prepare SQL statement for insertion
               
            }

        }
        ?>
        <form action="patSignup.php" method="post"> 
            <div class="row">
                <div class="input-group">
                    <label for="first-name">First Name :</label>
                    <input type="text" id="first-name" name="first-name" placeholder="Enter your name..">
                </div>
                <div class="input-group">
                    <label for="middle-name">Middle Name :</label>
                    <input type="text" id="middle-name" name="middle-name" placeholder="Enter your name..">
                </div>
                <div class="input-group">
                    <label for="last-name">Last Name :</label>
                    <input type="text" id="last-name" name="last-name" placeholder="Enter your name..">
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" placeholder="Enter your address...">
                </div>
                <div class="input-group">
                    <label for="birthdate">Birthdate:</label>
                    <input type="date" id="birthdate" name="birthdate">
                </div>
                <div class="input-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="">Select your gender...</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="mobile">Mobile No. :</label>
                    <input type="text" id="mobile" name="mobile" placeholder="+63- 912 345 6789">
                </div>
                <div class="input-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address...">
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username">
                </div>
                <div class="input-group">
                    <label for="password">Password :</label>
                    <input type="password" id="password" name="password" placeholder="xxxxxxxxxx">
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password :</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="xxxxxxxxxx">
                </div>
            </div>
            <button type="submit" name="signup">Sign Up</button>     
    </form>
    </div>
</body>
</html>
