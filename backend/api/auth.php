<?php
// backend/api/auth.php
include_once '../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    try {
        if ($action == 'register') {
            if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
                // Check if email exists
                $checkQuery = "SELECT id FROM users WHERE email = :email";
                $stmt = $conn->prepare($checkQuery);
                $stmt->bindParam(":email", $data->email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["success" => false, "message" => "Email already exists."]);
                } else {
                    $query = "INSERT INTO users (name, email, password, department) VALUES (:name, :email, :password, :department)";
                    $stmt = $conn->prepare($query);

                    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);

                    $stmt->bindParam(":name", $data->name);
                    $stmt->bindParam(":email", $data->email);
                    $stmt->bindParam(":password", $password_hash);
                    $stmt->bindParam(":department", $data->department);

                    if ($stmt->execute()) {
                        echo json_encode(["success" => true, "message" => "User registered successfully."]);
                    } else {
                        echo json_encode(["success" => false, "message" => "Unable to register user."]);
                    }
                }
            } else {
                echo json_encode(["success" => false, "message" => "Incomplete data."]);
            }
        } elseif ($action == 'login') {
            if (!empty($data->email) && !empty($data->password)) {
                $query = "SELECT id, name, email, password, department, role FROM users WHERE email = :email";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":email", $data->email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch();
                    if (password_verify($data->password, $row['password'])) {
                        unset($row['password']); // Don't send password back
                        echo json_encode(["success" => true, "data" => $row]);
                    } else {
                        echo json_encode(["success" => false, "message" => "Invalid password."]);
                    }
                } else {
                    echo json_encode(["success" => false, "message" => "User not found."]);
                }
            }
        } else {
            echo json_encode(["success" => false, "message" => "Invalid action."]);
        }
    } catch (Throwable $e) {
        echo json_encode(["success" => false, "message" => "Server Error: " . $e->getMessage()]);
    }
}