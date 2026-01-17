<?php
// backend/check_connection.php
// Enable errors for this diagnostic script
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";

try {
    $host = '127.0.0.1';
    $db_name = 'study_group_event';
    $username = 'root';
    $password = '';

    echo "<p>Attempting to connect to <strong>$host</strong> with user <strong>$username</strong>...</p>";

    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color:green'>✅ Connection Successful!</p>";

    // Check tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "<p>✅ Found " . count($tables) . " tables:</p><ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
            // Check rows
            $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo " ($count rows)";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>❌ Database exists but contains NO TABLES.</p>";
        echo "<p>Please import <strong>backend/sql/schema.sql</strong>.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Connection Failed: " . $e->getMessage() . "</p>";

    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<p><strong>Tip:</strong> The database '$db_name' does not exist. Please create it in phpMyAdmin.</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>❌ Fatal Error: " . $e->getMessage() . "</p>";
}
?>