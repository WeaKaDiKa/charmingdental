<?php
require_once '../db/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['policy_content'])) {
    $content = $_POST['policy_content'];

    // First, check if record exists
    $checkSql = "SELECT id FROM policy_content WHERE section = 'main_policy'";
    $result = $db->query($checkSql);

    if ($result->num_rows > 0) {
        // Update existing record
        $sql = "UPDATE policy_content SET content = ? WHERE section = 'main_policy'";
    } else {
        // Insert new record
        $sql = "INSERT INTO policy_content (section, content) VALUES ('main_policy', ?)";
    }

    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Policy content updated successfully';
    } else {
        $_SESSION['error'] = 'Failed to update policy content';
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Management</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="adminStyles.css">
    <script src="https://cdn.ckeditor.com/4.21.0/full/ckeditor.js"></script>
    <script src="AdminScript.js" defer></script>
    <style>
        .page-title {
            color: #8B5D5D;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e6b3b3;
        }

        .policy-form {
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .policy-label {
            min-width: 120px;
            color: #000;
            font-weight: 500;
            padding-top: 0.5rem;
        }

        .policy-input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .policy-textarea {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            resize: vertical;
            min-height: 200px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-start;
            margin-left: 135px;
        }

        .btn-save {
            background-color: #da9393;
            color: white;
            padding: 0.5rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .btn-save:hover {
            background-color: #f8b7b1;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .logout-confirm-dialog {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .logout-dialog-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logout-dialog-buttons {
            margin-top: 20px;
        }

        .btn-confirm,
        .btn-cancel {
            padding: 8px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-confirm {
            background-color: #ea5455;
            color: white;
        }

        .btn-confirm:hover {
            background-color: #d64849;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .top-header img {
            width: 55px;
            height: 50px;
            margin-right: 10px;
            border-radius: 20%;
        }
    </style>
</head>

<body>
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper">
        <?php
        $navactive = "recepPolicy";

        require_once "../db/nav.php" ?>


        <div class="main-content overflow-hidden">
            <div class="user-management overflow-hidden">
                <h2 class="page-title">Manage Policies - Add Policy</h2>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message success">
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message error">
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
              
                        <textarea id="policy-textarea" name="policy_content" class="policy-textarea" rows="10"><?php
                        $sql = "SELECT content FROM policy_content WHERE section = 'main_policy'";
                        $result = $db->query($sql);
                        if ($result && $result->num_rows > 0) {
                            echo htmlspecialchars($result->fetch_assoc()['content']);
                        }
                        ?></textarea>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn-save">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="logoutConfirmDialog" class="logout-confirm-dialog">
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

        function showLogoutDialog() {
            document.getElementById('logoutConfirmDialog').style.display = 'block';
        }

        function closeLogoutDialog() {
            document.getElementById('logoutConfirmDialog').style.display = 'none';
        }

        function logout() {
            window.location.href = '../dentist/logout.php';
        }

        window.onclick = function (event) {
            var logoutDialog = document.getElementById('logoutConfirmDialog');
            if (event.target == logoutDialog) {
                closeLogoutDialog();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            CKEDITOR.replace('policy-textarea', {
                width: '1000px',
                height: '400px',
                removeButtons: 'Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,About'
            });
        });
    </script>
</body>

</html>