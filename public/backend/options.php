<?php
// Initialize the script
require_once 'starter.php';

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Get parameters from GET request
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'; // Default sort type - by name
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 20; // Number of options per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Search string (default is empty)
$type = isset($_GET['type']) ? "AND type='" . $_GET['type'] . "'" : '';

// Determine sorting based on the $sort parameter
switch ($sort) {
    case 'name_asc':
        $orderBy = 'o.name ASC'; // Sort by name ascending
        break;
    case 'name_desc':
        $orderBy = 'o.name DESC'; // Sort by name descending
        break;
    case 'type_asc':
        $orderBy = 'o.type ASC'; // Sort by type ascending
        break;
    case 'type_desc':
        $orderBy = 'o.type DESC'; // Sort by type descending
        break;
    case 'price_high':
        $orderBy = 'o.price DESC'; // Sort by price descending
        break;
    case 'price_low':
        $orderBy = 'o.price ASC'; // Sort by price ascending
        break;
    default:
        $orderBy = 'o.name ASC'; // Default sort by name ascending
}

// Calculate the offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Prepare the condition for the search string
$searchCondition = '';
if (!empty($query)) {
    // Escape the search value for security
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (o.name LIKE '%$query%' OR o.type LIKE '%$query%')";
}

$splitByType = isset($_GET['splitByType']) ? true : false;

$limit = "LIMIT $offset, $itemsPerPage";
if ($splitByType) {
    $limit = "";
}

// SQL query to get the list of options
$sql = "SELECT o.* 
        FROM options o
        WHERE 1=1 $searchCondition $type
        ORDER BY $orderBy
        $limit";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Query execution error: ' . $conn->error]);
    exit;
}

$options = [];

// Save the results in an array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($splitByType) {
            $options[$row['type']][] = $row;
        } else {
            $options[] = $row;
        }
    }
}

// Get the total number of records to calculate the number of pages
$totalCountResult = $conn->query("SELECT COUNT(*) as count 
                                 FROM options o 
                                 WHERE 1=1 $searchCondition $type");

if (!$totalCountResult) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching the number of options: ' . $conn->error]);
    exit;
}

$totalCountRow = $totalCountResult->fetch_assoc();
$totalItems = intval($totalCountRow['count']);
$totalPages = ceil($totalItems / $itemsPerPage);

// Output the result (response with option information and pagination) in JSON format
echo json_encode([
    'status' => 'success',
    'options' => $options,
    'pagination' => [
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems
    ]
]);

$conn->close();
exit();
