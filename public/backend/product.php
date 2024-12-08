<?php
// Include the configuration file
require_once 'config.php';

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

// Process requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Get parameters from GET request
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : false;

$data = [];

if ($product_id == false) {
    $data = 'Data not found (no product_id)';
    echo json_encode(['status' => 'fail', 'data' => $data]);
} else {
    // SQL query to products
    $sql = "SELECT * FROM products
            WHERE id=$product_id";

    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Query execution error: ' . $conn->error]);
        exit;
    }

    // Save results to array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data['product'] = $row;
        }
    }

    // Get product type
    $product_type = isset($data['product']['type']) ? $data['product']['type'] : '';

    // SQL query to sizes
    $sql = "SELECT * FROM sizes
            WHERE product_id=$product_id";

    $result = $conn->query($sql);

    $sizes = [];

    // Remove null fields from array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Apply array_filter to each size row
            $sizes[] = array_filter($row, function ($value) {
                return !is_null($value);
            });
        }
    }

    // Save results to array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sizes[] = $row;
        }
    }
    $data['sizes'] = $sizes;

    // SQL query to product_images
    $sql = "SELECT * FROM product_images
            WHERE product_id=$product_id";
    $result = $conn->query($sql);

    $product_images = [];
    // Save results to array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $product_images[] = $row;
        }
    }
    $data['product_images'] = $product_images;

    // SQL query to options through options_indexes - only if product type is "suit"
    if ($product_type === 'suit') {
        $sql = "SELECT o.* FROM `options` o JOIN options_indexes op 
        ON o.id=op.option_id WHERE op.product_id=$product_id;";
        $result = $conn->query($sql);

        $options = [];
        // Save results to array
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $options[] = $row;
            }
        }
        $data['options'] = $options;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
}

$conn->close();
exit();
