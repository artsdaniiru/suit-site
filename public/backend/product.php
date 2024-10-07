<?php
// Включаем файл конфигурации
require_once 'config.php';

// Установка заголовков для CORS
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // cache for 1 day
}

// Обработка preflight-запроса
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
    }
    exit(0);
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

// Получение параметров из GET-запроса
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '1';

$data = [];

// SQL-запрос
$sql = "SELECT * FROM products
        WHERE id=$product_id";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $conn->error]);
    exit;
}

// Сохранение результатов в массив
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data['product'] = $row;
    }
}

$sql = "SELECT * FROM items
        WHERE product_id=$product_id";

$result = $conn->query($sql);

$sizes = [];

// Сохранение результатов в массив
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sizes[] = $row;
    }
}
$data['sizes'] = $sizes;


echo json_encode(['status' => 'success', 'data' => $data]);

$conn->close();
exit();
