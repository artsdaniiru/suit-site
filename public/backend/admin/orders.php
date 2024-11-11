<?php
// Включаем файл конфигурации
require_once '../config.php';

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

// Обработка запросов
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Проверка сессии (если требуется)
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_auth_token'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access."
    ]);
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

switch ($action) {

        // 1. Список всех заказов
    case 'list_all_orders':
        $sql = "SELECT co.*, cl.name AS client_name, cl.email, cl.height, cl.shoulder_width, cl.waist_size,
                ca.name AS address_name, ca.address, ca.phone,
                cpm.card_number
                FROM client_orders co
                JOIN clients cl ON co.client_id = cl.id
                JOIN client_addresses ca ON co.address_id = ca.id
                JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id";

        $result = $conn->query($sql);
        $orders = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        echo json_encode(['status' => 'success', 'orders' => $orders]);
        break;

        // 2. Удаление заказа и всех связанных данных
    case 'delete_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID заказа']);
            exit;
        }

        // Начало транзакции
        $conn->begin_transaction();

        try {
            // Удаление связи заказа и продуктов (client_order_indexes)
            $sqlDeleteOrderIndexes = "DELETE FROM client_order_indexes WHERE client_order_id = $order_id";
            if ($conn->query($sqlDeleteOrderIndexes) === FALSE) {
                throw new Exception('Ошибка удаления элементов связи заказа и продуктов: ' . $conn->error);
            }

            // Удаление самого заказа
            $sqlDeleteOrder = "DELETE FROM client_orders WHERE id = $order_id";
            if ($conn->query($sqlDeleteOrder) === FALSE) {
                throw new Exception('Ошибка удаления заказа: ' . $conn->error);
            }

            // Завершаем транзакцию
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Заказ успешно удалён']);
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

        // 3. Редактирование заказа
    case 'edit_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID заказа']);
            exit;
        }

        $status = isset($request['status']) ? $request['status'] : '';

        if (empty($status)) {
            echo json_encode(['status' => 'error', 'message' => 'Не указано новое значение статуса']);
            exit;
        }

        // Обновление статуса заказа
        $sqlUpdateOrder = "UPDATE client_orders SET status = '$status', date_of_change = '" . date("Y-m-d H:i:s") . "' WHERE id = $order_id";
        if ($conn->query($sqlUpdateOrder) === TRUE) {
            echo json_encode(['status' => 'success', 'message' => 'Заказ успешно обновлен']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

        // 4. Получение конкретного заказа
    case 'get_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID заказа']);
            exit;
        }

        $data = [];

        // Получение информации о заказе, клиенте, адресе и методе оплаты
        $sqlOrder = "SELECT co.*, cl.name AS client_name, cl.email, cl.height, cl.shoulder_width, cl.waist_size,
                     ca.name AS address_name, ca.address, ca.phone, cpm.card_number
                     FROM client_orders co
                     JOIN clients cl ON co.client_id = cl.id
                     JOIN client_addresses ca ON co.address_id = ca.id
                     JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                     WHERE co.id = $order_id";
        $resultOrder = $conn->query($sqlOrder);

        if ($resultOrder->num_rows > 0) {
            $data['order'] = $resultOrder->fetch_assoc();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Заказ не найден']);
            exit;
        }

        // Получение информации о продуктах в заказе
        $sqlProducts = "SELECT coi.price AS order_price, coi.options AS order_options,
                        p.*, s.*
                        FROM client_order_indexes coi
                        JOIN products p ON coi.product_id = p.id
                        JOIN sizes s ON coi.size_id = s.id
                        WHERE coi.client_order_id = $order_id";
        $resultProducts = $conn->query($sqlProducts);
        $products = [];

        if ($resultProducts->num_rows > 0) {
            while ($row = $resultProducts->fetch_assoc()) {
                $products[] = $row;
            }
        }

        $data['products'] = $products;

        // Возвращаем результат
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();
exit();
