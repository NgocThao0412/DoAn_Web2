<?php
session_name("user");
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
include "../app/config/data_connect.php"; // Kết nối database

header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);
$username = $_SESSION['user']['username'];
// Kiểm tra đăng nhập
if (
    !isset($_SESSION['user']) || 
    !isset($_SESSION['user']['username']) || 
    !isset($_SESSION['user']['role'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Please log in before performing this action."
    ]);
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$role = $_SESSION['user']['role'];
$order_date = date('Y-m-d H:i:s');

// Lấy dữ liệu từ form
$fullname = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
$phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
$shipping_city = mysqli_real_escape_string($conn, $_POST['shipping_city_name'] ?? '');
$shipping_ward = mysqli_real_escape_string($conn, $_POST['shipping_ward_name'] ?? '');
$shipping_street = mysqli_real_escape_string($conn, $_POST['shipping_street'] ?? '');
$delivery_date = mysqli_real_escape_string($conn, $_POST['delivery_date'] ?? '');

// $delivery_time = mysqli_real_escape_string($conn, $_POST['delivery_time'] ?? '');

// if (!empty($delivery_time) && strtotime($delivery_time) !== false) {
//     $formatted_time = date("H:i:00", strtotime($delivery_time_raw));
// } else {
//     $formatted_time = null; // hoặc "" tùy DB chấp nhận
// }
# test 
$delivery_time_raw = $_POST['delivery_time'] ?? '';

if ($delivery_time_raw) {
    $parts = explode(':', $delivery_time_raw);
    if (count($parts) >= 2) {
        $hour = intval($parts[0]);
        $minute = intval($parts[1]);
        $formatted_time = sprintf("%02d:%02d:00", $hour, $minute);
    } else {
        $formatted_time = null;
    }
} else {
    $formatted_time = "00:00:00";

if (!empty($_POST['delivery_time'])) {
    $parts = explode(':', $_POST['delivery_time']);
    if (count($parts) >= 2) {
        $formatted_time = sprintf("%02d:%02d:00", $parts[0], $parts[1]);
    }
}
}


$note = mysqli_real_escape_string($conn, $_POST['note'] ?? '');



// Kiểm tra hợp lệ phương thức thanh toán
$payment_status = 'UNPAID'; // mặc định chưa thanh toán

// Tính tổng tiền đơn hàng
$cart_query = mysqli_query($conn, "
    SELECT cd.cart_id, cd.product_id, cd.quantity, p.selling_price AS price
    FROM cart_detail cd
    JOIN cart c ON cd.cart_id = c.cart_id
    JOIN products p ON cd.product_id = p.product_id
    WHERE c.user_id = $user_id AND c.status = 'active'
") or die(json_encode([
    "success" => false,
    "message" => "Cart query error: " . mysqli_error($conn)
]));


$total_cost = 0;
$cart_items = [];
while ($row = mysqli_fetch_assoc($cart_query)) {
    $subtotal = $row['quantity'] * $row['price'];
    $total_cost += $subtotal;
    $cart_items[] = $row;
}

// Nếu giỏ hàng rỗng
if (empty($cart_items)) {
    echo json_encode([
        "success" => false,
        "message" => "Your cart is empty!"
    ]);
    exit;
}

$payment_status = 'UNPAID';
$order_status = 'PENDING';

$stmt = $conn->prepare("
INSERT INTO orders (
    username,
    user_id,
    recipient_name,
    recipient_phone,
    total_amount,
    payment_status,
    order_status,
    order_date,
    delivery_date,
    delivery_time,
    notes,
    shipping_city,
    shipping_ward,
    shipping_street
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sissdsssssssss",
    $username,
    $user_id,
    $fullname,
    $phone,
    $total_cost,
    $payment_status,
    $order_status,
    $order_date,
    $delivery_date,
    $formatted_time,
    $note,
    $shipping_city,
    $shipping_ward,
    $shipping_street
);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Error creating order: " . $stmt->error
    ]);
    exit;
}

$order_id = $stmt->insert_id;
$stmt->close();

$_SESSION['last_order_id'] = $order_id; // Lưu ID đơn hàng vào session để dùng cho get_last_order_items.php

// Tiến hành thêm chi tiết đơn hàng như trước

   $stmt = $conn->prepare("
    INSERT INTO order_detail (order_id, product_id, quantity, price, unit)
    VALUES (?, ?, ?, ?, ?)
");

foreach ($cart_items as $item) {

    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];

    $unit_query = mysqli_query($conn, "
        SELECT unit FROM products WHERE product_id = $product_id
    ");
    $unit_row = mysqli_fetch_assoc($unit_query);
    $unit = $unit_row['unit'] ?? null;

    $stmt->bind_param("iiids", $order_id, $product_id, $quantity, $price, $unit);
    $stmt->execute();
}

$stmt->close();

mysqli_query($conn, "
    DELETE cd FROM cart_detail cd
    JOIN cart c ON cd.cart_id = c.cart_id
    WHERE c.user_id = $user_id
");

// Xóa giỏ hàng sau khi đặt hàng thành công
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Trả kết quả về JSON
echo json_encode([
    "success" => true,
    "message" => "Order placed successfully!",
    "order_id" => $order_id
]);
exit;
?>
