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

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Get parameters from GET request
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest'; // Default sort type - newest
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Number of products per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Search string (default is empty)
$popular = isset($_GET['popular']) && $_GET['popular'] == '1'; // Parameter to filter recommended products
$productType = isset($_GET['productType']) ? $_GET['productType'] : ''; // Filter by product type (suit or not_suit)

// Determine sorting based on the $sort parameter
switch ($sort) {
    case 'newest':
        $orderBy = 'p.date_of_creation DESC'; // Newest products first
        break;
    case 'highest_price':
        $orderBy = 'min_price DESC'; // Products with the highest minimum price
        break;
    case 'lowest_price':
        $orderBy = 'min_price ASC'; // Products with the lowest minimum price
        break;
    case 'recommended':
        $orderBy = 'popular DESC'; // Recommended products
        break;
    case 'name_asc':
        $orderBy = 'name ASC'; // Sort by name ascending
        break;
    case 'name_desc':
        $orderBy = 'name DESC'; // Sort by name descending
        break;
    case 'name_eng_asc':
        $orderBy = 'name_eng ASC'; // Sort by English name ascending
        break;
    case 'name_eng_desc':
        $orderBy = 'name_eng DESC'; // Sort by English name descending
        break;
    case 'active_asc':
        $orderBy = 'active ASC'; // Sort by active status ascending
        break;
    case 'active_desc':
        $orderBy = 'active DESC'; // Sort by active status descending
        break;
    default:
        $orderBy = 'p.date_of_creation DESC'; // Default sort by creation date (newest)
}

// Calculate the offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Prepare the condition for the search string
$searchCondition = '';
if (!empty($query)) {
    // Escape the search value for security
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (p.name LIKE '%$query%' OR p.name_eng LIKE '%$query%' OR p.description LIKE '%$query%')";
}

// Prepare the condition for the "popular" parameter
$popularCondition = '';
if ($popular) {
    $popularCondition .= "AND p.popular = 1"; // Filter by popularity
}

// Prepare the condition for the product type filter
$productTypeCondition = '';
if (!empty($productType)) {
    // Escape the product type value for security
    $productType = $conn->real_escape_string($productType);
    $productTypeCondition .= "AND p.type = '$productType'";
}

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';

