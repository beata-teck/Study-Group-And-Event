<?php
// backend/api/comments.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, x-user-id");

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method == 'GET') {
        $event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;
        if (!$event_id) {
            echo json_encode(["success" => false, "message" => "Missing Event ID"]);
            exit;
        }

        $query = "SELECT c.id, c.comment, c.created_at, u.name as user_name, u.role
                  FROM event_comments c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.event_id = :eid
                  ORDER BY c.created_at DESC";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':eid', $event_id);
        $stmt->execute();
        $comments = $stmt->fetchAll();

        echo json_encode(["success" => true, "data" => $comments]);

    } elseif ($method == 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $user_id = isset($headers['x-user-id']) ? $headers['x-user-id'] : null;

        if (!$user_id || empty($data->event_id) || empty($data->comment)) {
            echo json_encode(["success" => false, "message" => "Invalid input or Unauthorized"]);
            exit;
        }

        $query = "INSERT INTO event_comments (event_id, user_id, comment) VALUES (:eid, :uid, :comment)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':eid', $data->event_id);
        $stmt->bindParam(':uid', $user_id);
        $stmt->bindParam(':comment', $data->comment);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Comment posted"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to post comment"]);
        }
    }
} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>