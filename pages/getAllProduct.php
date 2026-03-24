<?php
// 🔥 Bật debug (để thấy lỗi thật)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔥 Luôn trả JSON
header('Content-Type: application/json');

// 🔥 Kết nối DB
if (!isset($conn)) {
    include('../app/config/data_connect.php');
}

// 🔥 Kiểm tra kết nối
if (!$conn) {
    echo json_encode(["error" => "Kết nối DB thất bại"]);
    exit;
}

// 🔥 Lấy từ khóa
$term = $_GET['term'] ?? '';
$term = '%' . $term . '%';

// 🔥 SQL
$sql = "SELECT product_id, name AS product_name, selling_price AS price, image, category_id
        FROM products
        WHERE name COLLATE utf8mb4_general_ci LIKE ?
        AND status = 'AVAILABLE'";

// 🔥 Prepare
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Prepare lỗi", "detail" => $conn->error]);
    exit;
}

// 🔥 Bind
$stmt->bind_param("s", $term);

// 🔥 Execute
if (!$stmt->execute()) {
    echo json_encode(["error" => "Execute lỗi", "detail" => $stmt->error]);
    exit;
}

$result = $stmt->get_result();

if (!$result) {
    echo json_encode(["error" => "Query lỗi", "detail" => $stmt->error]);
    exit;
}

// 🔥 Lấy dữ liệu
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// 🔥 Nếu rỗng vẫn trả JSON hợp lệ
echo json_encode($products ?: []);
exit;