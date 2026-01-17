<?php
// backend/api/events.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : 'approved';
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Base query with participant count
    $query = "SELECT e.*, u.name as creator_name, u.department as creator_department,
              (SELECT COUNT(*) FROM event_attendees WHERE event_id = e.id) as participant_count
              FROM events e 
              JOIN users u ON e.created_by = u.id 
              WHERE 1=1";

    $params = [];

    if ($id) {
        $query .= " AND e.id = :id";
        $params[':id'] = $id;
    } else {
        if ($status != 'all') {
            $query .= " AND e.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($category)) {
            $query .= " AND e.category = :category";
            $params[':category'] = $category;
        }

        if (!empty($search)) {
            $query .= " AND (e.title LIKE :search OR e.description LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $query .= " ORDER BY e.event_date ASC";
    }

    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        if ($id) {
            $event = $stmt->fetch();
            echo json_encode(["success" => true, "data" => $event ?: null]);
        } else {
            $events = $stmt->fetchAll();
            echo json_encode(["success" => true, "data" => $events]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }

} elseif ($method == 'POST') {
    // Handle file upload
    $uploadDir = '../uploads/';
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $fileName;
        }
    }

    // Get form data
    $title = $_POST['title'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];

    // Get User ID from header (Basic security for demo)
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    $user_id = isset($headers['x-user-id']) ? $headers['x-user-id'] : null;

    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }

    $query = "INSERT INTO events (title, category, description, event_date, event_time, location, image_path, created_by) 
              VALUES (:title, :category, :description, :event_date, :event_time, :location, :imagePath, :user_id)";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':event_time', $event_time);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':imagePath', $imagePath);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Event created successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to create event"]);
        }
    } catch (Throwable $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
?>