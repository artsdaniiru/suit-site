<?php
// Include the configuration file
require_once 'config.php';

// Start the session
session_start();

// Allow access from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Connect to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
        } else {
            // Generate a unique token
            $auth_token = bin2hex(random_bytes(32));

            // Insert the new user into the database with the token
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
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing email or password."]);
    }

    // Log out the user
} elseif ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Destroy the session
    session_unset();
    session_destroy();

    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);

    // Get user data by token
} elseif ($action === 'get_user' && $_SERVER['REQUEST_METHOD'] === 'GET') {

    // Check the session
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
        echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
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
        $sql_payment_methods = "SELECT * FROM client_payment_methods WHERE client_id = " . $user['id'] . " AND active = 1";
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
        echo json_encode(["status" => "error", "message" => "Invalid token."]);
    }
} elseif ($action === 'update_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $cart = json_encode($request['cart']);

    // Check the session
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['auth_token'])) {
        echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
        exit;
    }
    $auth_token = $_SESSION['auth_token'];

    $sql = "UPDATE clients SET cart = '$cart' WHERE auth_token = '$auth_token'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Cart has been updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating record: " . $conn->error]);
    }

    // Invalid action or request method
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
                    echo json_encode(['status' => 'error', 'message' => "Can't send email"]);
                } else {

                    echo json_encode(["status" => "success", "message" => "Pass reset request sent"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Error : " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing email or password."]);
    }

    // Log out the user
} elseif ($action === 'aprove_pass_reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
                echo json_encode(['status' => 'error', 'message' => "Successfully changed password"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error : " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid request data."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing request data."]);
    }

    // Log out the user
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action or request method."]);
}

$conn->close();
