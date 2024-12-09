<?php
// Include the configuration file
require_once 'config.php';

// Start the session
session_start();

// Allow access from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Connect to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the action via GET parameter 'action'
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process data received from the client
$request = json_decode(file_get_contents('php://input'), true);

// Process requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Get mail info
$to = $request["to"];
$subject = $request["subject"];
$message = $request["message"];

switch ($action) {
    case 'contact':
        $headers = "From: mailer@arts-suit.com\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $name = $request["name"];
        $phone = $request["phone"];

        $message = "\r\n名前: " . $name . "\r\nメール: " . $to . "\r\n電話番号: " . $phone . "\r\nメッセージ:\r\n" . $message;
        $to_admin = "k237034@kccollege.ac.jp";

        if (!mail($to, $subject, "お客様のメッサージコピー\r\n\r\n" . $message, $headers)) {
            echo json_encode(['status' => 'error', 'message' => "Can't send email"]);
        } else {
            mail($to_admin, "お客様からのメッセージ: " . $subject, $message, $headers);
            echo json_encode(['status' => 'success', 'message' => "Email successfully sent!"]);
        }
        break;
    default:
        // Send any data you want 

        break;
}
