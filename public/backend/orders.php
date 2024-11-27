<?php
// Включаем файл конфигурации
require_once 'config.php';

// Стартуем сессию
session_start();

// Разрешаем доступ с любого источника
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// Обрабатываем preflight-запрос
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Подключение к базе данных
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получаем действие через GET-параметр 'action'
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Обработка данных, полученных от клиента
$request = json_decode(file_get_contents('php://input'), true);

// Проверка сессии
if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

// Обработка запросов
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'create_order':
        $cart = $request['cart'];

        // Получаем client_id
        $auth_token = $conn->real_escape_string($_SESSION['auth_token']);
        $sql = "SELECT id FROM clients WHERE auth_token = '$auth_token'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $client_id = intval($user['id']);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid token."]);
            exit;
        }

        $address_id = intval($request['address_id']);
        $payment_method_id = intval($request['payment_method_id']);

        // Создаём заказ в client_orders
        $sql = "INSERT INTO client_orders (client_id, address_id, payment_method_id, status) 
                VALUES ($client_id, $address_id, $payment_method_id, 'confirmed')";
        if ($conn->query($sql) === TRUE) {
            $order_id = $conn->insert_id;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create order: " . $conn->error]);
            exit;
        }

        // Обрабатываем каждый продукт из $cart
        foreach ($cart as $item) {
            $product_id = intval($item['id']);
            $base_price = floatval($item['price']);
            $size_id = intval($item['size']);
            $options = [];
            $total_option_price = 0;

            // Обрабатываем опции
            if (isset($item['options'])) {
                foreach ($item['options'] as $option_category => $option) {
                    if ($option['id'] != "") {
                        $option_id = intval($option['id']);
                        $options[] = $option_id;
                        if (!$option['free']) {

                            // Получаем цену опции
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

            // Добавляем запись в client_order_indexes
            $sql = "INSERT INTO client_order_indexes (client_order_id, product_id, price, size_id, options) VALUES ($order_id, $product_id, $final_price, $size_id, '$options_json')";

            if (!$conn->query($sql)) {
                echo json_encode(["status" => "error", "message" => "Failed to add product: " . $conn->error]);
                exit;
            }
        }

        echo json_encode(["status" => "success", "order_id" => $order_id]);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
