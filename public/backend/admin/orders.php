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

// Проверка сессии (если требуется)
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_auth_token'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access."
    ]);
    exit;
}

// Обработка запросов
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Получение параметров из GET-запроса
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id'; // Тип сортировки по умолчанию - по id
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Число клиентов на одной странице
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Текущая страница
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Строка поиска (по умолчанию пустая)
$status = isset($_GET['status']) ? $_GET['status'] : ''; // Фильтр по статусу заказа

// Определение сортировки в зависимости от параметра $sort
switch ($sort) {
    case 'id_desc':
        $orderBy = 'co.id DESC'; // Сортировка по id desc
        break;
    case 'id_asc':
        $orderBy = 'co.id ASC'; //  Сортировка по id asc
        break;
    case 'name_desc':
        $orderBy = 'ca.name DESC'; // Сортировка по имени клиента desc
        break;
    case 'name_asc':
        $orderBy = 'ca.name ASC'; // Сортировка по имени клиента asc
        break;
    case 'status_desc':
        $orderBy = 'co.status DESC'; // Сортировка по status desc
        break;
    case 'status_asc':
        $orderBy = 'co.status ASC'; // Сортировка по status asc
        break;
    case 'email_desc':
        $orderBy = 'cl.email DESC'; // Сортировка по email desc
        break;
    case 'email_asc':
        $orderBy = 'cl.email ASC'; // Сортировка по email asc
        break;
    case 'phone_desc':
        $orderBy = 'ca.phone DESC'; // Сортировка по email desc
        break;
    case 'phone_asc':
        $orderBy = 'ca.phone ASC'; // Сортировка по email asc
        break;
    case 'date_desc':
        $orderBy = 'co.date_of_creation DESC'; // Сначала новые заказы
        break;
    case 'date_asc':
        $orderBy = 'co.date_of_creation ASC'; // Сначала старые заказы
        break;
    default:
        $orderBy = 'co.id DESC'; // По умолчанию сортировка по id
}

// Вычисление смещения для пагинации
$offset = ($page - 1) * $itemsPerPage;

// Подготовка условия для строки поиска
$searchCondition = '';
if (!empty($query)) {
    // Экранируем значение поиска для безопасности
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (co.id LIKE '%$query%' OR ca.name LIKE '%$query%' OR co.status LIKE '%$query%' OR cl.email LIKE '%$query%')";
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

switch ($action) {

        // 1. Список всех заказов
    case 'list_all_orders':
        // Условия фильтрации (например, для поиска или сортировки заказов)
        $searchCondition = isset($searchCondition) ? $searchCondition : "";

        $filter = "";
        if ($status != "") {
            $filter .= " AND co.status = '$status'";
        }

        // Параметры сортировки, пагинации и лимитов
        $orderBy = isset($orderBy) ? $orderBy : "co.id DESC";
        $offset = isset($offset) ? $offset : 0;
        $itemsPerPage = isset($itemsPerPage) ? $itemsPerPage : 10;
        $page = isset($page) ? $page : 1;

        // Запрос с учетом условий, сортировки и пагинации
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
            echo json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $conn->error]);
            exit;
        }

        $orders = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }

        // Получение общего количества записей для пагинации
        $totalCountResult = $conn->query("SELECT COUNT(*) as count FROM client_orders co
                    JOIN clients cl ON co.client_id = cl.id
                    JOIN client_addresses ca ON co.address_id = ca.id
                    JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                    WHERE 1=1 $searchCondition $filter $searchCondition $filter");
        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка получения количества заказов: ' . $conn->error]);
            exit;
        }
        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = intval($totalCountRow['count']);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Вывод результата с информацией о заказах и пагинации
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
