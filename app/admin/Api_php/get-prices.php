<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

try {
    // Join bảng chi tiết nhập hàng và bảng sản phẩm
    $sql = "SELECT 
                ird.detail_id, 
                ird.receipt_id, 
                p.product_id,
                p.name as product_name, 
                ird.import_price as cost_price, 
                p.profit_percent,
                p.selling_price
            FROM import_receipt_detail ird
            JOIN products p ON ird.product_id = p.product_id
            ORDER BY ird.detail_id DESC";

    $result = $conn->query($sql);
    $data = [];

    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>