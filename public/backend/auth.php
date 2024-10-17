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

// Регистрация нового пользователя
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['name']) && !empty($request['email']) && !empty($request['password'])) {
        $name = $conn->real_escape_string($request['name']);
        $email = $conn->real_escape_string($request['email']);
        $password = password_hash($request['password'], PASSWORD_DEFAULT); // Хэширование пароля

        // Проверка, существует ли пользователь с таким email
        $sql = "SELECT id FROM clients WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
        } else {
            // Генерация уникального токена
            $auth_token = bin2hex(random_bytes(32));

            // Вставка нового пользователя в базу данных с токеном
            $sql = "INSERT INTO clients (name, email, password, auth_token) VALUES ('$name', '$email', '$password', '$auth_token')";
            if ($conn->query($sql) === TRUE) {
                echo json_encode([
                    "status" => "success",
                    "auth_token" => $auth_token,
                    "message" => "User registered successfully."
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
    }

    // Авторизация пользователя
} elseif ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['email']) && !empty($request['password'])) {
        $email = $conn->real_escape_string($request['email']);
        $password = $request['password'];

        // Проверяем наличие пользователя
        $sql = "SELECT id, password, name, auth_token FROM clients WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Сохраняем данные пользователя в сессии
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['auth_token'] = $user['auth_token'];

                // Отправляем токен клиенту
                echo json_encode([
                    "status" => "success",
                    "auth_token" => $user['auth_token'],
                    "user" => [
                        "id" => $user['id'],
                        "name" => $user['name'],
                        "email" => $email
                    ]
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing email or password."]);
    }
    // Выход из системы
} elseif ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Уничтожаем сессию
    session_unset();
    session_destroy();

    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);

    // Получение данных пользователя по токену
} elseif ($action === 'get_user' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Получение токена из заголовка
    $headers = apache_request_headers();
    $auth_token = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    if (!$auth_token) {
        echo json_encode(["status" => "error", "message" => "No token provided."]);
        exit;
    }

    // Проверка сессии
    if (!isset($_SESSION['user_id']) || $_SESSION['auth_token'] !== $auth_token) {
        echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
        exit;
    }

    // Получение данных пользователя
    $sql = "SELECT * FROM clients WHERE auth_token = '$auth_token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Удаляем поля 'login' и 'password' из массива
        unset($user['login'], $user['password']);

        // Возвращаем оставшиеся данные пользователя
        echo json_encode([
            "status" => "success",
            "user" => $user
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid token."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action or request method."]);
}

$conn->close();
