<?php
require_once 'dbinfo.php';

// Check if a session is already active
if (session_status() == PHP_SESSION_NONE) {
    // Start the session if none is active
    session_start();
}

// Connect to the database
$db = mysqli_connect($db_hostname, $db_username, $db_password, $db_database, 3318);

if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
