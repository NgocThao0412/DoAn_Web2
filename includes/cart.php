<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("user");
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kết nối database nếu chưa có
if (!isset($conn)) {
    include_once __DIR__ . "/../app/config/data_connect.php";
}

// Nếu chưa đăng nhập thì không xử lý giỏ hàng
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
    echo '<div class="empty-cart">
            <ion-icon name="close-circle-outline"></ion-icon>    
            Please login to view cart.
          </div>';
    return; // QUAN TRỌNG: không cho chạy tiếp
}

$user_name = $_SESSION['user']['username'];

// Câu lệnh SQL
$sql = "SELECT cart.product_id, product.product_name, product.price, product.image, cart.quantity
        FROM cart
        INNER JOIN product ON cart.product_id = product.product_id
        WHERE cart.user_name = ?";

$stmt = $conn->prepare($sql);

// Nếu prepare lỗi (ví dụ thiếu bảng trong DB mới)
if (!$stmt) {
    echo "<div style='color:red;'>Database error: " . $conn->error . "</div>";
    return;
}

$stmt->bind_param("s", $user_name);

if (!$stmt->execute()) {
    echo "<div style='color:red;'>Query failed: " . $stmt->error . "</div>";
    return;
}

$result = $stmt->get_result();

$total_price = 0;
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

if (empty($products)) {
    echo '<div class="empty-cart">
            <ion-icon name="close-circle-outline"></ion-icon>    
            There are no products in the cart.
          </div>';
    return;
}

// Hiển thị sản phẩm
foreach ($products as $row) {
    $total_price += $row["price"] * $row["quantity"];

    echo '
        <div class="cart-items">
            <img src="' . $row["image"] . '" height="74" width="60" class="cart-img" alt="">
            <span class="infor">
                <button class="close-mini" onclick="removeFromCart(' . $row["product_id"] . ')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
                <span class="head-text">' . $row["product_name"] . '</span>
                <p class="bottom-text">SL
                    <button class="click" onclick="updateQuantity(' . $row["product_id"] . ', -1)">
                        <ion-icon name="caret-back-outline"></ion-icon>
                    </button>
                    <input type="number" class="quantity-input"
                        value="' . $row["quantity"] . '" 
                        min="1">
                    <button class="click" onclick="updateQuantity(' . $row["product_id"] . ', 1)">
                        <ion-icon name="caret-forward-outline"></ion-icon>
                    </button>
                    <span class="price">
                        ' . number_format($row["price"], 0, ",", ".") . ' VND
                    </span>
                </p>
            </span>
        </div>
    ';
}

echo '
    <div class="provisional-charge">
        <p style="color: #ffd1d1;">Provisional invoice</p>
        <p>' . number_format($total_price, 0, ",", ".") . ' VND</p>
    </div>';
?>