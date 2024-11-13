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
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'; // Тип сортировки по умолчанию - по имени
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 20; // Число опций на одной странице
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Текущая страница
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Строка поиска (по умолчанию пустая)
$type = isset($_GET['type']) ? "AND type='" . $_GET['type'] . "'" : '';

// Определение сортировки в зависимости от параметра $sort
switch ($sort) {
    case 'name_asc':
        $orderBy = 'o.name ASC'; // Сортировка по имени по возрастанию
        break;
    case 'name_desc':
        $orderBy = 'o.name DESC'; // Сортировка по имени по убыванию
        break;
    case 'type_asc':
        $orderBy = 'o.type ASC'; // Сортировка по имени по возрастанию
        break;
    case 'type_desc':
        $orderBy = 'o.type DESC'; // Сортировка по имени по убыванию
        break;
    case 'price_high':
        $orderBy = 'o.price DESC'; // Сортировка по цене по убыванию
        break;
    case 'price_low':
        $orderBy = 'o.price ASC'; // Сортировка по цене по возрастанию
        break;
    default:
        $orderBy = 'o.name ASC'; // По умолчанию сортировка по имени по возрастанию
}

// Вычисление смещения для пагинации
$offset = ($page - 1) * $itemsPerPage;

// Подготовка условия для строки поиска
$searchCondition = '';
if (!empty($query)) {
    // Экранируем значение поиска для безопасности
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (o.name LIKE '%$query%' OR o.type LIKE '%$query%')";
}

$splitByType = isset($_GET['splitByType']) ? true : false;

$limit = "LIMIT $offset, $itemsPerPage";
if ($splitByType) {
    $limit = "";
}

// SQL-запрос для получения списка опций
$sql = "SELECT o.* 
                    FROM options o
                    WHERE 1=1 $searchCondition $type
                    ORDER BY $orderBy
                    $limit";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $conn->error]);
    exit;
}


$options = [];

// Сохранение результатов в массив
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($splitByType) {
            $options[$row['type']][] = $row;
        } else {
            $options[] = $row;
        }
    }
}

// Получение общего количества записей для вычисления количества страниц
$totalCountResult = $conn->query("SELECT COUNT(*) as count 
                                              FROM options o 
                                              WHERE 1=1 $searchCondition $type");

if (!$totalCountResult) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка получения количества опций: ' . $conn->error]);
    exit;
}

$totalCountRow = $totalCountResult->fetch_assoc();
$totalItems = intval($totalCountRow['count']);
$totalPages = ceil($totalItems / $itemsPerPage);

// Вывод результата (ответ с информацией об опциях и пагинации) в формате JSON
echo json_encode([
    'status' => 'success',
    'options' => $options,
    'pagination' => [
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems
    ]
]);

$conn->close();
exit();
