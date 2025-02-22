<?php
// Initialize the script
require_once 'starter.php';

// Start the session
session_start();

// Get the action via GET parameter 'action'
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process data received from the client
$request = json_decode(file_get_contents('php://input'), true);

// Check the session
if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

// Process requests
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'create_order':
        $cart = $request['cart'];

        // Get client_id
        $auth_token = $conn->real_escape_string($_SESSION['auth_token']);
        $sql = "SELECT id, email FROM clients WHERE auth_token = '$auth_token'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $client_id = intval($user['id']);
            $client_email = $user['email'];
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid token."]);
            exit;
        }

        $address_id = intval($request['address_id']);
        $payment_method_id = $request['payment_method_id'];

        // Разрешённые методы оплаты
        if ($payment_method_id == "CASH" || $payment_method_id == "KONBINI") {

            // Проверяем, существует ли метод оплаты для данного клиента и получаем его ID
            $sql_check = "SELECT id 
                          FROM client_payment_methods 
                          WHERE client_id = ? AND card_number = ? 
                          LIMIT 1";

            // Используем подготовленный запрос для безопасности
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("is", $client_id, $payment_method_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Метод оплаты уже существует, получаем его ID
                $payment_method_id = intval($row['id']);
            } else {
                // Если метода нет, добавляем его
                $sql_insert = "INSERT INTO client_payment_methods (client_id, card_number) 
                               VALUES (?, ?)";

                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("is", $client_id, $payment_method_id);

                if ($stmt_insert->execute()) {
                    $payment_method_id = intval($stmt_insert->insert_id);
                }
            }
        }


        // Create order in client_orders
        $sql = "INSERT INTO client_orders (client_id, address_id, payment_method_id, status) 
                VALUES ($client_id, $address_id, $payment_method_id, 'confirmed')";
        if ($conn->query($sql) === TRUE) {
            $order_id = $conn->insert_id;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create order: " . $conn->error]);
            exit;
        }

        $order_cart_products = "";

        // Process each product from $cart
        foreach ($cart as $item) {
            $product_id = intval($item['id']);
            $base_price = floatval($item['price']);
            $size_id = intval($item['size']);
            $options = [];
            $total_option_price = 0;

            // Process options
            if (isset($item['options'])) {
                foreach ($item['options'] as $option_category => $option) {
                    if ($option['id'] != "") {
                        $option_id = intval($option['id']);
                        $options[] = $option_id;
                        if (!$option['free']) {

                            // Get option price
                            $sql = "SELECT price FROM options WHERE id = $option_id";
                            $option_result = $conn->query($sql);

                            if ($option_result->num_rows > 0) {
                                $option_data = $option_result->fetch_assoc();
                                $total_option_price += floatval($option_data['price']);
                            }
                        }
                    }
                }
            }

            $final_price = $base_price + $total_option_price;
            $options_json = json_encode($options);

            $product_data_sql = "SELECT * FROM products WHERE id = $product_id";
            $product_result = $conn->query($product_data_sql);

            if ($product_result->num_rows > 0) {
                $product_data = $product_result->fetch_assoc();
                $order_cart_products .= $product_data['name'] . "    ¥" . number_format($final_price, 0, '', ' ') . "\r\n";
            }

            // Add record to client_order_indexes
            $sql = "INSERT INTO client_order_indexes (client_order_id, product_id, price, size_id, options) 
                    VALUES ($order_id, $product_id, $final_price, $size_id, '$options_json')";

            if (!$conn->query($sql)) {
                echo json_encode(["status" => "error", "message" => "Failed to add product: " . $conn->error]);
                exit;
            }
        }

        echo json_encode(["status" => "success", "order_id" => $order_id]);

        if ($_SERVER['HTTP_HOST'] == "arts-suit.com") {
            $adress_name = "";
            $address_data_sql = "SELECT * FROM client_addresses WHERE id = $address_id";
            $address_result = $conn->query($address_data_sql);

            if ($address_result->num_rows > 0) {
                $address_data = $address_result->fetch_assoc();
                $adress_name =  $address_data['address'] . " " . $address_data['phone'];
            }

            // Sending order complition email to user
            $to = $client_email;
            $order_id_formatted = str_pad($order_id, 5, '0', STR_PAD_LEFT);
            $subject = "注文番号： ＃" . $order_id_formatted . " ご注文ありがとうございました!";
            $message = "注文番号： ＃" . $order_id_formatted . " ご注文ありがとうございました!\r\n\r\n 注文内容：\r\n"; //TODO: добавить адрес и состав заказа
            $message .= "\r\n" . $order_cart_products;
            $message .= "\r\n" . $adress_name;
            $message .= "\r\n" . 'ご注文の詳細情報は、ウェブサイトの「マイページ」でご確認いただけます。';


            $headers = "From: mailer@arts-suit.com\r\n";
            $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
            $to_admin = "k237034@kccollege.ac.jp";

            if (!mail($to, $subject, $message, $headers)) {
                // echo json_encode(['status' => 'error', 'message' => "Can't send email"]);
            } else {
                mail($to_admin, "お客様からの注文: $order_id_formatted", $message, $headers);
                // echo json_encode(['status' => 'success', 'message' => "Email successfully sent!"]);
            }
        }


        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
