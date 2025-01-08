<?php
// Initialize the script
require_once '../starter.php';

// Start the session
session_start();

// Check session (if required)
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
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id'; // Default sort type - by id
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Number of clients per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Search string (default empty)
$status = isset($_GET['status']) ? $_GET['status'] : ''; // Order status filter

// Determine sorting based on $sort parameter
switch ($sort) {
    case 'id_desc':
        $orderBy = 'co.id DESC'; // Sort by id descending
        break;
    case 'id_asc':
        $orderBy = 'co.id ASC'; // Sort by id ascending
        break;
    case 'name_desc':
        $orderBy = 'ca.name DESC'; // Sort by client name descending
        break;
    case 'name_asc':
        $orderBy = 'ca.name ASC'; // Sort by client name ascending
        break;
    case 'status_desc':
        $orderBy = 'co.status DESC'; // Sort by status descending
        break;
    case 'status_asc':
        $orderBy = 'co.status ASC'; // Sort by status ascending
        break;
    case 'email_desc':
        $orderBy = 'cl.email DESC'; // Sort by email descending
        break;
    case 'email_asc':
        $orderBy = 'cl.email ASC'; // Sort by email ascending
        break;
    case 'phone_desc':
        $orderBy = 'ca.phone DESC'; // Sort by phone descending
        break;
    case 'phone_asc':
        $orderBy = 'ca.phone ASC'; // Sort by phone ascending
        break;
    case 'date_desc':
        $orderBy = 'co.date_of_creation DESC'; // Newest orders first
        break;
    case 'date_asc':
        $orderBy = 'co.date_of_creation ASC'; // Oldest orders first
        break;
    default:
        $orderBy = 'co.id DESC'; // Default sort by id descending
}