switch ($action) {
    case 'list_all_products':

        $where = "";
        if ($searchCondition != "" || $popularCondition != "" || $productTypeCondition != "") {
            $where = "WHERE 1=1 $searchCondition $popularCondition $productTypeCondition";
        }

        // SQL query with JOIN, MIN, sorting, search condition, popular products, product type filter, LIMIT and OFFSET
        $sql = "SELECT p.*, MIN(i.price) as min_price, COALESCE(MIN(im.image_path), NULL) AS image_path
        FROM products p
        LEFT JOIN sizes i ON p.id = i.product_id
        LEFT JOIN product_images im ON p.id = im.product_id
        $where
        GROUP BY p.id
        ORDER BY $orderBy
        LIMIT $offset, $itemsPerPage";

        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Query execution error: ' . $conn->error]);
            exit;
        }
        $products = [];

        // Save the results in an array
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        // Get the total number of records to calculate the number of pages (considering search and recommendation filters)
        $totalCountResult = $conn->query("SELECT COUNT(DISTINCT p.id) as count 
                          FROM products p 
                          JOIN sizes i ON p.id = i.product_id
                          WHERE 1=1 $searchCondition $popularCondition $productTypeCondition");

        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Error getting the number of products: ' . $conn->error]);
            exit;
        }
        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = intval($totalCountRow['count']);
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
        break;
    case 'list_all_options':
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
            echo json_encode(['status' => 'error', 'message' => 'Error getting the number of options: ' . $conn->error]);
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
        break;
    case 'get_option':
        // Check if the id was passed in the request
        if (!isset($_GET['option_id']) || empty($_GET['option_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Option ID is missing']);
            exit;
        }

        $option_id = $_GET['option_id'];

        // Execute a database query to get the option data
        $sql = "SELECT * FROM options WHERE id = '$option_id'";
        $result = $conn->query($sql);

        // Check if a result was found
        if ($result && $result->num_rows > 0) {
            // Get the option data
            $optionData = $result->fetch_assoc();

            // Execute a query to get related product_id from the options_indexes table
            $productIds = [];
            $sqlProducts = "SELECT product_id FROM options_indexes WHERE option_id = '$option_id'";
            $resultProducts = $conn->query($sqlProducts);

            if ($resultProducts && $resultProducts->num_rows > 0) {
                while ($row = $resultProducts->fetch_assoc()) {
                    $productIds[] = $row['product_id'];
                }
            }

            // Get product information if found
            $productsData = [];
            if (!empty($productIds)) {
                $productIdsString = implode(",", $productIds);
                $sqlProductsData = "SELECT * FROM products WHERE id IN ($productIdsString)";
                $resultProductsData = $conn->query($sqlProductsData);

                if ($resultProductsData && $resultProductsData->num_rows > 0) {
                    while ($product = $resultProductsData->fetch_assoc()) {
                        $productsData[] = $product;
                    }
                }
            }

            // Form the final response
            echo json_encode(['status' => 'success', 'option' => $optionData, 'products' => $productsData]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Option not found']);
        }
        break;
    case 'edit_option':
        // Check for the presence of original and updated data
        if (!isset($request['data_original']) || !isset($request['data'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing original or updated data']);
            exit;
        }

        $originalData = $request['data_original'];
        $updatedData = $request['data'];

        // Get the option ID from the original data
        $optionId = intval($originalData['id']);

        // Check if data has changed and update if necessary
        if ($originalData !== $updatedData) {
            $sql = "UPDATE options SET
                        name = '" . $conn->real_escape_string($updatedData['name']) . "',
                        type = '" . $conn->real_escape_string($updatedData['type']) . "',
                        price = '" . $conn->real_escape_string($updatedData['price']) . "',
                        stock = '" . $conn->real_escape_string($updatedData['stock']) . "',
                        date_of_change = '" . date("Y-m-d H:i:s") . "'
                        WHERE id = '$optionId'";

            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Error updating option: ' . $conn->error]);
                exit;
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Option updated successfully']);
        break;
    case 'add_product':
        // Get data from the JSON request
        $data = $request['data'];

        $product = $data['product'];
        $type =  $product['type'];
        $name = $conn->real_escape_string($product['name']);
        $name_eng = $conn->real_escape_string($product['name_eng']);
        $description = $conn->real_escape_string($product['description']);
        $popular = isset($product['popular']) ?  $product['popular'] : '';

        // Insert the main product
        $sql = "INSERT INTO products (type, name, name_eng, description, popular) 
            VALUES ('$type', '$name', '$name_eng', '$description', '$popular')";
        if ($conn->query($sql) === TRUE) {
            $newProductId = $conn->insert_id;

            // Add sizes if they exist
            if (!empty($data['sizes'])) {
                foreach ($data['sizes'] as $size) {

                    $size_details_h = "";
                    $size_details = "";
                    if (isset($size['height_min'])) {
                        $size_details_h = "height_min, height_max, shoulder_width_min, 
                        shoulder_width_max, waist_size_min, waist_size_max,";
                        $size_details = $conn->real_escape_string($size['height_min']) . "',
                        '" . $conn->real_escape_string($size['height_max']) . "', '" . $conn->real_escape_string($size['shoulder_width_min']) . "',
                        '" . $conn->real_escape_string($size['shoulder_width_max']) . "', '" . $conn->real_escape_string($size['waist_size_min']) . "',
                        '" . $conn->real_escape_string($size['waist_size_max']) . "', '";
                    }

                    $sql = "INSERT INTO sizes (product_id, name, price, $size_details_h stock)
                        VALUES ('$newProductId', '" . $conn->real_escape_string($size['name']) . "',
                        '" . $conn->real_escape_string($size['price']) . "', '" . $size_details  . $conn->real_escape_string($size['stock']) . "')";

                    if ($conn->query($sql) === FALSE) {
                        echo json_encode(['status' => 'error', 'message' => 'Error adding sizes: ' . $conn->error]);
                        exit;
                    }
                }
            }

            // Add options
            if (!empty($data['options']) && isset($data['options'])) {
                foreach ($data['options'] as $option) {
                    $sql = "INSERT INTO options_indexes (product_id, option_id)
                        VALUES ('$newProductId', '" . intval($option['id']) . "')";

                    if ($conn->query($sql) === FALSE) {
                        echo json_encode(['status' => 'error', 'message' => 'Error adding option: ' . $conn->error]);
                        exit;
                    }
                }
            }

            echo json_encode(['status' => 'success', 'id' => $newProductId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }

        break;
    case 'add_options':
        $name = $request['name'];
        $type = $request['type'];
        $price = $request['price'];
        $stock = $request['stock'];

        $sql = "INSERT INTO options (name, type, price, stock)
            VALUES ('$name', '$type', '$price', '$stock')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }

        break;
    case 'delete_options':
        // Get the option ID from the request
        $option_id = isset($_GET['option_id']) ? (int)$_GET['option_id'] : 0;

        // Check that the option ID was passed
        if ($option_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid option ID']);
            exit;
        }

        // Delete option relationships with products in the options_indexes table
        $sqlDeleteIndexes = "DELETE FROM options_indexes WHERE option_id = $option_id";
        if ($conn->query($sqlDeleteIndexes) === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting relationships with products: ' . $conn->error]);
            exit;
        }

        // Delete the option itself from the options table
        $sqlDeleteOption = "DELETE FROM options WHERE id = $option_id";
        if ($conn->query($sqlDeleteOption) === TRUE) {
            echo json_encode(['status' => 'success', 'message' => 'Option deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting option: ' . $conn->error]);
        }
        break;
    case 'edit_product':
        // Get data from the request
        $originalData = $request['data_original'];
        $updatedData = $request['data'];

        $productId = $originalData['product']['id'];

        // Update the main product
        if ($originalData['product'] !== $updatedData['product']) {
            $sql = "UPDATE products SET
                    type = '" . $conn->real_escape_string($updatedData['product']['type']) . "',
                    name = '" . $conn->real_escape_string($updatedData['product']['name']) . "',
                    name_eng = '" . $conn->real_escape_string($updatedData['product']['name_eng']) . "',
                    description = '" . $conn->real_escape_string($updatedData['product']['description']) . "',
                    active = '" . $conn->real_escape_string($updatedData['product']['active']) . "',
                    popular = '" . $conn->real_escape_string($updatedData['product']['popular']) . "',
                    date_of_change = '" . date("Y-m-d H:i:s") . "'
                    WHERE id = '$productId'";
            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Error updating product: ' . $conn->error]);
                exit;
            }
        }

        // Handle sizes
        $originalSizes = array_column($originalData['sizes'], null, 'id'); // Use ID as keys
        $updatedSizes = array_column($updatedData['sizes'], null, 'id');

        // Delete sizes that are not in the updated data
        foreach ($originalSizes as $id => $originalSize) {
            if (!isset($updatedSizes[$id])) {
                $sql = "DELETE FROM sizes WHERE id = '$id'";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error deleting sizes: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Update or add sizes
        foreach ($updatedSizes as $id => $updatedSize) {
            if (isset($originalSizes[$id])) {
                // If the size exists, update it
                if ($originalSizes[$id] != $updatedSize) {

                    $updateFields = [];

                    // Use the function to add fields
                    addFieldToUpdate($updateFields, $updatedSize, 'name');
                    addFieldToUpdate($updateFields, $updatedSize, 'price');
                    addFieldToUpdate($updateFields, $updatedSize, 'height_min');
                    addFieldToUpdate($updateFields, $updatedSize, 'height_max');
                    addFieldToUpdate($updateFields, $updatedSize, 'shoulder_width_min');
                    addFieldToUpdate($updateFields, $updatedSize, 'shoulder_width_max');
                    addFieldToUpdate($updateFields, $updatedSize, 'waist_size_min');
                    addFieldToUpdate($updateFields, $updatedSize, 'waist_size_max');
                    addFieldToUpdate($updateFields, $updatedSize, 'stock');
                    $updatedSize['date_of_change'] = date("Y-m-d H:i:s");
                    addFieldToUpdate($updateFields, $updatedSize, 'date_of_change');

                    if (!empty($updateFields)) {

                        $setClause = implode(', ', $updateFields);
                        $sql = "UPDATE sizes SET $setClause WHERE id = $id";
                        if ($conn->query($sql) === FALSE) {
                            echo json_encode(['status' => 'error', 'message' => 'Error updating sizes: ' . $sql . $conn->error]);
                            exit;
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
                    }
                }
            } else {
                // Helper function to safely handle numeric values
                function prepareIntValue($value)
                {
                    return is_numeric($value) ? $value : 'NULL';
                }

                // If the size is new, add it
                $sql = "INSERT INTO sizes (product_id, name, price, height_min, height_max, shoulder_width_min, shoulder_width_max, waist_size_min, waist_size_max, stock)
        VALUES (
            '$productId', 
            '" . $conn->real_escape_string($updatedSize['name']) . "', 
            '" . $conn->real_escape_string($updatedSize['price']) . "', 
            " . prepareIntValue($updatedSize['height_min']) . ", 
            " . prepareIntValue($updatedSize['height_max']) . ", 
            " . prepareIntValue($updatedSize['shoulder_width_min']) . ", 
            " . prepareIntValue($updatedSize['shoulder_width_max']) . ", 
            " . prepareIntValue($updatedSize['waist_size_min']) . ", 
            " . prepareIntValue($updatedSize['waist_size_max']) . ", 
            " . prepareIntValue($updatedSize['stock']) . "
        )";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding new size: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Handle images (similar to sizes)
        $originalImages = array_column($originalData['product_images'], null, 'id');
        $updatedImages = array_column($updatedData['product_images'], null, 'id');

        // Delete images
        foreach ($originalImages as $id => $originalImage) {
            if (!isset($updatedImages[$id])) {
                $sql = "DELETE FROM product_images WHERE id = '$id'";
                $imagePath = $originalImage['image_path'];

                $fullPath = __DIR__ . '/../..' . $imagePath;

                // Delete the file if it exists
                if (file_exists($fullPath)) {
                    if (!unlink($fullPath)) {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to delete file. File not found.']);
                        exit;
                    }
                }

                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error deleting image: ' . $conn->error]);
                    exit;
                }
            }
        }

        if (isset($originalData['options'])) {
            // Work with options and relationships in the options_indexes table
            $originalOptionIds = array_column($originalData['options'], 'id');
            $updatedOptionIds = array_column($updatedData['options'], 'id');

            // Remove old relationships (options not in updated data)
            $optionsToRemove = array_diff($originalOptionIds, $updatedOptionIds);
            if (!empty($optionsToRemove)) {
                $optionsToRemoveStr = implode(',', array_map('intval', $optionsToRemove));
                $sql = "DELETE FROM options_indexes WHERE product_id = '$productId' AND option_id IN ($optionsToRemoveStr)";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error deleting relationships with options: ' . $conn->error]);
                    exit;
                }
            }

            // Add new relationships (options not in original data) - add to options_indexes table
            $optionsToAdd = array_diff($updatedOptionIds, $originalOptionIds);
            foreach ($optionsToAdd as $optionId) {
                $sql = "INSERT INTO options_indexes (product_id, option_id)
                VALUES ('$productId', '" . intval($optionId) . "')";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding relationships with options: ' . $conn->error]);
                    exit;
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
        break;
    case 'delete_product':
        // Get the product ID from the request
        $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

        // Check that the product ID was passed
        if ($product_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }

        $sqlImagesList = "SELECT * FROM `product_images` WHERE `product_id`=$product_id";
        $resultImagesList = $conn->query($sqlImagesList);
        // Save the results in an array
        if ($resultImagesList->num_rows > 0) {
            while ($row = $resultImagesList->fetch_assoc()) {
                $imagePath = $row['image_path'];
                $fullPath = __DIR__ . '/../..' . $imagePath;

                // Delete the file if it exists
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }

        // Start the transaction
        $conn->begin_transaction();

        try {
            // Delete product images
            $sqlDeleteImages = "DELETE FROM product_images WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteImages) === FALSE) {
                throw new Exception('Error deleting product images: ' . $conn->error);
            }

            // Delete product sizes
            $sqlDeleteSizes = "DELETE FROM sizes WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteSizes) === FALSE) {
                throw new Exception('Error deleting product sizes: ' . $conn->error);
            }

            // Delete product relationships with options in the options_indexes table
            $sqlDeleteOptionIndexes = "DELETE FROM options_indexes WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteOptionIndexes) === FALSE) {
                throw new Exception('Error deleting relationships with options: ' . $conn->error);
            }

            // Delete records from client_order_indexes related to the product
            $sqlDeleteClientOrders = "DELETE FROM client_order_indexes WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteClientOrders) === FALSE) {
                throw new Exception('Error deleting client orders related to the product: ' . $conn->error);
            }

            // Delete the product itself
            $sqlDeleteProduct = "DELETE FROM products WHERE id = $product_id";
            if ($conn->query($sqlDeleteProduct) === FALSE) {
                throw new Exception('Error deleting product: ' . $conn->error);
            }

            // If everything went well, commit the transaction
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Product and all related data deleted successfully']);
        } catch (Exception $e) {
            // In case of an error, roll back the transaction
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        break;
    case 'deactive_product':
        $sql = "UPDATE products
        SET active = 0
        WHERE id = $product_id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    case 'active_product':
        $sql = "UPDATE products
            SET active = 1
            WHERE id = $product_id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();


function addFieldToUpdate(&$updateFields, $request, $fieldName)
{
    if (isset($request[$fieldName])) {
        $fieldValue = $request[$fieldName];
        $updateFields[] = "$fieldName='$fieldValue'";
    }
}

exit();
