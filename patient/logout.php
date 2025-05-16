<?php
// Initialize the session
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear any remember me cookies if you have them
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time()-3600, '/');
}

// Redirect to login page
header("Location: patLogin.php");
exit();
?>