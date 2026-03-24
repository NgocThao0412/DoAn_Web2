<?php
session_name("user"); // nếu bạn dùng custom name
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../app/config/data_connect.php"; // Kết nối database

header("Content-Type: application/json");

// Kiểm tra kết nối database
if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection error."
    ]);
    exit;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login"
    ]);
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$role = $_SESSION['user']['role'];

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

switch ($action) {
    case "update":
        updateQuantity($conn, $user_id, $data);
        break;
    case "remove":
        removeItem($conn, $user_id, $data);
        break;
    case "add":
        addToCart($conn, $user_id, $data);
        break;
    default:
        if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["cart_count"])) {
            getCartCount($conn, $user_id);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid action."]);
        }
        break;
}

/* 🛒 Thêm sản phẩm vào giỏ hàng */
function addToCart($conn, $user_id, $data) {
    $product_id = intval($data['product_id'] ?? 0);

    if (!$product_id) {
        echo json_encode(["success" => false, "message" => "Missing product_id"]);
        exit;
    }

    // lấy cart
    $stmt_cart = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND status = 'active'");
    if (!$stmt_cart) {
        echo json_encode(["success" => false, "message" => $conn->error]);
        exit;
    }

    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $res = $stmt_cart->get_result();

    if ($res->num_rows == 0) {
        $stmt_new = $conn->prepare("INSERT INTO cart (user_id, status) VALUES (?, 'active')");
        $stmt_new->bind_param("i", $user_id);
        $stmt_new->execute();
        $cart_id = $conn->insert_id;
    } else {
        $cart_id = $res->fetch_assoc()['cart_id'];
    }

    // check sản phẩm
    $stmt = $conn->prepare("SELECT quantity FROM cart_detail WHERE cart_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cart_id, $product_id);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $stmt_update = $conn->prepare("UPDATE cart_detail SET quantity = quantity + 1 WHERE cart_id = ? AND product_id = ?");
        $stmt_update->bind_param("ii", $cart_id, $product_id);
        $stmt_update->execute();
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO cart_detail (cart_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt_insert->bind_param("ii", $cart_id, $product_id);
        $stmt_insert->execute();
    }

    echo json_encode(["success" => true]);
    exit;
}

/* 🔄 Cập nhật số lượng sản phẩm */
function updateQuantity($conn, $user_id, $data) {
    $product_id = $data['product_id'] ?? 0;
    $new_quantity = $data['quantity'] ?? 1;

    if (!$product_id || $new_quantity < 1) {
        echo json_encode(["success" => false]);
        exit;
    }

    $sql = "UPDATE cart_detail cd
            JOIN cart c ON cd.cart_id = c.cart_id
            SET cd.quantity = ?
            WHERE c.user_id = ? AND cd.product_id = ? AND c.status = 'active'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
    $stmt->execute();

    echo json_encode(["success" => true]);
}

/* ❌ Xóa sản phẩm khỏi giỏ hàng */
function removeItem($conn, $user_id, $data) {
    $product_id = $data['product_id'] ?? 0;

    if (!$product_id) {
        echo json_encode(["success" => false]);
        exit;
    }

    $sql = "DELETE cd FROM cart_detail cd
            JOIN cart c ON cd.cart_id = c.cart_id
            WHERE c.user_id = ? AND cd.product_id = ? AND c.status = 'active'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    echo json_encode(["success" => true]);
}
/* 🛍️ Lấy số lượng sản phẩm trong giỏ hàng */
/* 🛍️ Lấy số lượng sản phẩm trong giỏ hàng (CHỈ ĐẾM SẢN PHẨM "AVAILABLE") */
function getCartCount($conn, $user_id) {

    $sql = "SELECT SUM(cd.quantity) AS total
            FROM cart_detail cd
            JOIN cart c ON cd.cart_id = c.cart_id
            JOIN products p ON cd.product_id = p.product_id
            WHERE c.user_id = ? AND p.status = 'AVAILABLE' AND c.status = 'active'";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["count" => 0, "error" => $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode(["count" => (int)($row['total'] ?? 0)]);
    exit;
}
