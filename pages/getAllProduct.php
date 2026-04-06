<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');


if (!isset($conn)) {
    include('../app/config/data_connect.php');
}


if (!$conn) {
    echo json_encode(["error" => "Kết nối DB thất bại"]);
    exit;
}


$term = $_GET['term'] ?? '';
$term = '%' . $term . '%';


$sql = "SELECT product_id, name AS product_name, selling_price AS price, image, category_id
        FROM products
        WHERE name LIKE ?
        AND status = 'AVAILABLE'";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Prepare lỗi", "detail" => $conn->error]);
    exit;
}


$stmt->bind_param("s", $term);


if (!$stmt->execute()) {
    echo json_encode(["error" => "Execute lỗi", "detail" => $stmt->error]);
    exit;
}

$result = $stmt->get_result();

if (!$result) {
    echo json_encode(["error" => "Query lỗi", "detail" => $stmt->error]);
    exit;
}

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products ?: []);
exit;