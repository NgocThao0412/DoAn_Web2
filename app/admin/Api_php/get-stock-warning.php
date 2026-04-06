<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

// 1. Lấy ngưỡng cảnh báo, mặc định là 10
$threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 10;

try {
    $sql = "SELECT 
                p.product_id, 
                p.name, 
                p.unit, 
                p.image, 
                c.name AS category_name,
                (SELECT COALESCE(SUM(ird.quantity), 0) 
                 FROM import_receipt_detail ird 
                 JOIN import_receipt ir ON ird.receipt_id = ir.receipt_id 
                 WHERE ird.product_id = p.product_id AND ir.status = 'completed') as total_import,
                (SELECT COALESCE(SUM(od.quantity), 0) 
                 FROM order_detail od 
                 JOIN orders o ON od.order_id = o.order_id 
                 WHERE od.product_id = p.product_id AND o.order_status = 'COMPLETED') as total_export
            FROM products p
            LEFT JOIN category c ON p.category_id = c.category_id";

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Lỗi Query: " . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Tính toán tồn kho thực tế
        $actual_stock = (int)$row['total_import'] - (int)$row['total_export'];
        
        // Chỉ lấy những món dưới hoặc bằng ngưỡng cảnh báo
        if ($actual_stock <= $threshold) {
            $row['current_stock'] = $actual_stock;
            
            // Xử lý để không bị lỗi null nếu sản phẩm chưa có loại
            if (empty($row['category_name'])) {
                $row['category_name'] = "Chưa phân loại";
            }
            
            $data[] = $row;
        }
    }

    // Trả về dữ liệu dạng JSON cho Javascript
    echo json_encode($data);

} catch (Exception $e) {
    // Nếu có lỗi SQL hoặc kết nối, trả về thông báo lỗi
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>