// Calculate offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Prepare condition for search string
$searchCondition = '';
if (!empty($query)) {
    // Escape search value for security
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (co.id LIKE '%$query%' OR ca.name LIKE '%$query%' OR co.status LIKE '%$query%' OR cl.email LIKE '%$query%')";
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

switch ($action) {

        // 1. List all orders
    case 'list_all_orders':
        // Filtering conditions (e.g., for searching or sorting orders)
        $searchCondition = isset($searchCondition) ? $searchCondition : "";

        $filter = "";
        if ($status != "") {
            $filter .= " AND co.status = '$status'";
        }

        // Sorting, pagination, and limit parameters
        $orderBy = isset($orderBy) ? $orderBy : "co.id DESC";
        $offset = isset($offset) ? $offset : 0;
        $itemsPerPage = isset($itemsPerPage) ? $itemsPerPage : 10;
        $page = isset($page) ? $page : 1;

        // Query considering conditions, sorting, and pagination
        $sql = "SELECT co.*, cl.name AS client_name, cl.email, cl.height, cl.shoulder_width, cl.waist_size,
                    ca.name AS address_name, ca.address, ca.phone,
                    cpm.card_number
                    FROM client_orders co
                    JOIN clients cl ON co.client_id = cl.id
                    JOIN client_addresses ca ON co.address_id = ca.id
                    JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                    WHERE 1=1 $searchCondition $filter
                    ORDER BY $orderBy
                    LIMIT $offset, $itemsPerPage";
        // print_r($sql);
        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Query execution error: ' . $conn->error]);
            exit;
        }

        $orders = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }

        // Get total count for pagination
        $totalCountResult = $conn->query("SELECT COUNT(*) as count FROM client_orders co
                    JOIN clients cl ON co.client_id = cl.id
                    JOIN client_addresses ca ON co.address_id = ca.id
                    JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                    WHERE 1=1 $searchCondition $filter $searchCondition $filter");
        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Error fetching order count: ' . $conn->error]);
            exit;
        }
        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = intval($totalCountRow['count']);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Output result with order information and pagination
        echo json_encode([
            'status' => 'success',
            'orders' => $orders,
            'pagination' => [
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems
            ]
        ]);
        break;

        // 2. Delete order and all related data
    case 'delete_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
            exit;
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Delete order-product relationships (client_order_indexes)
            $sqlDeleteOrderIndexes = "DELETE FROM client_order_indexes WHERE client_order_id = $order_id";
            if ($conn->query($sqlDeleteOrderIndexes) === FALSE) {
                throw new Exception('Error deleting order-product relationships: ' . $conn->error);
            }

            // Delete the order itself
            $sqlDeleteOrder = "DELETE FROM client_orders WHERE id = $order_id";
            if ($conn->query($sqlDeleteOrder) === FALSE) {
                throw new Exception('Error deleting order: ' . $conn->error);
            }

            // Commit the transaction
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Order successfully deleted']);
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

        // 3. Edit order
    case 'edit_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
            exit;
        }

        $dataOriginal = $request['data_original'];
        $dataUpdated = $request['data'];

        if (empty($dataOriginal) || empty($dataUpdated)) {
            echo json_encode(['status' => 'error', 'message' => 'Original or updated data is missing']);
            exit;
        }

        // Update order data
        $fieldsToUpdate = [];
        foreach (['client_id', 'address_id', 'payment_method_id', 'status'] as $field) {
            if ($dataOriginal['order'][$field] != $dataUpdated['order'][$field]) {
                $fieldsToUpdate[] = "$field = '" . $conn->real_escape_string($dataUpdated['order'][$field]) . "'";
            }
        }
        if (!empty($fieldsToUpdate)) {
            $fieldsToUpdate[] = "date_of_change = '" . date("Y-m-d H:i:s") . "'";
            $sqlUpdateOrder = "UPDATE client_orders SET " . implode(', ', $fieldsToUpdate) . " WHERE id = $order_id";
            if ($conn->query($sqlUpdateOrder) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Error updating order: ' . $conn->error]);
                exit;
            }
        }

        // Get products from original and updated data
        $originalProducts = [];
        foreach ($dataOriginal['products'] as $product) {
            $originalProducts[$product['product']['id']] = $product;
        }

        $updatedProducts = [];
        foreach ($dataUpdated['products'] as $product) {
            $updatedProducts[$product['product']['id']] = $product;
        }

        // Delete products not present in updated data
        foreach ($originalProducts as $productId => $originalProduct) {
            if (!isset($updatedProducts[$productId])) {
                $sqlDelete = "DELETE FROM client_order_indexes WHERE client_order_id = $order_id AND product_id = $productId";
                if ($conn->query($sqlDelete) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error deleting product: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Update and add products
        foreach ($updatedProducts as $productId => $updatedProduct) {
            $price = $conn->real_escape_string($updatedProduct['product']['price']);
            $sizeId = $conn->real_escape_string($updatedProduct['product']['size_id']);

            // Filter `order_options` to exclude zeros
            $optionsArray = array_filter(json_decode($updatedProduct['product']['order_options'], true), function ($value) {
                return $value != 0;
            });
            $optionsJson = $conn->real_escape_string(json_encode(array_values($optionsArray)));

            if (isset($originalProducts[$productId])) {
                // If product exists, update it
                $sqlUpdateProduct = "UPDATE client_order_indexes SET
                        price = '$price',
                        size_id = '$sizeId',
                        options = '$optionsJson'
                        WHERE client_order_id = $order_id AND product_id = $productId";
                if ($conn->query($sqlUpdateProduct) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error updating product: ' . $conn->error]);
                    exit;
                }
            } else {
                // If product is new, add it
                $sqlInsertProduct = "INSERT INTO client_order_indexes (client_order_id, product_id, price, size_id, options)
                        VALUES ($order_id, $productId, '$price', '$sizeId', '$optionsJson')";
                if ($conn->query($sqlInsertProduct) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding new product: ' . $conn->error]);
                    exit;
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Order successfully updated']);
        break;


    case 'get_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
            exit;
        }

        $data = [];

        // Get order, client, address, and payment method information
        $sqlOrder = "SELECT co.*, cl.name AS client_name, cl.email, cl.height, cl.shoulder_width, cl.waist_size,
                         ca.name AS address_name, ca.address, ca.phone, cpm.card_number
                         FROM client_orders co
                         JOIN clients cl ON co.client_id = cl.id
                         JOIN client_addresses ca ON co.address_id = ca.id
                         JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                         WHERE co.id = $order_id";
        $resultOrder = $conn->query($sqlOrder);

        if ($resultOrder->num_rows > 0) {
            $orderData = $resultOrder->fetch_assoc();
            $data['order'] = array_filter($orderData, function ($value) {
                return !is_null($value);
            });
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order not found']);
            exit;
        }

        // Get products information in the order
        $sqlProducts = "SELECT 
                coi.price AS price, 
                coi.options AS order_options,  
                coi.size_id AS size_id,
                p.*, 
                COALESCE(im.image_path, '/Image.png') AS image_path
            FROM client_order_indexes coi
            JOIN products p ON coi.product_id = p.id
            LEFT JOIN (
                SELECT 
                    product_id, 
                    image_path, 
                    ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY id ASC) AS rn
                FROM product_images
            ) im ON p.id = im.product_id AND im.rn = 1
            WHERE coi.client_order_id =  $order_id";
        $resultProducts = $conn->query($sqlProducts);
        $products = [];

        // print_r($sqlProducts);

        if ($resultProducts != false && $resultProducts->num_rows > 0) {
            while ($row = $resultProducts->fetch_assoc()) {
                $product_data = [];
                // Remove null fields from product
                $product_data['product'] = array_filter($row, function ($value) {
                    return !is_null($value);
                });
                // $product_data['product']['price'] = intval($product_data['product']['price']);

                $s_id = $row['size_id'];
                $sqlSize = "SELECT s.* FROM sizes s WHERE s.id=$s_id";
                $resultSize = $conn->query($sqlSize);


                if ($resultSize != false && $resultSize->num_rows > 0) {
                    $sizeData = $resultSize->fetch_assoc();
                    $product_data['size'] = array_filter($sizeData, function ($value) {
                        return !is_null($value);
                    });
                    $product_data['size']['price'] = intval($product_data['size']['price']);
                }
                $options = json_decode($row['order_options']);
                if ($row['order_options'] != null && !empty($options)) {
                    $options_sql_ids = implode(',', $options);
                    $sqlOptions = "SELECT * FROM options WHERE id IN ($options_sql_ids)";
                    $resultOptions = $conn->query($sqlOptions);

                    if ($resultOptions != false && $resultOptions->num_rows > 0) {
                        $product_data['options'] = [];
                        while ($row_o = $resultOptions->fetch_assoc()) {
                            $product_data['options'][] = array_filter($row_o, function ($value) {
                                return !is_null($value);
                            });
                        }
                    }
                }

                $products[] = $product_data;
            }
        }

        $data['products'] = $products;

        // Return the result
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;


    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();
exit();
