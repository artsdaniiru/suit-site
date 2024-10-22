<?php
// Включаем файл конфигурации
require_once 'config.php';

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

// Получение параметров из GET-запроса
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : false;

$data = [];

if ($product_id == false) {
    $data = 'Data not found (no product_id)';
    echo json_encode(['status' => 'fail', 'data' => $data]);
} else {
    // SQL-запрос (к products)
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

    // Получаем тип продукта
    $product_type = isset($data['product']['type']) ? $data['product']['type'] : '';

    // SQL-запрос (к sizes)
    $sql = "SELECT * FROM sizes
            WHERE product_id=$product_id";

    $result = $conn->query($sql);

    $sizes = [];

    // Удаляем null поля из массива
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Применяем array_filter к каждой строке размеров
            $sizes[] = array_filter($row, function ($value) {
                return !is_null($value);
            });
        }
    }

    // Сохранение результатов в массив
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sizes[] = $row;
        }
    }
    $data['sizes'] = $sizes;

    // SQL-запрос (к product_images)
    $sql = "SELECT * FROM product_images
            WHERE product_id=$product_id";
    $result = $conn->query($sql);

    $product_images = [];
    // Сохранение результатов в массив
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $product_images[] = $row;
        }
    }
    $data['product_images'] = $product_images;

    // SQL-запрос (к options через options_indexes) -  только если тип продукта "suit"
    if ($product_type === 'suit') {
        $sql = "SELECT o.* FROM `options` o JOIN options_indexes op 
        ON o.id=op.option_id WHERE op.product_id=$product_id;";
        $result = $conn->query($sql);

        $options = [];
        // Сохранение результатов в массив
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $options[] = $row;
            }
        }
        $data['options'] = $options;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
}


$conn->close();
exit();
