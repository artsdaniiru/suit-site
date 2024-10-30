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
    case 'list_all_options':
        // Получение параметров из GET-запроса
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'; // Тип сортировки по умолчанию - по имени
        $itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 20; // Число опций на одной странице
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Текущая страница
        $query = isset($_GET['query']) ? $_GET['query'] : ''; // Строка поиска (по умолчанию пустая)

        // Определение сортировки в зависимости от параметра $sort
        switch ($sort) {
            case 'name_asc':
                $orderBy = 'o.name ASC'; // Сортировка по имени по возрастанию
                break;
            case 'name_desc':
                $orderBy = 'o.name DESC'; // Сортировка по имени по убыванию
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
                    WHERE 1=1 $searchCondition
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
                                              WHERE 1=1 $searchCondition");

        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка получения количества опций: ' . $conn->error]);
            exit;
        }

        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = $totalCountRow['count'];
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
    case 'delete_options':
        // Получение ID опции из запроса
        $option_id = isset($_GET['option_id']) ? (int)$_GET['option_id'] : 0;

        // Проверка, что ID опции передан
        if ($option_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID опции']);
            exit;
        }

        // Удаление связей опции с продуктами в таблице options_indexes
        $sqlDeleteIndexes = "DELETE FROM options_indexes WHERE option_id = $option_id";
        if ($conn->query($sqlDeleteIndexes) === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка удаления связей с продуктами: ' . $conn->error]);
            exit;
        }

        // Удаление самой опции из таблицы options
        $sqlDeleteOption = "DELETE FROM options WHERE id = $option_id";
        if ($conn->query($sqlDeleteOption) === TRUE) {
            echo json_encode(['status' => 'success', 'message' => 'Опция успешно удалена']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка удаления опции: ' . $conn->error]);
        }
        break;
    case 'edit_product':
        // Получение данных из запроса
        $originalData = $request['data_original'];
        $updatedData = $request['data'];

        $productId = $originalData['product']['id'];

        // Обновление основного продукта
        if ($originalData['product'] !== $updatedData['product']) {
            $sql = "UPDATE products SET
                    type = '" . $conn->real_escape_string($updatedData['product']['type']) . "',
                    name = '" . $conn->real_escape_string($updatedData['product']['name']) . "',
                    name_eng = '" . $conn->real_escape_string($updatedData['product']['name_eng']) . "',
                    description = '" . $conn->real_escape_string($updatedData['product']['description']) . "',
                    active = '" . $conn->real_escape_string($updatedData['product']['active']) . "',
                    popular = '" . $conn->real_escape_string($updatedData['product']['popular']) . "',
                    date_of_change = '" . date("Y-m-d H:i:s") . "'
                    WHERE id = '$productId'";
            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка обновления продукта: ' . $conn->error]);
                exit;
            }
        }

        // Обработка размеров
        $originalSizes = array_column($originalData['sizes'], null, 'id'); // Используем ID как ключи
        $updatedSizes = array_column($updatedData['sizes'], null, 'id');

        // Удаление размеров, которых нет в обновленных данных
        foreach ($originalSizes as $id => $originalSize) {
            if (!isset($updatedSizes[$id])) {
                $sql = "DELETE FROM sizes WHERE id = '$id'";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка удаления размеров: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Обновление или добавление размеров
        foreach ($updatedSizes as $id => $updatedSize) {
            if (isset($originalSizes[$id])) {
                // Если размер существует, обновляем его
                if ($originalSizes[$id] != $updatedSize) {
                    $sql = "UPDATE sizes SET
                            name = '" . $conn->real_escape_string($updatedSize['name']) . "',
                            price = '" . $conn->real_escape_string($updatedSize['price']) . "',
                            height_min = '" . $conn->real_escape_string($updatedSize['height_min']) . "',
                            height_max = '" . $conn->real_escape_string($updatedSize['height_max']) . "',
                            shoulder_width_min = '" . $conn->real_escape_string($updatedSize['shoulder_width_min']) . "',
                            shoulder_width_max = '" . $conn->real_escape_string($updatedSize['shoulder_width_max']) . "',
                            waist_size_min = '" . $conn->real_escape_string($updatedSize['waist_size_min']) . "',
                            waist_size_max = '" . $conn->real_escape_string($updatedSize['waist_size_max']) . "',
                            stock = '" . $conn->real_escape_string($updatedSize['stock']) . "'
                            date_of_change = '" . date("Y-m-d H:i:s") . "'
                            WHERE id = '$id'";
                    if ($conn->query($sql) === FALSE) {
                        echo json_encode(['status' => 'error', 'message' => 'Ошибка обновления размеров: ' . $conn->error]);
                        exit;
                    }
                }
            } else {
                // Если размер новый, добавляем его
                $sql = "INSERT INTO sizes (product_id, name, price, height_min, height_max, shoulder_width_min, shoulder_width_max, waist_size_min, waist_size_max, stock)
                            VALUES ('$productId', '" . $conn->real_escape_string($updatedSize['name']) . "', '" . $conn->real_escape_string($updatedSize['price']) . "', '" . $conn->real_escape_string($updatedSize['height_min']) . "', '" . $conn->real_escape_string($updatedSize['height_max']) . "', '" . $conn->real_escape_string($updatedSize['shoulder_width_min']) . "', '" . $conn->real_escape_string($updatedSize['shoulder_width_max']) . "', '" . $conn->real_escape_string($updatedSize['waist_size_min']) . "', '" . $conn->real_escape_string($updatedSize['waist_size_max']) . "', '" . $conn->real_escape_string($updatedSize['stock']) . "')";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка добавления нового размера: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Обработка изображений (аналогично размерам)
        $originalImages = array_column($originalData['product_images'], null, 'id');
        $updatedImages = array_column($updatedData['product_images'], null, 'id');

        // Удаление изображений
        foreach ($originalImages as $id => $originalImage) {
            if (!isset($updatedImages[$id])) {
                $sql = "DELETE FROM product_images WHERE id = '$id'";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка удаления изображения: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Обновление или добавление изображений
        foreach ($updatedImages as $id => $updatedImage) {
            if (isset($originalImages[$id])) {
                // Если изображение существует, обновляем его
                if ($originalImages[$id] != $updatedImage) {
                    $sql = "UPDATE product_images SET
                            image_path = '" . $conn->real_escape_string($updatedImage['image_path']) . "',
                            date_of_change = '" . date("Y-m-d H:i:s") . "'
                            WHERE id = '$id'";
                    if ($conn->query($sql) === FALSE) {
                        echo json_encode(['status' => 'error', 'message' => 'Ошибка обновления изображения: ' . $conn->error]);
                        exit;
                    }
                }
            } else {
                // Если изображение новое,  добавляем его
                $sql = "INSERT INTO product_images (product_id, image_path)
                            VALUES ('$productId', '" . $conn->real_escape_string($updatedImage['image_path']) . "')";
                if ($conn->query($sql) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка добавления изображения: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Работа с опциями и связями в таблице options_indexes
        $originalOptionIds = array_column($originalData['options'], 'id');
        $updatedOptionIds = array_column($updatedData['options'], 'id');

        // Удаление старых связей (опций, которых нет в обновленных данных)
        $optionsToRemove = array_diff($originalOptionIds, $updatedOptionIds);
        if (!empty($optionsToRemove)) {
            $optionsToRemoveStr = implode(',', array_map('intval', $optionsToRemove));
            $sql = "DELETE FROM options_indexes WHERE product_id = '$productId' AND option_id IN ($optionsToRemoveStr)";
            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка удаления связей с опциями: ' . $conn->error]);
                exit;
            }
        }

        // Добавление новых связей (опций, которых нет в оригинальных данных) - добавление в таблицу options_indexes
        $optionsToAdd = array_diff($updatedOptionIds, $originalOptionIds);
        foreach ($optionsToAdd as $optionId) {
            $sql = "INSERT INTO options_indexes (product_id, option_id)
                VALUES ('$productId', '" . intval($optionId) . "')";
            if ($conn->query($sql) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка добавления связей с опциями: ' . $conn->error]);
                exit;
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Продукт успешно обновлен']);
        break;
    case 'delete_product':
        // Получение ID продукта из запроса
        $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

        // Проверка, что ID продукта передан
        if ($product_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID продукта']);
            exit;
        }

        // Начало транзакции
        $conn->begin_transaction();

        try {
            // Удаление изображений продукта
            $sqlDeleteImages = "DELETE FROM product_images WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteImages) === FALSE) {
                throw new Exception('Ошибка удаления изображений продукта: ' . $conn->error);
            }

            // Удаление размеров продукта
            $sqlDeleteSizes = "DELETE FROM sizes WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteSizes) === FALSE) {
                throw new Exception('Ошибка удаления размеров продукта: ' . $conn->error);
            }

            // Удаление связей продукта с опциями в таблице options_indexes
            $sqlDeleteOptionIndexes = "DELETE FROM options_indexes WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteOptionIndexes) === FALSE) {
                throw new Exception('Ошибка удаления связей с опциями: ' . $conn->error);
            }

            // Удаление записей из client_order_indexes, связанных с продуктом
            $sqlDeleteClientOrders = "DELETE FROM client_order_indexes WHERE product_id = $product_id";
            if ($conn->query($sqlDeleteClientOrders) === FALSE) {
                throw new Exception('Ошибка удаления заказов, связанных с продуктом: ' . $conn->error);
            }

            // Удаление самого продукта
            $sqlDeleteProduct = "DELETE FROM products WHERE id = $product_id";
            if ($conn->query($sqlDeleteProduct) === FALSE) {
                throw new Exception('Ошибка удаления продукта: ' . $conn->error);
            }

            // Если все прошло успешно, подтверждаем транзакцию
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Продукт и все связанные данные успешно удалены']);
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
