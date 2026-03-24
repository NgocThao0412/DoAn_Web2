<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

// 2. Lấy ngưỡng cảnh báo, mặc định là 10
$threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 10;

try {
    $sql = "SELECT p.product_id, p.name, p.unit, p.image,
            (SELECT COALESCE(SUM(quantity), 0) 
             FROM import_receipt_detail ird 
             JOIN import_receipt ir ON ird.receipt_id = ir.receipt_id 
             WHERE ird.product_id = p.product_id AND ir.status = 'completed') as total_import,
            (SELECT COALESCE(SUM(quantity), 0) 
             FROM order_detail od 
             JOIN orders o ON od.order_id = o.order_id 
             WHERE od.product_id = p.product_id AND o.order_status = 'COMPLETED') as total_export
            FROM products p";

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Lỗi Query: " . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $actual_stock = $row['total_import'] - $row['total_export'];
        
        // Chỉ lấy những món dưới ngưỡng cảnh báo
        if ($actual_stock <= $threshold) {
            $row['current_stock'] = $actual_stock;
            $row['category_name'] = "Sản phẩm"; // Tạm thời để tránh lỗi JOIN bảng category
            $data[] = $row;
        }
    }

    echo json_encode($data);

} catch (Exception $e) {
    // Nếu lỗi, nó sẽ hiện thông báo lỗi ở đây
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn->close();
?>