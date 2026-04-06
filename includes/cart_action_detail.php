<?php
session_name("user");
session_start();

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra đăng nhập
if (
    !isset($_SESSION['user']) || 
    !isset($_SESSION['user']['username']) || 
    !isset($_SESSION['user']['role'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Vui lòng đăng nhập để thực hiện hành động này."
    ]);
    exit;
}

include "../app/config/data_connect.php"; // Kết nối database

$user_id = $_SESSION['user']['user_id'];
$role = $_SESSION['user']['role'];

$data = $_POST; // Sử dụng $_POST thay vì json_decode vì JS gửi form-urlencoded
$action = $data['action'] ?? '';
$product_id = intval($data['product_id'] ?? 0);
$quantity = intval($data['quantity'] ?? 1); // Lấy số lượng từ yêu cầu AJAX, mặc định là 1

$username = $_SESSION['user']['username'];

// Kiểm tra user có tồn tại không
$sql_check_user = "SELECT username FROM users WHERE user_id = ?";
$stmt_check_user = $conn->prepare($sql_check_user);
$stmt_check_user->bind_param("i", $user_id);
$stmt_check_user->execute();
$result_user = $stmt_check_user->get_result();

if ($result_user->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User does not exist."]);
    exit;
}

if ($action == "add" && $product_id > 0) {
    // Lấy current_stock từ database
    $stmt_stock = $conn->prepare("SELECT current_stock FROM products WHERE product_id = ?");
    $stmt_stock->bind_param("i", $product_id);
    $stmt_stock->execute();
    $stock_result = $stmt_stock->get_result();
    
    if ($stock_result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Sản phẩm không tồn tại."]);
        exit;
    }
    
    $stock_row = $stock_result->fetch_assoc();
    $current_stock = intval($stock_row['current_stock']);
    
    // Lấy cart_id
    $stmt_cart = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND status = 'active'");
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

    // Kiểm tra sản phẩm trong cart_detail và lấy số lượng hiện tại
    $stmt_check = $conn->prepare("SELECT quantity FROM cart_detail WHERE cart_id = ? AND product_id = ?");
    $stmt_check->bind_param("ii", $cart_id, $product_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    $current_quantity_in_cart = 0;
    $product_exists_in_cart = false;
    
    if ($result_check->num_rows > 0) {
        $cart_item = $result_check->fetch_assoc();
        $current_quantity_in_cart = intval($cart_item['quantity']);
        $product_exists_in_cart = true;
    }
    
    // Kiểm tra tổng số lượng có vượt quá stock không
    $total_quantity = $current_quantity_in_cart + $quantity;
    if ($total_quantity > $current_stock) {
        echo json_encode([
            "success" => false,
            "message" => "Hàng không đủ! Số lượng tồn kho: " . $current_stock . ", số lượng trong giỏ: " . $current_quantity_in_cart . ". Bạn chỉ có thể thêm tối đa " . max(0, $current_stock - $current_quantity_in_cart) . " sản phẩm."
        ]);
        exit;
    }

    if ($product_exists_in_cart) {
        // Tăng số lượng
        $stmt_update = $conn->prepare("UPDATE cart_detail SET quantity = quantity + ? WHERE cart_id = ? AND product_id = ?");
        $stmt_update->bind_param("iii", $quantity, $cart_id, $product_id);
        $stmt_update->execute();
    } else {
        // Thêm mới
        $stmt_insert = $conn->prepare("INSERT INTO cart_detail (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iii", $cart_id, $product_id, $quantity);
        $stmt_insert->execute();
    }

    // Tính tổng số lượng
    $cart_query = "SELECT COUNT(*) AS total FROM cart_detail cd JOIN cart c ON cd.cart_id = c.cart_id JOIN products p ON cd.product_id = p.product_id WHERE c.user_id = ? AND p.status = 'AVAILABLE' AND c.status = 'active'";
    $stmt_cart_sum = $conn->prepare($cart_query);
    $stmt_cart_sum->bind_param("i", $user_id);
    $stmt_cart_sum->execute();
    $cart_result = $stmt_cart_sum->get_result();
    $cart_row = $cart_result->fetch_assoc();
    $cart_count = $cart_row['total'] ?? 0;

    echo json_encode([
        "success" => true,
        "message" => "Sản phẩm đã được thêm vào giỏ hàng.",
        "cart_count" => $cart_count
    ]);
} else {
    echo json_encode(["success" => false, "message" => "An error occurred."]);
}
?>
