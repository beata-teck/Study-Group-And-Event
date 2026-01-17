<?php
// backend/config/db.php
// Keep reporting off for production-like behavior, but catch logic will handle errors
error_reporting(0);
ini_set('display_errors', 0);

$host = '127.0.0.1'; // Force IPv4
$db_name = 'study_group_event';
$username = 'root';
$password = ''; // Default XAMPP password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Throwable $e) { // Catch ALL errors (PDOException + Fatal Errors)
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Database Connection Error: " . $e->getMessage()
    ]);
    exit;
}
