<?php
// Initialize the script
require_once 'starter.php';

// Process requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // SQL query to select all users
        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);
        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        // Return the list of users in JSON format
        echo json_encode(['status' => 'success', 'users' => $users]);
        break;

    case 'POST':
        if ($action === 'delete') {
            // Get the user ID from the request
            $id = $request['id'];
            // SQL query to delete the user
            $sql = "DELETE FROM users WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            // Get the name and email from the request
            $name = $request['name'];
            $email = $request['email'];
            // SQL query to insert a new user
            $sql = "INSERT INTO users (name, email) VALUES ('$name', '$email')";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        }
        break;

    case 'PUT':
        // Get the user ID, name, and email from the request
        $id = $request['id'];
        $name = $request['name'];
        $email = $request['email'];
        // SQL query to update the user
        $sql = "UPDATE users SET name='$name', email='$email' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    default:
        // Return an error for invalid request methods
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        break;
}

// Test branch
$conn->close();
