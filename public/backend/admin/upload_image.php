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
$uploadDir = __DIR__ . '/../../images/';
// $uploadDir = __DIR__ . '/images/';
$logFile = __DIR__ . '/upload_log.txt';


// Функция логирования
function logMessage($message)
{
    global $logFile;
    // file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}


// Логируем начало работы скрипта
logMessage("Начало работы скрипта");


// Проверка наличия папки для загрузок и прав на запись
if (!is_dir($uploadDir)) {
    logMessage("Ошибка: Папка для загрузок не существует - $uploadDir");
} elseif (!is_writable($uploadDir)) {
    logMessage("Ошибка: Папка для загрузок не доступна для записи - $uploadDir");
}

$response = [
    'status' => 'error',
    'message' => '',
    'uploadedImages' => []
];

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';



// Проверка, что запрос содержит файлы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    logMessage("Файлы обнаружены в запросе");

    // Проверка на множественные файлы
    $files = is_array($_FILES['image']['name']) ? $_FILES['image'] : [$_FILES['image']];

    // Массив допустимых форматов изображений
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    logMessage("Допустимые форматы файлов: " . implode(", ", $allowedExtensions));

    // Обработка каждого файла
    foreach ($files as $index => $file) {
        $name = is_array($file) ? $file['name'] : $file['name'];
        $tmpName = is_array($file) ? $file['tmp_name'] : $file['tmp_name'];
        $fileType = pathinfo($name, PATHINFO_EXTENSION);

        logMessage("Обработка файла: $name, временное имя: $tmpName, тип файла: $fileType");

        // Проверка на допустимое расширение
        if (!in_array(strtolower($fileType), $allowedExtensions)) {
            $message = "Недопустимый формат файла: $name";
            $response['message'] = $message;
            logMessage($message);
            continue;
        }

        // Уникальное имя для файла
        $uniqueName = uniqid() . '.' . $fileType;
        $targetPath = $uploadDir . $uniqueName;

        // Перемещение файла в папку /images
        if (move_uploaded_file($tmpName, $targetPath)) {

            $image_path = '/images/' . $uniqueName;

            $sql = "INSERT INTO product_images (product_id, image_path) 
                            VALUES ('$product_id', '" . $conn->real_escape_string($image_path) . "')";
            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка добавления изображения: ' . $conn->error]);
                exit;
            }
            // Получаем ID новой записи
            $newImageId = $conn->insert_id;

            $response['uploadedImages'][] = [
                'id' => $newImageId,
                'image_path' => $image_path,
                'date_of_creation' => date("Y-m-d H:i:s"),
                'date_of_change' => date("Y-m-d H:i:s"),
                'new' => true,
            ];
            logMessage("Файл успешно загружен: $targetPath");
        } else {
            $message = "Ошибка загрузки файла: $name";
            $response['message'] = $message;
            logMessage($message);
        }
    }

    if (!empty($response['uploadedImages'])) {
        $response['status'] = 'success';
        $response['message'] = 'Файлы успешно загружены';
        logMessage("Все файлы успешно загружены");
    } else {
        $response['message'] = 'Загрузка файлов не удалась';
        logMessage("Загрузка файлов не удалась");
    }
} else {
    $response['message'] = 'Файлы не найдены в запросе';
    logMessage("Ошибка: Файлы не найдены в запросе");
}

// Установка заголовка JSON и вывод ответа
header('Content-Type: application/json');
echo json_encode($response);

// Логируем завершение работы скрипта
logMessage("Завершение работы скрипта");
