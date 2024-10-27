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


// Установка директории для загрузки изображений
$uploadDir = __DIR__ . '/uploads/';
$response = [
    'status' => 'error',
    'message' => '',
    'uploadedImages' => []
];

// Проверка, что запрос содержит файлы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    // Проверка на множественные файлы
    $files = is_array($_FILES['image']['name']) ? $_FILES['image'] : [$_FILES['image']];

    // Массив допустимых форматов изображений
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Обработка каждого файла
    foreach ($files['name'] as $index => $name) {
        $tmpName = $files['tmp_name'][$index];
        $fileType = pathinfo($name, PATHINFO_EXTENSION);

        // Проверка на допустимое расширение
        if (!in_array(strtolower($fileType), $allowedExtensions)) {
            $response['message'] = 'Недопустимый формат файла: ' . $name;
            continue;
        }

        // Уникальное имя для файла
        $uniqueName = uniqid() . '.' . $fileType;
        $targetPath = $uploadDir . $uniqueName;

        // Перемещение файла в папку /uploads/
        if (move_uploaded_file($tmpName, $targetPath)) {
            $response['uploadedImages'][] = [
                'name' => $uniqueName,
                'path' => '/uploads/' . $uniqueName,
            ];
        } else {
            $response['message'] = 'Ошибка загрузки файла: ' . $name;
        }
    }

    if (!empty($response['uploadedImages'])) {
        $response['status'] = 'success';
        $response['message'] = 'Файлы успешно загружены';
    } else {
        $response['message'] = 'Загрузка файлов не удалась';
    }
} else {
    $response['message'] = 'Файлы не найдены в запросе';
}

// Установка заголовка JSON и вывод ответа
header('Content-Type: application/json');
echo json_encode($response);
