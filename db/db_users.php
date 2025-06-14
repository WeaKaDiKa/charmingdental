<?php
$username = "";
$email = "";
$usertype = "";
$errors = array();


function generateotp()
{
    $otp = "";
    for ($i = 0; $i < 6; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}

// Function to validate birthdate
function validateBirthdate($birthdate)
{
    if (empty($birthdate)) {
        return "Birthdate is required";
    }

    $birthdateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (!$birthdateObj) {
        return "Invalid birthdate format";
    }

    $today = new DateTime();
    $birthdateObj->setTime(0, 0, 0);
    $today->setTime(0, 0, 0);

    if ($birthdateObj > $today) {
        return "Birthdate cannot be in the future";
    }

    $age = $today->diff($birthdateObj)->y;
    if ($age < 5) {
        return "You must be at least 5 years old to register";
    }

    return null;
}


require_once '../db/config.php';
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {

    echo '<script>console.log("dfdsi");</script>';
  
}

if (isset($_POST["signup"])) {
    $firstName = $_POST["first-name"];
    $middleName = $_POST["middle-name"];
    $lastName = $_POST["last-name"];
    $address = $_POST["address"];
    $birthdate = $_POST["birthdate"];
    $gender = $_POST["gender"];
    $mobile = $_POST["mobile"];
    $email = $_POST["email"];

    $emergencycontact = $_POST["emergencycontact"];
    $emergencyname = $_POST["emergencyname"];

    $otp = generateotp();

    $username = $_POST["username"];
    $password = $_POST["password"];
    $passwordRepeat = $_POST["confirm-password"];
    $usertype = isset($_POST["usertype"]) ? $_POST["usertype"] : "patient"; // Default to patient if not set

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Validate inputs
    if (empty($firstName)) {
        $errors['first-name'] = "* First name is required.";
    }

    if (empty($lastName)) {
        $errors['last-name'] = "* Last name is required.";
    }

    if (empty($address)) {
        $errors['address'] = "* Address is required.";
    }

    if (empty($mobile)) {
        $errors['mobile'] = "* Mobile number is required.";
    } elseif (!preg_match('/^\+63\d{10}$/', $mobile)) {
        $errors['mobile'] = "* Mobile number must start with +63 followed by 10 digits.";
    }

    $birthdateError = validateBirthdate($birthdate);
    if ($birthdateError) {
        $errors['birthdate'] = "* " . $birthdateError;
    }

    if (empty($gender)) {
        $errors['gender'] = "* Gender selection is required.";
    }

    if (empty($email)) {
        $errors['email'] = "* Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "* Email is not valid.";
    }

    if (empty($username)) {
        $errors['username'] = "* Username is required.";
    }

    if (empty($password)) {
        $errors['password'] = "* Password is required.";
    } else {
        $passwordErrors = [];

        if (strlen($password) < 8) {
            $passwordErrors[] = "at least 8 characters";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $passwordErrors[] = "one uppercase letter";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $passwordErrors[] = "one lowercase letter";
        }
        if (!preg_match('/\d/', $password)) {
            $passwordErrors[] = "one number";
        }
        if (!preg_match('/[\W_]/', $password)) {
            $passwordErrors[] = "one special character";
        }

        if (!empty($passwordErrors)) {
            $errors['password'] = "* Password must contain " . implode(", ", $passwordErrors) . ".";
        } elseif ($password !== $passwordRepeat) {
            $errors['confirm-password'] = "* Passwords do not match.";
        }
    }


    if ($usertype === "employee" && empty($_POST["usertype"])) {
        $errors['usertype'] = "* User selection is required.";
    }

    // Check for existing email
    if (count($errors) == 0) {
        $table = ($usertype === "patient") ? "users" : "users_employee";
        $user_check_query_email = "SELECT * FROM $table WHERE email=? LIMIT 1";
        $stmt_email = mysqli_prepare($db, $user_check_query_email);

        mysqli_stmt_bind_param($stmt_email, "s", $email);
        mysqli_stmt_execute($stmt_email);

        $result_email = mysqli_stmt_get_result($stmt_email);

        if (mysqli_fetch_assoc($result_email)) {
            $errors['email'] = "Email already exists";
        }

        mysqli_stmt_close($stmt_email);

        // Check for existing username
        $user_check_query_username = "SELECT * FROM $table WHERE username=? LIMIT 1";
        $stmt_username = mysqli_prepare($db, $user_check_query_username);

        mysqli_stmt_bind_param($stmt_username, "s", $username);
        mysqli_stmt_execute($stmt_username);

        $result_username = mysqli_stmt_get_result($stmt_username);

        if (mysqli_fetch_assoc($result_username)) {
            $errors['username'] = "Username already exists";
        }

        mysqli_stmt_close($stmt_username);
    }

    // Register user if there are no errors
    if (count($errors) == 0) {
        if ($usertype === "patient") {
            // Set expiryotp to 5 minutes from now
            $expiryotp = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            $insert_query = "INSERT INTO users (
                        first_name, middle_name, last_name, address, birthdate, gender, mobile, email, 
                        username, password, usertype, emergencyname, emergencycontact, otp, expiryotp
                    ) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $redirect = 'patSignup.php';
            $insert_stmt = mysqli_prepare($db, $insert_query);

            mysqli_stmt_bind_param(
                $insert_stmt,
                "sssssssssssssss",
                $firstName,
                $middleName,
                $lastName,
                $address,
                $birthdate,
                $gender,
                $mobile,
                $email,
                $username,
                $passwordHash,
                $usertype,
                $emergencyname,
                $emergencycontact,
                $otp,
                $expiryotp
            );

            require_once '../db/sendmail.php';
            $message = "Here is your OTP. Use it to activate your account after signing in: <strong>" . $otp . "</strong><br><br>";
            $message .= "This OTP will expire in <strong>5 minutes</strong> (at " . date("g:i A", strtotime($expiryotp)) . ").";

            sendmail($email, $firstName . " " . $lastName, "Confirm your email", $message);
        } else {
            $insert_query = "INSERT INTO users_employee (first_name, middle_name, last_name, address, birthdate, gender, mobile, email, username, password, usertype) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $redirect = 'index.php';
            $insert_stmt = mysqli_prepare($db, $insert_query);
            mysqli_stmt_bind_param(
                $insert_stmt,
                "sssssssssss",
                $firstName,
                $middleName,
                $lastName,
                $address,
                $birthdate,
                $gender,
                $mobile,
                $email,
                $username,
                $passwordHash,
                $usertype
            );

        }
        if (mysqli_stmt_execute($insert_stmt)) {

            $_SESSION['registration_success'] = true;
            setModalMessage("Success", "User registered successfully.", "success");
            if ($usertype === "patient") {
                $_SESSION['otpmode'] = true;
                $_SESSION['otpemail'] = $email;
                header("location: $redirect");
                exit();
            } else {
                header("location: $redirect");
                exit();
            }
        } else {
            setModalMessage("Danger", "Error creating user: " . mysqli_error($db), "danger");

        }

        header("location: " . $_SERVER['PHP_SELF']);
        exit();
    }

}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = "Username is required";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }


    if (count($errors) == 0) {
        // Check both users and users_employee tables
        $sql = "SELECT id, first_name, gender, password, usertype, status, last_login FROM users WHERE username = ?
                UNION
                SELECT id, first_name, gender, password, usertype, status, last_login FROM users_employee WHERE username = ?";
        $stmt_login = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt_login, "ss", $username, $username);
        mysqli_stmt_execute($stmt_login);
        $result_login = mysqli_stmt_get_result($stmt_login);

        if ($user = mysqli_fetch_assoc($result_login)) {
            // Check if the account is archived
            if ($user['status'] === 'archived') {
                setModalMessage("Warning", "Your account has been archived. Please contact support.", "warning");
                header('location: patLogin.php');
                exit();
            }

            // Check if last_login is more than one year ago
            $last_login = new DateTime($user['last_login']);
            $current_date = new DateTime();
            $interval = $last_login->diff($current_date);

            if ($interval->y >= 1) {
                // Archive the account
                if ($user['usertype'] === 'admin' || $user['usertype'] === 'dentist' || $user['usertype'] === 'clinic_receptionist') {
                    $archive_sql = "UPDATE users_employee SET status = 'archived' WHERE id = ?";
                } else {
                    $archive_sql = "UPDATE users SET status = 'archived' WHERE id = ?";
                }
                $stmt_archive = mysqli_prepare($db, $archive_sql);
                mysqli_stmt_bind_param($stmt_archive, "i", $user['id']);
                mysqli_stmt_execute($stmt_archive);
                mysqli_stmt_close($stmt_archive);

                setModalMessage("Warning", "Your account has been archived due to inactivity. Please contact support.", "warning");
                header('location: patLogin.php');
                exit();
            }

            if (password_verify($password, $user['password'])) {
                // Update last_login with the current date
                if ($user['usertype'] === 'admin' || $user['usertype'] === 'dentist' || $user['usertype'] === 'clinic_receptionist') {
                    $update_sql = "UPDATE users_employee SET last_login = NOW() WHERE id = ?";
                } else {
                    $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                }
                $stmt_update = mysqli_prepare($db, $update_sql);
                mysqli_stmt_bind_param($stmt_update, "i", $user['id']);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);

                $_SESSION['id'] = htmlspecialchars($user['id']);
                $_SESSION['username'] = $username;
                $_SESSION['first_name'] = htmlspecialchars($user['first_name']);
                $_SESSION['gender'] = htmlspecialchars($user['gender']);
                $_SESSION['usertype'] = htmlspecialchars($user['usertype']);
                setModalMessage("Success", "You are now logged in.", "success");

                switch ($_SESSION['usertype']) {
                    case 'admin':
                        header('location: ../receptionist_admin/recepDashboard.php');
                        break;
                    case 'dentist':
                        header('location: ../dentist/denDashboard.php');
                        break;
                    case 'clinic_receptionist':
                        header('location: ../receptionist_admin/recepDashboard.php');
                        break;
                    case 'patient':
                        $_SESSION['status'] = htmlspecialchars($user['status']);
                        header('location: ../patient/patDashboard.php');
                        break;
                    default:
                        header('location: defaultDashboard.php');
                        break;
                }
                exit();
            } else {
                array_push($errors, "Invalid username or password.");
                $errors['login'] = 'Incorrect username or password.';
            }
        } else {
            array_push($errors, "Invalid username or password.");
            $errors['login'] = 'Incorrect username or password.';
        }

        mysqli_stmt_close($stmt_login);
    }
}


?>