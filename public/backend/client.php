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

// Обработка запросов
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);



// Проверка сессии
if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'edit_client':
        $updateFields = [];

        // Используем функцию для добавления полей
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
    case 'delete_client':
        // Проверка, что ID клиента передан
        if ($user_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID клиента']);
            exit;
        }

        // Начало транзакции
        $conn->begin_transaction();

        try {
            // Удаление заказов клиента
            // Сначала удалим связанные элементы корзины в client_order_indexes
            $sqlDeleteOrderIndexes = "DELETE FROM client_order_indexes WHERE client_order_id IN (SELECT id FROM client_orders WHERE client_id = $user_id)";
            if ($conn->query($sqlDeleteOrderIndexes) === FALSE) {
                throw new Exception('Ошибка удаления элементов корзины: ' . $conn->error);
            }

            // Затем удаляем сами заказы клиента
            $sqlDeleteOrders = "DELETE FROM client_orders WHERE client_id = $user_id";
            if ($conn->query($sqlDeleteOrders) === FALSE) {
                throw new Exception('Ошибка удаления заказов клиента: ' . $conn->error);
            }

            // Удаление методов оплаты клиента
            $sqlDeletePaymentMethods = "DELETE FROM client_payment_methods WHERE client_id = $user_id";
            if ($conn->query($sqlDeletePaymentMethods) === FALSE) {
                throw new Exception('Ошибка удаления методов оплаты клиента: ' . $conn->error);
            }

            // Удаление адресов клиента после удаления заказов
            $sqlDeleteAddresses = "DELETE FROM client_addresses WHERE client_id = $user_id";
            if ($conn->query($sqlDeleteAddresses) === FALSE) {
                throw new Exception('Ошибка удаления адресов клиента: ' . $conn->error);
            }

            // Удаление самого клиента
            $sqlDeleteClient = "DELETE FROM clients WHERE id = $user_id";
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
