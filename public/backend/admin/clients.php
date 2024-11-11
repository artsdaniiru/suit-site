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

// Проверка сессии
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
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name'; // Тип сортировки по умолчанию - по имени
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Число клиентов на одной странице
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Текущая страница
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Строка поиска (по умолчанию пустая)

// Определение сортировки в зависимости от параметра $sort
switch ($sort) {
    case 'date_desc':
        $orderBy = 'clients.date_of_registration DESC'; // Сначала новые клиенты
        break;
    case 'date_asc':
        $orderBy = 'clients.date_of_registration ASC'; // Сначала старые клиенты
        break;
    case 'name_desc':
        $orderBy = 'clients.name DESC'; // Сортировка по имени desc
        break;
    case 'name_asc':
        $orderBy = 'clients.name ASC'; // Сортировка по имени asc
        break;
    case 'email_desc':
        $orderBy = 'clients.email DESC'; // Сортировка по email desc
        break;
    case 'email_asc':
        $orderBy = 'clients.email ASC'; // Сортировка по email asc
        break;
    default:
        $orderBy = 'clients.name DESC'; // По умолчанию сортировка по имени
}

// Вычисление смещения для пагинации
$offset = ($page - 1) * $itemsPerPage;

// Подготовка условия для строки поиска
$searchCondition = '';
if (!empty($query)) {
    // Экранируем значение поиска для безопасности
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (`name` LIKE '%$query%' OR `email` LIKE '%$query%' OR `login` LIKE '%$query%')";
}

$client_id = isset($_GET['client_id']) ? $_GET['client_id'] : '';

