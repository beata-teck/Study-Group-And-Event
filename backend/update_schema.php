<?php
// backend/update_schema.php
include_once 'config/db.php';

header("Content-Type: application/json");

try {
    // 1. Add columns to users table
    $alterUsersString = "ALTER TABLE users 
                         ADD COLUMN IF NOT EXISTS year_of_study INT, 
                         ADD COLUMN IF NOT EXISTS bio TEXT, 
                         ADD COLUMN IF NOT EXISTS interests TEXT,
                         ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255);";
    // Splitting because PDO might not like multiple ALTERs in one go depending on driver, but usually ok. 
    // Safest to do one by one or catch error if exists.
    // actually "ADD COLUMN IF NOT EXISTS" is MariaDB 10.2+. XAMPP usually has recent MariaDB.
    // If it fails, we catch it.

    $conn->exec($alterUsersString);
    echo "Updated users table.\n";

    // 2. Create Notifications Table
    $createNotif = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($createNotif);
    echo "Created notifications table.\n";

    // 3. Create Comments Table
    $createComments = "CREATE TABLE IF NOT EXISTS event_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($createComments);
    echo "Created event_comments table.\n";

    echo "Schema update completed successfully.";

} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}
?>