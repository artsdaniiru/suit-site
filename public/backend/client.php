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
    case 'edit_user':
        $updateFields = [];

        // Используем функцию для добавления полей
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
            $sql = "UPDATE clients SET $setClause WHERE id = $user_id";

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
    case 'edit_password':
        $password = password_hash($request['password'], PASSWORD_DEFAULT); // Хэширование пароля
        $sql = "UPDATE clients 
            SET password='$password'
            WHERE id = $user_id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    case 'add_address':

        break;
    case 'edit_address':

        break;
    case 'delete_address':

        break;
    default:
        # code...
        break;
}

$conn->close();

function addFieldToUpdate(&$updateFields, $request, $fieldName) {
    if (isset($request[$fieldName])) {
        $fieldValue = $request[$fieldName];
        $updateFields[] = "$fieldName='$fieldValue'";
    }
}

exit();
