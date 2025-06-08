<?php
$username = "";
$email = "";
$usertype = "";
$errors = array();

require_once '../db/config.php';

if (isset($_POST["signup"])) {
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
    $usertype = $_POST["usertype"];

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Validate inputs
    if (empty($firstName)) {
        $errors['first-name'] = "First name is required.";
    }

    if (empty($lastName)) {
        $errors['last-name'] = "Last name is required.";
    }

    if (empty($address)) {
        $errors['address'] = "Address is required.";
    }

    if (empty($mobile)) {
        $errors['mobile'] = "Mobile number is required.";
    } elseif (!preg_match('/^\+?\d{1,3}?[-.\s]?\(?\d{1,4}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/', $mobile)) {
        $errors['mobile'] = "Invalid mobile number format.";
    }

    if (empty($birthdate)) {
        $errors['birthdate'] = "Birthdate is required.";
    }

    if (empty($gender)) {
        $errors['gender'] = "Gender selection is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email is not valid.";
    }

    if (empty($username)) {
        $errors['username'] = "Username is required.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    if ($password !== $passwordRepeat) {
        $errors['confirm-password'] = "Passwords do not match.";
    }

    if (empty($usertype)) {
        $errors['usertype'] = "User selection is required.";
    }

    // Check for existing email
    if (count($errors) == 0) {
        $user_check_query_email = "SELECT * FROM users_employee WHERE email=? LIMIT 1";
        $stmt_email = mysqli_prepare($db, $user_check_query_email);

        mysqli_stmt_bind_param($stmt_email, "s", $email);
        mysqli_stmt_execute($stmt_email);

        // Get the result
        $result_email = mysqli_stmt_get_result($stmt_email);

        if (mysqli_fetch_assoc($result_email)) {
            $errors['email'] = "Email already exists";
        }

        mysqli_stmt_close($stmt_email);

        // Check for existing username
        $user_check_query_username = "SELECT * FROM users_employee WHERE username=? LIMIT 1";
        $stmt_username = mysqli_prepare($db, $user_check_query_username);

        mysqli_stmt_bind_param($stmt_username, "s", $username);

        mysqli_stmt_execute($stmt_username);

        // Get the result
        $result_username = mysqli_stmt_get_result($stmt_username);

        if (mysqli_fetch_assoc($result_username)) {
            $errors['username'] = "Username already exists";
        }

        mysqli_stmt_close($stmt_username);
    }

    // Register user if there are no errors
    if (count($errors) == 0) {
        $insert_query_employee = "INSERT INTO users_employee (first_name, middle_name, last_name, address, birthdate, gender, mobile, email, username, password, usertype) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt_insert_employee = mysqli_prepare($db, $insert_query_employee)) {
            mysqli_stmt_bind_param(
                $stmt_insert_employee,
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


            if (mysqli_stmt_execute($stmt_insert_employee)) {
                echo "$usertype registered successfully.";
            }

            $_SESSION['success_message'] = "You are now registered";
            header('location: index.php');
            exit();
        } else {
            echo "Error creating user: " . mysqli_error($db);
        }

        mysqli_stmt_close($stmt_insert_employee);
    } else {
        mysqli_error($db);
    }
} else {
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
}

if (isset($_POST['login'])) {
    $username_login = mysqli_real_escape_string($db, $_POST['username']);
    $password_login = mysqli_real_escape_string($db, $_POST['password']);

    if (empty($username_login)) {
        array_push($errors, "Username is required");
    }
    if (empty($password_login)) {
        array_push($errors, "Password is required");
    }

    // If there are no errors, proceed with login
    if (count($errors) == 0) {
        // Single query to check credentials and get user type
        $sql_login = "SELECT id, first_name, gender, password, usertype FROM users_employee WHERE username=?";

        $stmt_login = mysqli_prepare($db, $sql_login);
        mysqli_stmt_bind_param($stmt_login, "s", $username_login);
        mysqli_stmt_execute($stmt_login);

        $result_login = mysqli_stmt_get_result($stmt_login);
        if ($user_login = mysqli_fetch_assoc($result_login)) {
            if (password_verify($password_login, $user_login['password'])) {
                $_SESSION['id'] = htmlspecialchars($user_login['id']);
                $_SESSION['first_name'] = htmlspecialchars($user_login['first_name']);
                $_SESSION['gender'] = htmlspecialchars($user_login['gender']);
                $_SESSION['usertype'] = htmlspecialchars($user_login['usertype']);
                $_SESSION['success'] = "You are now logged in";

                switch ($_SESSION['usertype']) {
                    case 'admin':
                        header('location: recepDashboard.php');
                        break;
                    case 'dentist':
                        header('location: ../dentist/denDashboard.php');
                        break;
                    case 'clinic_receptionist':
                        header('location: recepDashboard.php');
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
