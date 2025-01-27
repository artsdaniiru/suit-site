<?php
// Initialize the script
require_once '../starter.php';

// Start the session
session_start();

// Check session
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
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name'; // Default sort type - by name
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Number of clients per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Current page
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Search string (default empty)

// Determine sorting based on $sort parameter
switch ($sort) {
    case 'date_desc':
        $orderBy = 'clients.date_of_registration DESC'; // Newest clients first
        break;
    case 'date_asc':
        $orderBy = 'clients.date_of_registration ASC'; // Oldest clients first
        break;
    case 'name_desc':
        $orderBy = 'clients.name DESC'; // Sort by name descending
        break;
    case 'name_asc':
        $orderBy = 'clients.name ASC'; // Sort by name ascending
        break;
    case 'email_desc':
        $orderBy = 'clients.email DESC'; // Sort by email descending
        break;
    case 'email_asc':
        $orderBy = 'clients.email ASC'; // Sort by email ascending
        break;
    default:
        $orderBy = 'clients.name DESC'; // Default sort by name descending
}

// Calculate offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Prepare condition for search string
$searchCondition = '';
if (!empty($query)) {
    // Escape search value for security
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (`name` LIKE '%$query%' OR `email` LIKE '%$query%' OR `login` LIKE '%$query%')";
}

$client_id = isset($_GET['client_id']) ? $_GET['client_id'] : '';

