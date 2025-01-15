<?php
// Initialize the script
require_once 'starter.php';

// Start the session
session_start();

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Check the session
if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'edit_client':
        $updateFields = [];

        // Use the function to add fields
        addFieldToUpdate($updateFields, $request, 'name');
        addFieldToUpdate($updateFields, $request, 'email');
        addFieldToUpdate($updateFields, $request, 'height');
        addFieldToUpdate($updateFields, $request, 'shoulder_width');
        addFieldToUpdate($updateFields, $request, 'waist_size');

        if (!empty($updateFields)) {
            $setClause = implode(', ', $updateFields);
            $sql = "UPDATE clients SET $setClause WHERE id = $user_id";

            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        }
        break;

    case 'edit_password':
        $password = password_hash($request['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE clients SET password='$password' WHERE id = $user_id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    case 'edit_address':
        $address_id = isset($_GET['address_id']) ? $_GET['address_id'] : '';
        $updateFields = [];

        addFieldToUpdate($updateFields, $request, 'name');
        addFieldToUpdate($updateFields, $request, 'address');
        addFieldToUpdate($updateFields, $request, 'phone');

        if (!empty($updateFields)) {
            $setClause = implode(', ', $updateFields);
            $sql = "UPDATE client_addresses SET $setClause WHERE client_id = $user_id AND id=$address_id";

            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        }
        break;

    case 'edit_payment_method':
        $payment_method_id = isset($_GET['payment_method_id']) ? $_GET['payment_method_id'] : '';
        $updateFields = [];

        addFieldToUpdate($updateFields, $request, 'card_number');

        if (!empty($updateFields)) {
            $setClause = implode(', ', $updateFields);
            $sql = "UPDATE client_payment_methods SET $setClause WHERE client_id = $user_id AND id=$payment_method_id";

            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        }
        break;

    case 'delete_client':
        // Check that the client ID is valid
        if ($user_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid client ID']);
            exit;
        }

        // Start the transaction
        $conn->begin_transaction();

        try {
            // Delete client's cart items first in client_order_indexes
            $sqlDeleteOrderIndexes = "DELETE FROM client_order_indexes WHERE client_order_id IN (SELECT id FROM client_orders WHERE client_id = $user_id)";
            if ($conn->query($sqlDeleteOrderIndexes) === FALSE) {
                throw new Exception('Error deleting cart items: ' . $conn->error);
            }

            // Then delete the client's orders
            $sqlDeleteOrders = "DELETE FROM client_orders WHERE client_id = $user_id";
            if ($conn->query($sqlDeleteOrders) === FALSE) {
                throw new Exception('Error deleting client orders: ' . $conn->error);
            }

            // Delete client's payment methods
            $sqlDeletePaymentMethods = "DELETE FROM client_payment_methods WHERE client_id = $user_id";
            if ($conn->query($sqlDeletePaymentMethods) === FALSE) {
                throw new Exception('Error deleting client payment methods: ' . $conn->error);
            }

            // Delete client's addresses after deleting orders
            $sqlDeleteAddresses = "DELETE FROM client_addresses WHERE client_id = $user_id";
            if ($conn->query($sqlDeleteAddresses) === FALSE) {
                throw new Exception('Error deleting client addresses: ' . $conn->error);
            }

            // Delete the client itself
            $sqlDeleteClient = "DELETE FROM clients WHERE id = $user_id";
            if ($conn->query($sqlDeleteClient) === FALSE) {
                throw new Exception('Error deleting client: ' . $conn->error);
            }

            // If everything went well, commit the transaction
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Client and all related data deleted successfully']);
        } catch (Exception $e) {
            // In case of an error, roll back the transaction
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        break;

    case 'delete_address':
        $address_id = isset($_GET['address_id']) ? $_GET['address_id'] : '';
        $sql = "UPDATE client_addresses 
                    SET active = 0 
                    WHERE client_id = $user_id AND id = $address_id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    case 'delete_payment_method':
        $payment_method_id = isset($_GET['payment_method_id']) ? $_GET['payment_method_id'] : '';
        $sql = "UPDATE client_payment_methods 
                    SET active = 0 
                    WHERE client_id = $user_id AND id = $payment_method_id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    case 'add_address':
        $address_name = $request['address_name'];
        $address = $request['address'];
        $phone = $request['phone'];

        $sql = "INSERT INTO client_addresses (name, client_id, address, phone) 
                VALUES ('$address_name', '$user_id', '$address', '$phone')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    case 'add_payment_method':
        $card_number = $request['card_number'];

        $sql = "INSERT INTO client_payment_methods (client_id, card_number) 
                    VALUES ('$user_id', '$card_number')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    case 'get_client_orders':
        if (!$user_id) {
            echo json_encode(['status' => 'fail', 'data' => 'No client_id provided']);
            exit;
        }

        $data = [];
        $sql = "SELECT 
                    co.id, co.status,
                    ca.name AS client_name,
                    ca.address AS client_address,
                    ca.phone AS client_phone,
                    cpm.card_number
                FROM client_orders co
                JOIN client_addresses ca ON co.address_id = ca.id
                JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                WHERE co.client_id = $user_id";

        $result = $conn->query($sql);

        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $conn->error]);
            exit;
        }

        $client_orders = [];
        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $order_id = $row['id'];

                // Getting products from orders
                $cart_sql = "SELECT 
                                coi.price AS order_price,
                                coi.options AS order_options,
                                coi.id AS oi_id,
                                p.*,
                                p.name AS product_name,
                                p.id AS p_id,
                                s.*,
                                s.id AS s_id, 
                                s.name AS size_name, 
                            COALESCE(im.image_path, '/Image.png') AS image_path
                            FROM client_order_indexes coi
                            JOIN products p ON coi.product_id = p.id
                            JOIN sizes s ON coi.size_id = s.id
                            LEFT JOIN (
                            SELECT 
                                product_id, 
                                image_path, 
                                ROW_NUMBER() OVER (PARTITION BY product_id ORDER BY id ASC) AS rn
                            FROM product_images
                        ) im ON p.id = im.product_id AND im.rn = 1
                            WHERE client_order_id = $order_id";

                $cart_result = $conn->query($cart_sql);
                $cart = [];
                $total_price = 0;
                if ($cart_result->num_rows > 0) {
                    while ($cart_row = $cart_result->fetch_assoc()) {
                        $cart_row['id'] = $cart_row['oi_id'];
                        unset($cart_row['oi_id']);
                        $total_price += intval($cart_row['order_price']);

                        $options = json_decode($cart_row['order_options']);
                        if ($cart_row['order_options'] != null && !empty($options)) {
                            $options_sql_ids = implode(',', $options);
                            $sqlOptions = "SELECT * FROM options WHERE id IN ($options_sql_ids)";
                            $resultOptions = $conn->query($sqlOptions);

                            if ($resultOptions != false && $resultOptions->num_rows > 0) {
                                $cart_row['options'] = [];
                                while ($row_o = $resultOptions->fetch_assoc()) {
                                    $cart_row['options'][] = array_filter($row_o, function ($value) {
                                        return !is_null($value);
                                    });
                                }
                            }
                        }
                        unset($cart_row['order_options']);

                        $cart[] = $cart_row;
                    }
                }

                $row['cart'] = $cart;
                $row['total_price'] = $total_price;
                $client_orders[] = $row;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $client_orders]);
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
