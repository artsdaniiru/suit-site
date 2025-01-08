<?php
// Initialize the script
require_once 'starter.php';

// Start the session
session_start();

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