switch ($action) {
    case 'list_all_clients':
        // Filtering conditions (e.g., for searching or sorting clients)

        // Sorting, pagination, and limit parameters
        $orderBy = isset($orderBy) ? $orderBy : "name ASC";
        $offset = isset($offset) ? $offset : 0;
        $itemsPerPage = isset($itemsPerPage) ? $itemsPerPage : 10;

        // Query considering conditions, sorting, and pagination
        $sql = "SELECT * FROM clients 
                WHERE 1=1 $searchCondition
                ORDER BY $orderBy
                LIMIT $offset, $itemsPerPage";

        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Query execution error: ' . $conn->error]);
            exit;
        }

        $clients = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
        }

        // Get total count for pagination
        $totalCountResult = $conn->query("SELECT COUNT(*) as count FROM clients WHERE 1=1 $searchCondition");
        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Error fetching client count: ' . $conn->error]);
            exit;
        }
        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = intval($totalCountRow['count']);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Output result with client information and pagination
        echo json_encode([
            'status' => 'success',
            'clients' => $clients,
            'pagination' => [
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems
            ]
        ]);
        break;

    case 'delete_client':
        // Get client ID from request
        $client_id = isset($_GET['client_id']) ? (int)$client_id : 0;

        // Check that client ID is provided
        if ($client_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid client ID']);
            exit;
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Delete client's order indexes
            $sqlDeleteOrderIndexes = "DELETE FROM client_order_indexes WHERE client_order_id IN (SELECT id FROM client_orders WHERE client_id = $client_id)";
            if ($conn->query($sqlDeleteOrderIndexes) === FALSE) {
                throw new Exception('Error deleting order indexes: ' . $conn->error);
            }

            // Delete client's orders
            $sqlDeleteOrders = "DELETE FROM client_orders WHERE client_id = $client_id";
            if ($conn->query($sqlDeleteOrders) === FALSE) {
                throw new Exception('Error deleting client orders: ' . $conn->error);
            }

            // Delete client's payment methods
            $sqlDeletePaymentMethods = "DELETE FROM client_payment_methods WHERE client_id = $client_id";
            if ($conn->query($sqlDeletePaymentMethods) === FALSE) {
                throw new Exception('Error deleting client payment methods: ' . $conn->error);
            }

            // Delete client's addresses after deleting orders
            $sqlDeleteAddresses = "DELETE FROM client_addresses WHERE client_id = $client_id";
            if ($conn->query($sqlDeleteAddresses) === FALSE) {
                throw new Exception('Error deleting client addresses: ' . $conn->error);
            }

            // Delete the client
            $sqlDeleteClient = "DELETE FROM clients WHERE id = $client_id";
            if ($conn->query($sqlDeleteClient) === FALSE) {
                throw new Exception('Error deleting client: ' . $conn->error);
            }

            // If everything is successful, commit the transaction
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Client and all related data successfully deleted']);
        } catch (Exception $e) {
            // In case of error, rollback the transaction
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        break;

    case 'edit_client':
        // Check for original and updated client data
        if (!isset($request['data_original']) || !isset($request['data'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing original or updated client data']);
            exit;
        }

        $originalData = $request['data_original'];
        $updatedData = $request['data'];

        $client_id = intval($originalData['client']['id']);

        // --- Update client data ---
        if ($originalData['client'] !== $updatedData['client']) {
            $sql = "UPDATE clients SET
                        login = '" . $conn->real_escape_string($updatedData['client']['login']) . "',
                        name = '" . $conn->real_escape_string($updatedData['client']['name']) . "',
                        email = '" . $conn->real_escape_string($updatedData['client']['email']) . "',
                        height = '" . $conn->real_escape_string($updatedData['client']['height']) . "',
                        shoulder_width = '" . $conn->real_escape_string($updatedData['client']['shoulder_width']) . "',
                        waist_size = '" . $conn->real_escape_string($updatedData['client']['waist_size']) . "'
                        WHERE id = '$client_id'";

            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Error updating client: ' . $conn->error]);
                exit;
            }
        }

        // --- Handle changes, additions, and deactivation of addresses ---
        if (isset($updatedData['client_addresses'])) {
            $addressDataOriginal = array_column($originalData['client_addresses'], null, 'id');
            $addressDataUpdated = array_column($updatedData['client_addresses'], null, 'id');

            // Update and add addresses
            foreach ($addressDataUpdated as $addressId => $updatedAddress) {
                if (isset($addressDataOriginal[$addressId])) {
                    // Update existing address if it has changed
                    if ($addressDataOriginal[$addressId] !== $updatedAddress) {
                        $sql = "UPDATE client_addresses SET
                                    name = '" . $conn->real_escape_string($updatedAddress['name']) . "',
                                    address = '" . $conn->real_escape_string($updatedAddress['address']) . "',
                                    phone = '" . $conn->real_escape_string($updatedAddress['phone']) . "',
                                    active = 1
                                    WHERE client_id = $client_id AND id = $addressId";

                        if ($conn->query($sql) === FALSE) {
                            echo json_encode(['status' => 'error', 'message' => 'Error updating address: ' . $conn->error]);
                            exit;
                        }
                    }
                } else {
                    // Add new address
                    $sql = "INSERT INTO client_addresses (client_id, name, address, phone, active) VALUES (
                                $client_id,
                                '" . $conn->real_escape_string($updatedAddress['name']) . "',
                                '" . $conn->real_escape_string($updatedAddress['address']) . "',
                                '" . $conn->real_escape_string($updatedAddress['phone']) . "',
                                1
                        )";

                    if ($conn->query($sql) === FALSE) {
                        echo json_encode(['status' => 'error', 'message' => 'Error adding new address: ' . $conn->error]);
                        exit;
                    }
                }
            }

            // Deactivate addresses not present in updated data
            foreach ($addressDataOriginal as $addressId => $originalAddress) {
                if (!isset($addressDataUpdated[$addressId])) {
                    $sql = "UPDATE client_addresses SET active = 0 WHERE client_id = $client_id AND id = $addressId";
                    if ($conn->query($sql) === FALSE) {
                        echo json_encode(['status' => 'error', 'message' => 'Error deactivating address: ' . $conn->error]);
                        exit;
                    }
                }
            }
        }

        // --- Handle changes and deactivation of payment methods ---
        if (isset($updatedData['client_payment_methods'])) {
            $paymentDataOriginal = array_column($originalData['client_payment_methods'], null, 'id');
            $paymentDataUpdated = array_column($updatedData['client_payment_methods'], null, 'id');

            // Update existing payment methods
            foreach ($paymentDataUpdated as $paymentId => $updatedPayment) {
                if (isset($paymentDataOriginal[$paymentId])) {
                    // Update payment method if it has changed
                    if ($paymentDataOriginal[$paymentId] !== $updatedPayment) {
                        $sql = "UPDATE client_payment_methods SET
                                    card_number = '" . $conn->real_escape_string($updatedPayment['card_number']) . "',
                                    active = 1
                                    WHERE client_id = $client_id AND id = $paymentId";

                        if ($conn->query($sql) === FALSE) {
                            echo json_encode(['status' => 'error', 'message' => 'Error updating payment method: ' . $conn->error]);
                            exit;
                        }
                    }
                }
            }

            // Deactivate payment methods not present in updated data
            foreach ($paymentDataOriginal as $paymentId => $originalPayment) {
                if (!isset($paymentDataUpdated[$paymentId])) {
                    $sql = "UPDATE client_payment_methods SET active = 0 WHERE client_id = $client_id AND id = $paymentId";
                    if ($conn->query($sql) === FALSE) {
                        echo json_encode(['status' => 'error', 'message' => 'Error deactivating payment method: ' . $conn->error]);
                        exit;
                    }
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Client and related data updated successfully']);
        break;

    case 'get_client':
        // Get data about a specific client
        $data = [];
        if ($client_id == false) {
            $data = 'Data not found (no client_id)';
            echo json_encode(['status' => 'fail', 'data' => $data]);
        } else {
            // SQL query (to clients)
            $sql = "SELECT * FROM clients
                    WHERE id=$client_id";

            $result = $conn->query($sql);
            if (!$result) {
                echo json_encode(['status' => 'error', 'message' => 'Query execution error: ' . $conn->error]);
                exit;
            }

            // Store results in array
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data['client'] = $row;
                }
            }

            // SQL query (to client_addresses)
            $sql = "SELECT * FROM client_addresses
                    WHERE client_id=$client_id";

            $result = $conn->query($sql);

            $client_addresses = [];

            // Store results in array
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $client_addresses[] = $row;
                }
            }
            $data['client_addresses'] = $client_addresses;

            // SQL query (to client_payment_methods)
            $sql = "SELECT * FROM client_payment_methods
                    WHERE client_id=$client_id AND active=1";
            $result = $conn->query($sql);

            $client_payment_methods = [];
            // Store results in array
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $client_payment_methods[] = $row;
                }
            }
            $data['client_payment_methods'] = $client_payment_methods;

            // SQL query (to client_orders)
            $sql = "SELECT 
            co.id, co.status,
            ca.name AS client_name,
            ca.address AS client_address,
            ca.phone AS client_phone,
            cpm.card_number
            FROM client_orders co
            JOIN client_addresses ca ON co.address_id = ca.id
            JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
            WHERE co.client_id = $client_id ";
            $result = $conn->query($sql);

            $client_orders = [];
            // Store results in array
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];

                    // SQL query to form the user's cart
                    $sql = "SELECT 
                    coi.price AS order_price,
                    coi.options AS order_options,
                    coi.id AS oi_id,
                    p.*,
                    p.id AS p_id,
                    s.*,
                    s.id AS s_id
                    FROM client_order_indexes coi
                    JOIN products p ON coi.product_id = p.id
                    JOIN sizes s ON coi.size_id = s.id
                    WHERE client_order_id = $id";
                    $result_cart = $conn->query($sql);

                    $cart = [];
                    // Store results in array
                    if ($result_cart->num_rows > 0) {
                        while ($row_cart = $result_cart->fetch_assoc()) {
                            unset($row_cart['id']);
                            $row_cart['id'] = $row_cart['oi_id'];
                            unset($row_cart['oi_id']);
                            $cart[] = $row_cart;
                        }
                    }

                    $row['cart'] = $cart;
                    $client_orders[] = $row;
                }
            }
            $data['client_orders'] = $client_orders;

            echo json_encode(['status' => 'success', 'data' => $data]);
        }

        break;

    case 'edit_password':
        $password = password_hash($request['password'], PASSWORD_DEFAULT); // Hash the password
        $sql = "UPDATE clients 
            SET password='$password'
            WHERE id = $client_id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    case 'active_address':
        $address_id = isset($_GET['address_id']) ? $_GET['address_id'] : '';
        $sql = "UPDATE client_addresses
                SET active = 1
                WHERE id = $address_id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    case 'active_payment_method':
        $payment_method_id = isset($_GET['payment_method_id']) ? $_GET['payment_method_id'] : '';
        $sql = "UPDATE client_payment_methods
                    SET active = 1
                    WHERE id = $payment_method_id";
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
