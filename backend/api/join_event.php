<?php
// backend/api/join_event.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    // Check if input is valid
    if (!isset($data->event_id) || !isset($data->user_id) || !isset($data->action)) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }

    try {
        if ($data->action == 'join') {
            $query = "INSERT INTO event_attendees (event_id, user_id) VALUES (:eid, :uid)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':eid', $data->event_id);
            $stmt->bindParam(':uid', $data->user_id);
            $stmt->execute();
        } else {
            $query = "DELETE FROM event_attendees WHERE event_id = :eid AND user_id = :uid";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':eid', $data->event_id);
            $stmt->bindParam(':uid', $data->user_id);
            $stmt->execute();
        }

        // Get updated count
        $countQuery = "SELECT COUNT(*) as count FROM event_attendees WHERE event_id = :eid";
        $stmtCount = $conn->prepare($countQuery);
        $stmtCount->bindParam(':eid', $data->event_id);
        $stmtCount->execute();
        $count = $stmtCount->fetch()['count'];

        echo json_encode([
            "success" => true,
            "message" => $data->action == 'join' ? "Joined event" : "Left event",
            "participant_count" => $count
        ]);
    } catch (Throwable $e) {
        // You might want to return the current count even on error if needed, 
        // but typically error means no change.
        echo json_encode(["success" => false, "message" => "Already joined or error: " . $e->getMessage()]);
    }
}
?>