<?php
require_once '../db/config.php';

// Function to get policy content
function getPolicyContent($db)
{
    $sql = "SELECT content FROM policy_content WHERE section = 'main_policy'";
    $result = $db->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['content'];
    }
    return '';
}

// Get the current policy content
$policyContent = getPolicyContent($db);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policies</title>
    <?php require_once "../db/head.php" ?>
    <link rel="stylesheet" href="patPolicy.css">
    <link rel="stylesheet" href="main.css">
</head>

<body>
    <!-- Top Header -->
    <?php require_once "../db/header.php" ?>

    <div class="main-wrapper overflow-hidden">
        <?php
        $navactive = "patPolicy";
        require_once "../db/nav.php" ?>


        <div class="main-content overflow-hidden">
            <div class="user-management">
                <!-- Policies Title Box -->
                <div class="policy-title-box">
                    <h1>Policies</h1>
                </div>

                <!-- Main Content Box -->
                <div class="policy-content-box">
                    <?php
                    echo $policyContent;
                    ?>
                </div>
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

            // Fetch current time every second (1000 milliseconds)
            setInterval(fetchCurrentTime, 1000);

            // Initial call to display time immediately on page load
            fetchCurrentTime();

            document.addEventListener('DOMContentLoaded', function () {
                var dropdownButtons = document.querySelectorAll('.dropdown-btn');

                dropdownButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        // Toggle active class on the button
                        this.classList.toggle('active');

                        // Find the next sibling dropdown container
                        var dropdownContainer = this.nextElementSibling;

                        // Toggle dropdown visibility
                        if (dropdownContainer.style.display === 'block') {
                            dropdownContainer.style.display = 'none';
                        } else {
                            dropdownContainer.style.display = 'block';
                        }
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