<?php
ini_set('display_errors', 0);

if (php_sapi_name() !== 'cli' && !headers_sent()) {
    header('Content-Type: text/plain'); // Ensure it's plain text
}

date_default_timezone_set('Asia/Manila');

echo date('l, F j, Y h:i:s A');
