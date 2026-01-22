<?php
// backend/api/notifications.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, x-user-id");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get User ID from header
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$user_id = isset($headers['x-user-id']) ? $headers['x-user-id'] : null;

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method == 'GET') {

        $upcomingQuery = "
            SELECT e.id, e.title, e.event_date, e.event_time 
            FROM events e
            JOIN event_attendees ea ON e.id = ea.event_id
            WHERE ea.user_id = :uid
            AND CONCAT(e.event_date, ' ', IFNULL(e.event_time, '00:00:00')) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
        ";

        $stmt = $conn->prepare($upcomingQuery);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        $upcomingEvents = $stmt->fetchAll();

        foreach ($upcomingEvents as $evt) {
            $message = "Reminder: '{$evt['title']}' is coming up on {$evt['event_date']} at " . ($evt['event_time'] ?: 'TBD');

            // Check if notification already exists
            $check = $conn->prepare("SELECT id FROM notifications WHERE user_id = :uid AND message = :msg");
            $check->bindParam(':uid', $user_id);
            $check->bindParam(':msg', $message);
            $check->execute();

            if ($check->rowCount() == 0) {
                // Insert notification
                $ins = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (:uid, :msg)");
                $ins->bindParam(':uid', $user_id);
                $ins->bindParam(':msg', $message);
                $ins->execute();
            }
        }

        // 2. Fetch all unread notifications
        $query = "SELECT * FROM notifications WHERE user_id = :uid AND is_read = 0 ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        $notifications = $stmt->fetchAll();

        echo json_encode(["success" => true, "data" => $notifications]);

    } elseif ($method == 'POST') {
        // Mark as read
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $query = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $data->id);
            $stmt->bindParam(':uid', $user_id);
            $stmt->execute();
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Missing ID"]);
        }
    }
} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>