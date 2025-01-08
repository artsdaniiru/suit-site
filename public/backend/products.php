<?php
// Initialize the script
require_once 'starter.php';

// Process requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Get parameters from GET request
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest'; // Default sort type - newest first
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Number of products per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Search string (default empty)
$popular = isset($_GET['popular']) && $_GET['popular'] == '1'; // Parameter to filter recommended products
$productType = isset($_GET['productType']) ? $_GET['productType'] : ''; // Filter by product type (suit or not_suit)
$withSize = isset($_GET['withSize']) ? $_GET['withSize'] : 'false'; // With sizes

$withSize = boolval($withSize);

$ON = '';
$byIds = isset($_GET['ids']) ? $_GET['ids'] : '';

if ($byIds != '') {
    $ON = 'AND p.id IN (' . $byIds . ')';
}

// Determine sorting based on $sort parameter
switch ($sort) {
    case 'newest':
        $orderBy = 'p.date_of_creation DESC'; // Newest products first
        break;
    case 'highest_price':
        $orderBy = 'min_price DESC'; // Products with highest minimum price
        break;
    case 'lowest_price':
        $orderBy = 'min_price ASC'; // Products with lowest minimum price
        break;
    case 'recommended':
        $orderBy = 'popular DESC'; // Recommended products
        break;
    default:
        $orderBy = 'p.date_of_creation DESC'; // Default sort by date (newest)
}

// Calculate offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Prepare condition for search string
$searchCondition = '';
if (!empty($query)) {
    // Escape search value for security
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (p.name LIKE '%$query%' OR p.name_eng LIKE '%$query%' OR p.description LIKE '%$query%')";
}

// Prepare condition for "popular" parameter
$popularCondition = '';
if ($popular) {
    $popularCondition .= "AND p.popular = 1"; // Filter by popularity
}

// Prepare condition for product type filter
$productTypeCondition = '';
if (!empty($productType)) {
    // Escape product type value for security
    $productType = $conn->real_escape_string($productType);
    $productTypeCondition .= "AND p.type = '$productType'";
}

// SQL query with JOIN, MIN, sorting, search condition, popular products, product type filter, LIMIT and OFFSET
$sql = "SELECT p.*, MIN(i.price) as min_price, COALESCE(MIN(im.image_path), NULL) AS image_path
        FROM products p
        JOIN sizes i ON p.id = i.product_id
        LEFT JOIN product_images im ON p.id = im.product_id
        WHERE p.active=1 $searchCondition $popularCondition $productTypeCondition $ON
        GROUP BY p.id
        ORDER BY $orderBy
        LIMIT $offset, $itemsPerPage";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Query execution error: ' . $conn->error]);
    exit;
}
$products = [];

// Save results to array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        if ($withSize) {
            // SQL query to sizes
            $sql = "SELECT * FROM sizes
            WHERE product_id={$row['id']}";

            $result_s = $conn->query($sql);

            $sizes = [];

            // Remove null fields from array
            if ($result_s->num_rows > 0) {
                while ($row_s = $result_s->fetch_assoc()) {
                    // Apply array_filter to each size row
                    $sizes[] = array_filter($row_s, function ($value) {
                        return !is_null($value);
                    });
                }
            }

            // Save results to array
            if ($result_s->num_rows > 0) {
                while ($row_s = $result_s->fetch_assoc()) {
                    $sizes[] = $row_s;
                }
            }
            $row['sizes'] = $sizes;
        }

        $products[] = $row;
    }
}

// Get total count of records for pagination (considering search and recommendation filters)
$totalCountResult = $conn->query("SELECT COUNT(DISTINCT p.id) as count 
                                  FROM products p 
                                  JOIN sizes i ON p.id = i.product_id
                                  WHERE p.active=1 $searchCondition $popularCondition $productTypeCondition $ON");

if (!$totalCountResult) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching product count: ' . $conn->error]);
    exit;
}
$totalCountRow = $totalCountResult->fetch_assoc();
$totalItems = $totalCountRow['count'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Output the result (response with product information and pagination) in JSON format
echo json_encode([
    'status' => 'success',
    'products' => $products,
    'pagination' => [
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems
    ]
]);

$conn->close();
exit();
