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

// Проверка сессии (если требуется)
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
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id'; // Тип сортировки по умолчанию - по id
$itemsPerPage = isset($_GET['itemsPerPage']) ? max(1, (int)$_GET['itemsPerPage']) : 10; // Число клиентов на одной странице
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Текущая страница
$query = isset($_GET['query']) ? $_GET['query'] : ''; // Строка поиска (по умолчанию пустая)
$status = isset($_GET['status']) ? $_GET['status'] : ''; // Фильтр по статусу заказа

// Определение сортировки в зависимости от параметра $sort
switch ($sort) {
    case 'id_desc':
        $orderBy = 'co.id DESC'; // Сортировка по id desc
        break;
    case 'id_asc':
        $orderBy = 'co.id ASC'; //  Сортировка по id asc
        break;
    case 'name_desc':
        $orderBy = 'ca.name DESC'; // Сортировка по имени клиента desc
        break;
    case 'name_asc':
        $orderBy = 'ca.name ASC'; // Сортировка по имени клиента asc
        break;
    case 'status_desc':
        $orderBy = 'co.status DESC'; // Сортировка по status desc
        break;
    case 'status_asc':
        $orderBy = 'co.status ASC'; // Сортировка по status asc
        break;
    case 'email_desc':
        $orderBy = 'cl.email DESC'; // Сортировка по email desc
        break;
    case 'email_asc':
        $orderBy = 'cl.email ASC'; // Сортировка по email asc
        break;
    case 'phone_desc':
        $orderBy = 'ca.phone DESC'; // Сортировка по email desc
        break;
    case 'phone_asc':
        $orderBy = 'ca.phone ASC'; // Сортировка по email asc
        break;
    case 'date_desc':
        $orderBy = 'co.date_of_creation DESC'; // Сначала новые заказы
        break;
    case 'date_asc':
        $orderBy = 'co.date_of_creation ASC'; // Сначала старые заказы
        break;
    default:
        $orderBy = 'co.id DESC'; // По умолчанию сортировка по id
}

// Вычисление смещения для пагинации
$offset = ($page - 1) * $itemsPerPage;