switch ($action) {
    case 'list_all_clients':
        // Условия фильтрации (например, для поиска или сортировки клиентов)


        // Параметры сортировки, пагинации и лимитов
        $orderBy = isset($orderBy) ? $orderBy : "name ASC";
        $offset = isset($offset) ? $offset : 0;
        $itemsPerPage = isset($itemsPerPage) ? $itemsPerPage : 10;

        // Запрос с учетом условий, сортировки и пагинации
        $sql = "SELECT * FROM clients 
                WHERE 1=1 $searchCondition
                ORDER BY $orderBy
                LIMIT $offset, $itemsPerPage";

        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $conn->error]);
            exit;
        }

        $clients = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
        }

        // Получение общего количества записей для пагинации
        $totalCountResult = $conn->query("SELECT COUNT(*) as count FROM clients WHERE 1=1 $searchCondition");
        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка получения количества клиентов: ' . $conn->error]);
            exit;
        }
        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = intval($totalCountRow['count']);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Вывод результата с информацией о клиентах и пагинации
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
    case 'delete_user':
        // Получение ID клиента из запроса
        $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

        // Проверка, что ID клиента передан
        if ($client_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID клиента']);
            exit;
        }

        // Начало транзакции
        $conn->begin_transaction();

        try {
            // Удаление заказов клиента
            // Сначала удалим связанные элементы корзины в client_order_indexes
            $sqlDeleteOrderIndexes = "DELETE FROM client_order_indexes WHERE client_order_id IN (SELECT id FROM client_orders WHERE client_id = $client_id)";
            if ($conn->query($sqlDeleteOrderIndexes) === FALSE) {
                throw new Exception('Ошибка удаления элементов корзины: ' . $conn->error);
            }

            // Затем удаляем сами заказы клиента
            $sqlDeleteOrders = "DELETE FROM client_orders WHERE client_id = $client_id";
            if ($conn->query($sqlDeleteOrders) === FALSE) {
                throw new Exception('Ошибка удаления заказов клиента: ' . $conn->error);
            }

            // Удаление методов оплаты клиента
            $sqlDeletePaymentMethods = "DELETE FROM client_payment_methods WHERE client_id = $client_id";
            if ($conn->query($sqlDeletePaymentMethods) === FALSE) {
                throw new Exception('Ошибка удаления методов оплаты клиента: ' . $conn->error);
            }

            // Удаление адресов клиента после удаления заказов
            $sqlDeleteAddresses = "DELETE FROM client_addresses WHERE client_id = $client_id";
            if ($conn->query($sqlDeleteAddresses) === FALSE) {
                throw new Exception('Ошибка удаления адресов клиента: ' . $conn->error);
            }

            // Удаление самого клиента
            $sqlDeleteClient = "DELETE FROM clients WHERE id = $client_id";
            if ($conn->query($sqlDeleteClient) === FALSE) {
                throw new Exception('Ошибка удаления клиента: ' . $conn->error);
            }

            // Если все прошло успешно, подтверждаем транзакцию
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Клиент и все связанные данные успешно удалены']);
        } catch (Exception $e) {
            // В случае ошибки откатываем транзакцию
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        break;
    case 'edit_user':
        $updateFields = [];

        // Используем функцию для добавления полей
        addFieldToUpdate($updateFields, $request, 'login');
        addFieldToUpdate($updateFields, $request, 'name');
        addFieldToUpdate($updateFields, $request, 'email');
        addFieldToUpdate($updateFields, $request, 'height');
        addFieldToUpdate($updateFields, $request, 'shoulder_width');
        addFieldToUpdate($updateFields, $request, 'waist_size');

        // Проверяем, если есть поля для обновления
        if (!empty($updateFields)) {
            // Формируем строку с обновляемыми полями
            $setClause = implode(', ', $updateFields);

            // Создаем SQL-запрос
            $sql = "UPDATE clients SET $setClause WHERE id = $client_id";

            // Выполняем запрос
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        }
        break;
    case 'get_client':
        // Получаем данные о конретном клиенте
        $data = [];
        if ($client_id == false) {
            $data = 'Data not found (no client_id)';
            echo json_encode(['status' => 'fail', 'data' => $data]);
        } else {
            // SQL-запрос (к clients)
            $sql = "SELECT * FROM clients
                    WHERE id=$client_id";

            $result = $conn->query($sql);
            if (!$result) {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $conn->error]);
                exit;
            }

            // Сохранение результатов в массив
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data['client'] = $row;
                }
            }

            // SQL-запрос (к client_addresses)
            $sql = "SELECT * FROM client_addresses
                    WHERE client_id=$client_id";

            $result = $conn->query($sql);

            $client_addresses = [];

            // Сохранение результатов в массив
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $client_addresses[] = $row;
                }
            }
            $data['client_addresses'] = $client_addresses;

            // SQL-запрос (к client_payment_methods)
            $sql = "SELECT * FROM client_payment_methods
                    WHERE client_id=$client_id";
            $result = $conn->query($sql);

            $client_payment_methods = [];
            // Сохранение результатов в массив
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $client_payment_methods[] = $row;
                }
            }
            $data['client_payment_methods'] = $client_payment_methods;

            // SQL-запрос (к client_orders)
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
            // Сохранение результатов в массив
            if ($result->num_rows > 0) {
                // print($result->num_rows);
                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];

                    //SQL-запрос для формирования cart пользователя
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
                    // Сохранение результатов в массив
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
        $password = password_hash($request['password'], PASSWORD_DEFAULT); // Хэширование пароля
        $sql = "UPDATE clients 
            SET password='$password'
            WHERE id = $client_id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    case 'edit_address':
        $address_id = isset($_GET['address_id']) ? $_GET['address_id'] : '';
        if ($address_id == "") {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        } else {
            $updateFields = [];

            // Используем функцию для добавления полей
            addFieldToUpdate($updateFields, $request, 'name');
            addFieldToUpdate($updateFields, $request, 'address');
            addFieldToUpdate($updateFields, $request, 'phone');

            if (!empty($updateFields)) {
                $setClause = implode(', ', $updateFields);
                $sql = "UPDATE client_addresses SET $setClause WHERE client_id = $client_id AND id=$address_id";

                if ($conn->query($sql) === TRUE) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => $conn->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            }
        }
        break;

    case 'delete_address':
        $address_id = isset($_GET['address_id']) ? $_GET['address_id'] : '';
        if ($address_id == "") {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        } else {
            $sql = "DELETE FROM client_addresses WHERE client_id=$client_id AND id=$address_id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
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
