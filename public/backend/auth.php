<?php
// Initialize the script
require_once 'starter.php';

// Start the session
session_start();

// Get the action from the GET parameter 'action'
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle data received from the client
$request = json_decode(file_get_contents('php://input'), true);

// Register a new user
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['name']) && !empty($request['email']) && !empty($request['password'])) {
        $name = $conn->real_escape_string($request['name']);
        $email = $conn->real_escape_string($request['email']);
        $password = password_hash($request['password'], PASSWORD_DEFAULT); // Hash the password

        // Check if a user with the same email already exists
        $sql = "SELECT id FROM clients WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "電子メールはすでに存在します。"]);
        } else {
            // Generate a unique token
            $auth_token = bin2hex(random_bytes(32));

            // Insert the new user into the database with the token
            $sql = "INSERT INTO clients (name, email, password, auth_token) VALUES ('$name', '$email', '$password', '$auth_token')";
            if ($conn->query($sql) === TRUE) {
                echo json_encode([
                    "status" => "success",
                    "auth_token" => $auth_token,
                    "message" => "ユーザーが正常に登録されました。"
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "すべてのフィールドは必須です。"]);
    }

    // Authenticate a user
} elseif ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['email']) && !empty($request['password'])) {
        $email = $conn->real_escape_string($request['email']);
        $password = $request['password'];

        // Check if the user exists
        $sql = "SELECT id, password, name, auth_token FROM clients WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Save user data in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['auth_token'] = $user['auth_token'];

                // Send the token to the client
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
                echo json_encode(["status" => "error", "message" => "メールアドレスまたはパスワードが無効です。"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "メールアドレスまたはパスワードが無効です。"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "メールアドレスまたはパスワードが見つかりません。"]);
    }

    // Log out the user
} elseif ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Destroy the session
    session_unset();
    session_destroy();

    echo json_encode(["status" => "success", "message" => "ログアウトに成功しました。"]);

    // Get user data by token
} elseif ($action === 'get_user' && $_SERVER['REQUEST_METHOD'] === 'GET') {

    // Check the session
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
        echo json_encode(["status" => "error", "message" => "不正アクセス。"]);
        exit;
    }

    $auth_token = $_SESSION['auth_token'];

    // SQL query to get user data
    $sql = "SELECT * FROM clients WHERE auth_token = '$auth_token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Remove sensitive data
        unset($user['login'], $user['password']);

        $user['height'] = intval($user['height']);
        $user['shoulder_width'] = intval($user['shoulder_width']);
        $user['waist_size'] = intval($user['waist_size']);

        // Get user addresses
        $sql_addresses = "SELECT * FROM client_addresses WHERE client_id = " . $user['id'] . " AND active = 1";
        $result_addresses = $conn->query($sql_addresses);
        $user_addresses = [];

        if ($result_addresses->num_rows > 0) {
            while ($row = $result_addresses->fetch_assoc()) {
                $user_addresses[] = $row;
            }
        }
        $user['addresses'] = $user_addresses;

        // Get user payment methods
        $sql_payment_methods = "SELECT * FROM client_payment_methods WHERE client_id = " . $user['id'] . " AND active = 1 AND card_number != 'CASH' AND card_number !='KONBINI'";
        $result_payment_methods = $conn->query($sql_payment_methods);
        $user_payment_methods = [];

        if ($result_payment_methods->num_rows > 0) {
            while ($row = $result_payment_methods->fetch_assoc()) {
                $user_payment_methods[] = $row;
            }
        }
        $user['payment_methods'] = $user_payment_methods;

        // Get user orders
        $sql_orders = "SELECT co.id, co.status, ca.name AS client_name, ca.address AS client_address, 
                       cpm.card_number
                       FROM client_orders co
                       JOIN client_addresses ca ON co.address_id = ca.id
                       JOIN client_payment_methods cpm ON co.payment_method_id = cpm.id
                       WHERE co.client_id = " . $user['id'];
        $result_orders = $conn->query($sql_orders);
        $user_orders = [];

        if ($result_orders->num_rows > 0) {
            while ($order = $result_orders->fetch_assoc()) {
                $order_id = $order['id'];

                // Get items in the order
                $sql_cart = "SELECT coi.price AS order_price, coi.options AS order_options, 
                             p.*, s.*
                             FROM client_order_indexes coi
                             JOIN products p ON coi.product_id = p.id
                             JOIN sizes s ON coi.size_id = s.id
                             WHERE client_order_id = $order_id";
                $result_cart = $conn->query($sql_cart);
                $cart_items = [];

                if ($result_cart->num_rows > 0) {
                    while ($item = $result_cart->fetch_assoc()) {
                        $cart_items[] = $item;
                    }
                }
                $order['cart'] = $cart_items;
                $user_orders[] = $order;
            }
        }
        $user['orders'] = $user_orders;

        // Return all data
        echo json_encode([
            "status" => "success",
            "user" => $user
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "無効なトークンです。"]);
    }
} elseif ($action === 'update_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $cart = json_encode($request['cart']);

    // Check the session
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
        echo json_encode(["status" => "error", "message" => "認証されていないアクセスです。"]);
        exit;
    }
    $auth_token = $_SESSION['auth_token'];

    $sql = "UPDATE clients SET cart = '$cart' WHERE auth_token = '$auth_token'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "カートが更新されました。"]);
    } else {
        echo json_encode(["status" => "error", "message" => "レコードの更新中にエラーが発生しました: " . $conn->error]);
    }

    // Password reset
} elseif ($action === 'pass_reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['email'])) {
        $email = $conn->real_escape_string($request['email']);

        // Check if the user exists
        $sql = "SELECT id, password, name, auth_token FROM clients WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $pass_reset_url = bin2hex(random_bytes(32));

            $sql = "UPDATE clients SET pass_reset_url = '$pass_reset_url' WHERE id = '$user_id'";
            if ($conn->query($sql) === TRUE) {



                $to = $email;
                $headers = "From: mailer@arts-suit.com\r\n";
                $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

                $message = "パスワードリセットリンク: https://arts-suit.com/pass-reset/" . $pass_reset_url;


                if (!mail($to, "パスワードリセットリンク",  $message, $headers)) {
                    echo json_encode(['status' => 'error', 'message' => "メールを送信できません"]);
                } else {

                    echo json_encode(["status" => "success", "message" => "パスワードリセットリクエストが送信されました"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "エラー: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "メールアドレスまたはパスワードが無効です。"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "メールアドレスが見つかりません。"]);
    }

    // Check password reset link validity
} elseif ($action === 'check_pass_reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['pass_reset_url']) && $request['pass_reset_url'] != "") {
        $pass_reset_url = $conn->real_escape_string($request['pass_reset_url']);


        // Check if the user exists
        $sql = "SELECT id, password, name, auth_token FROM clients WHERE pass_reset_url = '$pass_reset_url'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => "このリンクは有効です"]);
        } else {
            echo json_encode(["status" => "error", "message" => "無効なリクエストデータです。"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "リクエストデータが不足しています。"]);
    }

    // Approve password reset
} elseif ($action === 'approve_pass_reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($request['pass_reset_url']) && !empty($request['password']) && $request['pass_reset_url'] != "") {
        $pass_reset_url = $conn->real_escape_string($request['pass_reset_url']);
        $password = $conn->real_escape_string($request['password']);

        // Check if the user exists
        $sql = "SELECT id, password, name, auth_token FROM clients WHERE pass_reset_url = '$pass_reset_url'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $password = password_hash($request['password'], PASSWORD_DEFAULT); // Hash the password

            $sql = "UPDATE clients SET password = '$password', pass_reset_url = '' WHERE id = '$user_id'";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success', 'message' => "パスワードが正常に変更されました。"]);
            } else {
                echo json_encode(["status" => "error", "message" => "エラー: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "無効なリクエストデータです。"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "リクエストデータが不足しています。"]);
    }

    // Invalid action or request method
} else {
    echo json_encode(["status" => "error", "message" => "無効なアクションまたはリクエストメソッドです。"]);
}

$conn->close();
