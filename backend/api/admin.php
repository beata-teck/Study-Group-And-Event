<?php
// backend/api/admin.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->event_id) || empty($data->action)) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit;
    }

    $status = '';
    if ($data->action == 'approve')
        $status = 'approved';
    elseif ($data->action == 'reject')
        $status = 'rejected';

    try {
        if ($data->action == 'delete') {
            $query = "DELETE FROM events WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $data->event_id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Event deleted"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to delete"]);
            }
        } else {
            $query = "UPDATE events SET status = :status WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $data->event_id);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Status updated"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to update"]);
            }
        }
    } catch (Throwable $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
?>