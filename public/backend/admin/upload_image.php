<?php
// Initialize the script
require_once '../starter.php';

// Start the session
session_start();

// Check the session
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_auth_token'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access."
    ]);
    exit;
}

// Set the directory for image uploads
$uploadDir = __DIR__ . '/../../images/';
// $uploadDir = __DIR__ . '/images/';
$logFile = __DIR__ . '/upload_log.txt';

// Logging function
function logMessage($message)
{
    global $logFile;
    // file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Log the start of the script
logMessage("Script started");

// Check if the upload directory exists and is writable
if (!is_dir($uploadDir)) {
    logMessage("Error: Upload directory does not exist - $uploadDir");
} elseif (!is_writable($uploadDir)) {
    logMessage("Error: Upload directory is not writable - $uploadDir");
}

$response = [
    'status' => 'error',
    'message' => '',
    'uploadedImages' => []
];

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';

// Check that the request contains files
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    logMessage("Files detected in the request");

    // Check for multiple files
    $files = is_array($_FILES['image']['name']) ? $_FILES['image'] : [$_FILES['image']];

    // Array of allowed image formats
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    logMessage("Allowed file formats: " . implode(", ", $allowedExtensions));

    // Process each file
    foreach ($files as $index => $file) {
        $name = is_array($file) ? $file['name'] : $file['name'];
        $tmpName = is_array($file) ? $file['tmp_name'] : $file['tmp_name'];
        $fileType = pathinfo($name, PATHINFO_EXTENSION);

        logMessage("Processing file: $name, temporary name: $tmpName, file type: $fileType");

        // Check for allowed extension
        if (!in_array(strtolower($fileType), $allowedExtensions)) {
            $message = "Invalid file format: $name";
            $response['message'] = $message;
            logMessage($message);
            continue;
        }

        // Unique name for the file
        $uniqueName = uniqid() . '.' . $fileType;
        $targetPath = $uploadDir . $uniqueName;

        // Move the file to the /images directory
        if (move_uploaded_file($tmpName, $targetPath)) {

            chmod($targetPath, 0777);
            $image_path = '/images/' . $uniqueName;

            $sql = "INSERT INTO product_images (product_id, image_path) 
                            VALUES ('$product_id', '" . $conn->real_escape_string($image_path) . "')";
            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Error adding image: ' . $conn->error]);
                exit;
            }
            // Get the ID of the new record
            $newImageId = $conn->insert_id;

            $response['uploadedImages'][] = [
                'id' => $newImageId,
                'image_path' => $image_path,
                'date_of_creation' => date("Y-m-d H:i:s"),
                'date_of_change' => date("Y-m-d H:i:s"),
                'new' => true,
            ];
            logMessage("File successfully uploaded: $targetPath");
        } else {
            $message = "Error uploading file: $name";
            $response['message'] = $message;
            logMessage($message);
        }
    }

    if (!empty($response['uploadedImages'])) {
        $response['status'] = 'success';
        $response['message'] = 'Files uploaded successfully';
        logMessage("All files uploaded successfully");
    } else {
        $response['message'] = 'File upload failed';
        logMessage("File upload failed");
    }
} else {
    $response['message'] = 'No files found in the request';
    logMessage("Error: No files found in the request");
}

// Set the JSON header and output the response
header('Content-Type: application/json');
echo json_encode($response);

// Log the end of the script
logMessage("Script ended");
