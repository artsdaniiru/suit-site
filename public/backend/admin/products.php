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

// Обработка запросов
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request = json_decode(file_get_contents('php://input'), true);

// Получение параметров из GET-запроса
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest'; // Тип сортировки по умолчанию - самые новые
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Число продуктов на одной странице
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Текущая страница
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Строка поиска (по умолчанию пустая)
$popular = isset($_GET['popular']) && $_GET['popular'] == '1'; // Параметр фильтрации рекомендованных товаров
$productType = isset($_GET['productType']) ? $_GET['productType'] : ''; // Фильтр по типу продукта (suit или not_suit)

// Определение сортировки в зависимости от параметра $sort
switch ($sort) {
    case 'newest':
        $orderBy = 'p.date_of_creation DESC'; // Сначала новые товары
        break;
    case 'highest_price':
        $orderBy = 'min_price DESC'; // Товары с наибольшей минимальной ценой
        break;
    case 'lowest_price':
        $orderBy = 'min_price ASC'; // Товары с наименьшей минимальной ценой
        break;
    case 'recomended':
        $orderBy = 'popular DESC'; // Рекомендованные товары
        break;
    default:
        $orderBy = 'p.date_of_creation DESC'; // По умолчанию сортировка по дате (новые)
}

// Вычисление смещения для пагинации
$offset = ($page - 1) * $itemsPerPage;

// Подготовка условия для строки поиска
$searchCondition = '';
if (!empty($query)) {
    // Экранируем значение поиска для безопасности
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (p.name LIKE '%$query%' OR p.name_eng LIKE '%$query%' OR p.description LIKE '%$query%')";
}

// Подготовка условия для параметра "popular"
$popularCondition = '';
if ($popular) {
    $popularCondition .= "AND p.popular = 1"; // Фильтрация по популярности
}

// Подготовка условия для фильтра по типу продукта
$productTypeCondition = '';
if (!empty($productType)) {
    // Экранируем значение типа продукта для безопасности
    $productType = $conn->real_escape_string($productType);
    $productTypeCondition .= "AND p.type = '$productType'";
}

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';

switch ($action) {
    case 'list_all_products':
        // SQL-запрос с JOIN, MIN, сортировкой, условием поиска, популярными товарами, фильтром по типу продукта, LIMIT и OFFSET
        $sql = "SELECT p.*, MIN(i.price) as min_price, MAX(im.image_path) as image_path
        FROM products p
        JOIN sizes i ON p.id = i.product_id
        JOIN product_images im ON p.id = im.product_id
        WHERE 1=1 $searchCondition $popularCondition $productTypeCondition
        GROUP BY p.id
        ORDER BY $orderBy
        LIMIT $offset, $itemsPerPage";

        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $conn->error]);
            exit;
        }
        $products = [];

        // Сохранение результатов в массив
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $products[] = $row;
            }
        }

        // Получение общего количества записей для вычисления количества страниц (с учетом фильтров поиска и рекомендаций)
        $totalCountResult = $conn->query("SELECT COUNT(DISTINCT p.id) as count 
                          FROM products p 
                          JOIN sizes i ON p.id = i.product_id
                          WHERE 1=1 $searchCondition $popularCondition $productTypeCondition");

        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка получения количества товаров: ' . $conn->error]);
            exit;
        }
        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = $totalCountRow['count'];
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Вывод результата (ответ с информацией о продуктах и пагинации) в формате JSON
        echo json_encode([
            'status' => 'success',
            'products' => $products,
            'pagination' => [
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems
            ]
        ]);
        break;
    case 'add_product':
        $type = $request['type'];
        $name = $request['name'];
        $name_eng = $request['name_eng'];
        $description = $request['description'];
        $active =  isset($request['acive']) ? $request['acive'] : '';
        $popular = isset($request['popular']) ? $request['popular'] : '';


        $sql = "INSERT INTO products (type, name, name_eng, description, active, popular) 
                VALUES ('$type', '$name', '$name_eng', '$description', '$active', '$popular')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }

        break;
    case 'add_product_images':
        //TODO: реализовать логику добавление картинки
        $image_path = $request['image_path'];

        $sql = "INSERT INTO product_images (product_id, image_path)
                VALUES ('$product_id', '$image_path')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    case 'add_sizes':
        $name = $request['name'];
        $price = $request['price'];
        $height_min = $request['height_min'];
        $height_max = $request['height_max'];
        $shoulder_width_min = $request['shoulder_width_min'];
        $shoulder_width_max = $request['shoulder_width_max'];
        $waist_size_min = $request['waist_size_min'];
        $waist_size_max = $request['waist_size_max'];
        $stock = $request['stock'];


        $sql = "INSERT INTO sizes (product_id, name, price, height_min, height_max, shoulder_width_min, shoulder_width_max, 
                            waist_size_min, waist_size_max, stock)                   
                VALUES ('$product_id', '$name', '$price','$height_min','$height_max','$shoulder_width_min','$shoulder_width_max',
                        '$waist_size_min','$waist_size_max','$stock')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    case 'add_options':
        $name = $request['name'];
        $type = $request['type'];
        $price = $request['price'];
        $stock = $request['stock'];

        $sql = "INSERT INTO options (name, type, price, stock)
        VALUES ('$name', '$type', '$price', '$stock')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }

        break;
    case 'link_option':
        $option_id = isset($_GET['option_id']) ? $_GET['option_id'] : '';

        $sql = "INSERT INTO options_indexes (product_id, option_id)
            VALUES ('$product_id', '$option_id')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }

        break;
    case 'delete_product':
        // Начинаем транзакцию
        $conn->begin_transaction();

        try {
            // Удаляем продукт
            $conn->query("DELETE FROM products WHERE id=$product_id");

            // Удаляем связанные изображения
            $conn->query("DELETE FROM product_images WHERE product_id = $product_id");

            // Удаляем связанные размеры
            $conn->query("DELETE FROM sizes WHERE product_id = $product_id");

            // Удаляем связанные опции
            $conn->query("DELETE FROM options_indexes WHERE product_id = $product_id");

            // TODO: добавить удаление файла изображения

            // Если все запросы прошли успешно, фиксируем транзакцию
            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            // В случае ошибки откатываем транзакцию
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        break;
    case 'deactive_product':
        $sql = "UPDATE products
        SET active = 0
        WHERE id = $product_id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;
    case 'active_product':
        $sql = "UPDATE products
            SET active = 1
            WHERE id = $product_id";
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
exit();
