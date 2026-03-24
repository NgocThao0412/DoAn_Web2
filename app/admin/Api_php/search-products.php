<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

// Nhận từ khóa tìm kiếm
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($q)) {
    echo json_encode([]);
    exit;
}

// Tìm kiếm gần đúng (LIKE) và chỉ lấy sản phẩm đang bán (AVAILABLE)
$searchTerm = "%{$q}%";
$sql = "SELECT product_id, name, current_stock FROM products WHERE name LIKE ? AND status = 'AVAILABLE' LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
?>