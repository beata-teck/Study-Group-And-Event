<?php
// backend/api/profile.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, x-user-id");
header("Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS");

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
        $query = "SELECT id, name, email, department, year_of_study, bio, interests, role FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            echo json_encode(["success" => true, "data" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "User not found"]);
        }
    } elseif ($method == 'PUT') {
        $data = json_decode(file_get_contents("php://input"));

        $query = "UPDATE users SET name = :name, department = :department, year_of_study = :year, bio = :bio, interests = :interests WHERE id = :id";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':department', $data->department);
        $stmt->bindParam(':year', $data->year_of_study);
        $stmt->bindParam(':bio', $data->bio);
        $stmt->bindParam(':interests', $data->interests);
        $stmt->bindParam(':id', $user_id);

        if ($stmt->execute()) {
            // Return updated user data
            $stmt = $conn->prepare("SELECT id, name, email, department, year_of_study, bio, interests, role FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $updatedUser = $stmt->fetch();

            echo json_encode(["success" => true, "message" => "Profile updated", "data" => $updatedUser]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update profile"]);
        }
    } elseif ($method == 'DELETE') {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $user_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Account deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete account"]);
        }
    }
} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>