// Подготовка условия для строки поиска
$searchCondition = '';
if (!empty($query)) {
    // Экранируем значение поиска для безопасности
    $query = $conn->real_escape_string($query);
    $searchCondition .= "AND (co.id LIKE '%$query%' OR ca.name LIKE '%$query%' OR co.status LIKE '%$query%' OR cl.email LIKE '%$query%')";
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

switch ($action) {

        // 1. Список всех заказов
    case 'list_all_orders':
        // Условия фильтрации (например, для поиска или сортировки заказов)
        $searchCondition = isset($searchCondition) ? $searchCondition : "";

        $filter = "";
        if ($status != "") {
            $filter .= " AND co.status = '$status'";
        }

        // Параметры сортировки, пагинации и лимитов
        $orderBy = isset($orderBy) ? $orderBy : "co.id DESC";
        $offset = isset($offset) ? $offset : 0;
        $itemsPerPage = isset($itemsPerPage) ? $itemsPerPage : 10;
        $page = isset($page) ? $page : 1;

        // Запрос с учетом условий, сортировки и пагинации
        $sql = "SELECT co.*, cl.name AS client_name, cl.email, cl.height, cl.shoulder_width, cl.waist_size,
                    ca.name AS address_name, ca.address, ca.phone,
                    cpm.card_number
                    FROM client_orders co
                    JOIN clients cl ON co.client_id = cl.id
                    JOIN client_addresses ca ON co.address_id = ca.id
                    JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                    WHERE 1=1 $searchCondition $filter
                    ORDER BY $orderBy
                    LIMIT $offset, $itemsPerPage";
        // print_r($sql);
        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка выполнения запроса: ' . $conn->error]);
            exit;
        }

        $orders = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }

        // Получение общего количества записей для пагинации
        $totalCountResult = $conn->query("SELECT COUNT(*) as count FROM client_orders co
                    JOIN clients cl ON co.client_id = cl.id
                    JOIN client_addresses ca ON co.address_id = ca.id
                    JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                    WHERE 1=1 $searchCondition $filter $searchCondition $filter");
        if (!$totalCountResult) {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка получения количества заказов: ' . $conn->error]);
            exit;
        }
        $totalCountRow = $totalCountResult->fetch_assoc();
        $totalItems = intval($totalCountRow['count']);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Вывод результата с информацией о заказах и пагинации
        echo json_encode([
            'status' => 'success',
            'orders' => $orders,
            'pagination' => [
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems
            ]
        ]);
        break;

        // 2. Удаление заказа и всех связанных данных
    case 'delete_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID заказа']);
            exit;
        }

        // Начало транзакции
        $conn->begin_transaction();

        try {
            // Удаление связи заказа и продуктов (client_order_indexes)
            $sqlDeleteOrderIndexes = "DELETE FROM client_order_indexes WHERE client_order_id = $order_id";
            if ($conn->query($sqlDeleteOrderIndexes) === FALSE) {
                throw new Exception('Ошибка удаления элементов связи заказа и продуктов: ' . $conn->error);
            }

            // Удаление самого заказа
            $sqlDeleteOrder = "DELETE FROM client_orders WHERE id = $order_id";
            if ($conn->query($sqlDeleteOrder) === FALSE) {
                throw new Exception('Ошибка удаления заказа: ' . $conn->error);
            }

            // Завершаем транзакцию
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Заказ успешно удалён']);
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

        // 3. Редактирование заказа
    case 'edit_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID заказа']);
            exit;
        }

        $dataOriginal = $request['data_original'];
        $dataUpdated = $request['data'];

        if (empty($dataOriginal) || empty($dataUpdated)) {
            echo json_encode(['status' => 'error', 'message' => 'Исходные или обновленные данные отсутствуют']);
            exit;
        }

        // Обновление данных заказа
        $fieldsToUpdate = [];
        foreach (['client_id', 'address_id', 'payment_method_id', 'status'] as $field) {
            if ($dataOriginal['order'][$field] != $dataUpdated['order'][$field]) {
                $fieldsToUpdate[] = "$field = '" . $conn->real_escape_string($dataUpdated['order'][$field]) . "'";
            }
        }
        if (!empty($fieldsToUpdate)) {
            $fieldsToUpdate[] = "date_of_change = '" . date("Y-m-d H:i:s") . "'";
            $sqlUpdateOrder = "UPDATE client_orders SET " . implode(', ', $fieldsToUpdate) . " WHERE id = $order_id";
            if ($conn->query($sqlUpdateOrder) === FALSE) {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка обновления заказа: ' . $conn->error]);
                exit;
            }
        }

        // Получение продуктов из оригинальных и обновленных данных
        $originalProducts = [];
        foreach ($dataOriginal['products'] as $product) {
            $originalProducts[$product['product']['id']] = $product;
        }

        $updatedProducts = [];
        foreach ($dataUpdated['products'] as $product) {
            $updatedProducts[$product['product']['id']] = $product;
        }

        // Удаление продуктов, отсутствующих в обновленных данных
        foreach ($originalProducts as $productId => $originalProduct) {
            if (!isset($updatedProducts[$productId])) {
                $sqlDelete = "DELETE FROM client_order_indexes WHERE client_order_id = $order_id AND product_id = $productId";
                if ($conn->query($sqlDelete) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка удаления продукта: ' . $conn->error]);
                    exit;
                }
            }
        }

        // Обновление и добавление продуктов
        foreach ($updatedProducts as $productId => $updatedProduct) {
            $price = $conn->real_escape_string($updatedProduct['product']['price']);
            $sizeId = $conn->real_escape_string($updatedProduct['product']['size_id']);

            // Фильтрация `order_options` для исключения нулей
            $optionsArray = array_filter(json_decode($updatedProduct['product']['order_options'], true), function ($value) {
                return $value != 0;
            });
            $optionsJson = $conn->real_escape_string(json_encode(array_values($optionsArray)));

            if (isset($originalProducts[$productId])) {
                // Если продукт существует, обновляем его
                $sqlUpdateProduct = "UPDATE client_order_indexes SET
                        price = '$price',
                        size_id = '$sizeId',
                        options = '$optionsJson'
                        WHERE client_order_id = $order_id AND product_id = $productId";
                if ($conn->query($sqlUpdateProduct) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка обновления продукта: ' . $conn->error]);
                    exit;
                }
            } else {
                // Если продукт новый, добавляем его
                $sqlInsertProduct = "INSERT INTO client_order_indexes (client_order_id, product_id, price, size_id, options)
                        VALUES ($order_id, $productId, '$price', '$sizeId', '$optionsJson')";
                if ($conn->query($sqlInsertProduct) === FALSE) {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка добавления нового продукта: ' . $conn->error]);
                    exit;
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Заказ успешно обновлен']);
        break;


    case 'get_order':
        if ($order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Некорректный ID заказа']);
            exit;
        }

        $data = [];

        // Получение информации о заказе, клиенте, адресе и методе оплаты
        $sqlOrder = "SELECT co.*, cl.name AS client_name, cl.email, cl.height, cl.shoulder_width, cl.waist_size,
                         ca.name AS address_name, ca.address, ca.phone, cpm.card_number
                         FROM client_orders co
                         JOIN clients cl ON co.client_id = cl.id
                         JOIN client_addresses ca ON co.address_id = ca.id
                         JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                         WHERE co.id = $order_id";
        $resultOrder = $conn->query($sqlOrder);

        if ($resultOrder->num_rows > 0) {
            $orderData = $resultOrder->fetch_assoc();
            $data['order'] = array_filter($orderData, function ($value) {
                return !is_null($value);
            });
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Заказ не найден']);
            exit;
        }

        // Получение информации о продуктах в заказе
        $sqlProducts = "SELECT coi.price AS price, coi.options AS order_options,  coi.size_id AS size_id,
                            p.*, COALESCE(MIN(im.image_path), NULL) AS image_path
                            FROM client_order_indexes coi
                            JOIN products p ON coi.product_id = p.id
                            LEFT JOIN product_images im ON p.id = im.product_id
                            WHERE coi.client_order_id = $order_id";
        $resultProducts = $conn->query($sqlProducts);
        $products = [];

        if ($resultProducts->num_rows > 0) {
            while ($row = $resultProducts->fetch_assoc()) {
                // Удаляем null поля из product
                $product_data['product'] = array_filter($row, function ($value) {
                    return !is_null($value);
                });
                $product_data['product']['price'] = intval($product_data['product']['price']);

                $s_id = $row['size_id'];
                $sqlSize = "SELECT s.* FROM sizes s WHERE s.id=$s_id";
                $resultSize = $conn->query($sqlSize);

                if ($resultSize->num_rows > 0) {
                    $sizeData = $resultSize->fetch_assoc();
                    $product_data['size'] = array_filter($sizeData, function ($value) {
                        return !is_null($value);
                    });
                    $product_data['size']['price'] = intval($product_data['size']['price']);
                }

                if ($row['order_options'] != null) {
                    $options = json_decode($row['order_options']);
                    $options_sql_ids = implode(',', $options);
                    $sqlOptions = "SELECT * FROM options WHERE id IN ($options_sql_ids)";
                    $resultOptions = $conn->query($sqlOptions);

                    if ($resultOptions->num_rows > 0) {
                        $product_data['options'] = [];
                        while ($row_o = $resultOptions->fetch_assoc()) {
                            $product_data['options'][] = array_filter($row_o, function ($value) {
                                return !is_null($value);
                            });
                        }
                    }
                }

                $products[] = $product_data;
            }
        }

        $data['products'] = $products;

        // Возвращаем результат
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;


    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();
exit();
