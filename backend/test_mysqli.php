<?php
// backend/test_mysqli.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'study_group_event');

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected successfully via MySQLi";
}
?>