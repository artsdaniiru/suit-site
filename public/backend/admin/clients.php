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



// Проверка сессии
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_auth_token'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access."
    ]);
    exit;
}

$client_id = isset($_GET['client_id']) ? $_GET['client_id'] : '';

switch ($action) {
    case 'list_all_clients':
        $sql = "SELECT * FROM clients";
        $result = $conn->query($sql);
        $clients = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
        }
        echo json_encode(['status' => 'success', 'users' => $clients]);
        break;
    case 'delete_user':
        $sql = "DELETE FROM clients WHERE id=$client_id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
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
                    $result = $conn->query($sql);

                    $cart = [];
                    // Сохранение результатов в массив
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            unset($row['id']);
                            $row['id'] = $row['oi_id'];
                            unset($row['oi_id']);
                            $cart[] = $row;
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
