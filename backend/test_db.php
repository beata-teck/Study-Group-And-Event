<?php
// backend/test_db.php
// Temporarily enable errors for CLI test
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config/db.php';

if (isset($conn)) {
    echo "Database connection successful!\n";

    // Test query
    try {
        $stmt = $conn->query("SELECT count(*) as count FROM users");
        $row = $stmt->fetch();
        echo "Users count: " . $row['count'] . "\n";
    } catch (PDOException $e) {
        echo "Query failed: " . $e->getMessage() . "\n";
        echo "Did you import the schema.sql?\n";
    }
} else {
    echo "Database connection failed (variable \$conn not set).\n";
}
?>