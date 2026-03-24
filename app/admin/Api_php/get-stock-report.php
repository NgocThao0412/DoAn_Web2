<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

$fromDate = isset($_GET['from']) && !empty($_GET['from']) ? $_GET['from'] : '1970-01-01';
$toDate = isset($_GET['to']) && !empty($_GET['to']) ? $_GET['to'] : date('Y-m-d');

try {
    $sql_products = "SELECT product_id, name FROM products";
    $result_products = $conn->query($sql_products);
    $report = [];

    while ($p = $result_products->fetch_assoc()) {
        $pid = $p['product_id'];

        // --- 1. TỒN ĐẦU KỲ (Trước $fromDate) ---
        // Nhập trước kỳ (chỉ tính phiếu 'completed')
        $sql_in_before = "SELECT SUM(quantity) as total FROM import_receipt_detail ird 
                          JOIN import_receipt ir ON ird.receipt_id = ir.receipt_id 
                          WHERE ird.product_id = $pid AND ir.import_date < '$fromDate' AND ir.status = 'completed'";
        $res_in_before = $conn->query($sql_in_before)->fetch_assoc();
        
        // Xuất trước kỳ (chỉ tính đơn 'COMPLETED')
        $sql_out_before = "SELECT SUM(od.quantity) as total FROM order_detail od 
                           JOIN orders o ON od.order_id = o.order_id 
                           WHERE od.product_id = $pid AND o.created_at < '$fromDate' AND o.order_status = 'COMPLETED'";
        $res_out_before = $conn->query($sql_out_before)->fetch_assoc();

        $ton_dau = ($res_in_before['total'] ?? 0) - ($res_out_before['total'] ?? 0);

        // --- 2. NHẬP TRONG KỲ ($fromDate -> $toDate) ---
        $sql_in_period = "SELECT SUM(quantity) as total FROM import_receipt_detail ird 
                          JOIN import_receipt ir ON ird.receipt_id = ir.receipt_id 
                          WHERE ird.product_id = $pid AND ir.import_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59' AND ir.status = 'completed'";
        $res_in_period = $conn->query($sql_in_period)->fetch_assoc();
        $nhap_trong_ky = $res_in_period['total'] ?? 0;

        // --- 3. XUẤT TRONG KỲ ($fromDate -> $toDate) ---
        $sql_out_period = "SELECT SUM(od.quantity) as total FROM order_detail od 
                           JOIN orders o ON od.order_id = o.order_id 
                           WHERE od.product_id = $pid AND o.created_at BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59' AND o.order_status = 'COMPLETED'";
        $res_out_period = $conn->query($sql_out_period)->fetch_assoc();
        $xuat_trong_ky = $res_out_period['total'] ?? 0;

        $report[] = [
            "product_id" => $pid,
            "product_name" => $p['name'],
            "ton_dau" => (int)$ton_dau,
            "nhap_trong_ky" => (int)$nhap_trong_ky,
            "xuat_trong_ky" => (int)$xuat_trong_ky
        ];
    }

    echo json_encode($report);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>