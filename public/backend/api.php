<?php
//ref https://stackoverflow.com/questions/55382147/php-cors-issue-with-axios-post

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 1000');
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
    }
}




$data = json_decode(file_get_contents('php://input'), true);

// Включаем файл конфигурации
require_once 'config.php';
// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);


// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => $conn->connect_error]);
    die("Connection failed: " . $conn->connect_error);
}




// SQL query to select all data from the table 'meibo'
$sql = "SELECT * FROM film LIMIT 100";
$result = $conn->query($sql);

$data = array();

if ($result->num_rows > 0) {
    // Output data of each row into an array
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "0 results";
}



echo json_encode(['status' => 'success', 'message' => $data]);


// Close connection
$conn->close();
