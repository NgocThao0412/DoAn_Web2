<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

try {
    $sql = "SELECT 
                ird.detail_id, 
                ird.receipt_id, 
                p.product_id,
                p.name as product_name, 
                ird.import_price as cost_price, 
                IF(p.profit_percent IS NULL OR p.profit_percent = 0, 20, p.profit_percent) as profit_percent,
                p.selling_price
            FROM import_receipt_detail ird
            JOIN products p ON ird.product_id = p.product_id
            -- Sắp xếp để những món mới nhập hiện lên đầu danh sách
            ORDER BY ird.receipt_id ASC, ird.detail_id ASC";

    $result = $conn->query($sql);
    $data = [];

    if ($result) {
        while($row = $result->fetch_assoc()) {
            // Ép kiểu dữ liệu số để JS tính toán không bị lỗi chuỗi
            $row['cost_price'] = floatval($row['cost_price']);
            $row['profit_percent'] = floatval($row['profit_percent']);
            $row['selling_price'] = floatval($row['selling_price']);
            
            $data[] = $row;
        }
    }

    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>