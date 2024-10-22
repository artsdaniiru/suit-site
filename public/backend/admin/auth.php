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

// Получаем действие через GET-параметр 'action'
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Обработка данных, полученных от клиента
$request = json_decode(file_get_contents('php://input'), true);

// Авторизация администратора
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['email']) && !empty($request['password'])) {
        $email = $conn->real_escape_string($request['email']);
        $password = $request['password'];

        // Проверяем наличие администратора
        $sql = "SELECT id, password, name, auth_token FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Сохраняем данные администратора в сессии
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_auth_token'] = $user['auth_token'];

                // Отправляем токен клиенту
                echo json_encode([
                    "status" => "success",
                    "auth_token" => $user['auth_token'],
                    "user" => [
                        "id" => $user['id'],
                        "name" => $user['name']
                    ]
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Admin not found or not authorized."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing email or password."]);
    }

    // Выход администратора
} elseif ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Уничтожаем сессию администратора
    session_unset();
    session_destroy();

    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);

    // Получение данных администратора по токену
} elseif ($action === 'get_user' && $_SERVER['REQUEST_METHOD'] === 'GET') {

    // Проверка сессии
    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_auth_token'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Unauthorized access."
        ]);
        exit;
    }

    // Возвращаем данные администратора
    echo json_encode([
        "status" => "success",
        "user" => [
            "id" => $_SESSION['admin_id'],
            "name" => $_SESSION['admin_name']
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action or request method."]);
}

$conn->close();
