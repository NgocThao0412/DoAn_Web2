<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

$fromDate = isset($_GET['from']) && !empty($_GET['from']) ? $_GET['from'] : '1970-01-01';
$toDate = isset($_GET['to']) && !empty($_GET['to']) ? $_GET['to'] : date('Y-m-d');

// Đảm bảo định dạng datetime để so sánh chính xác
$startDateTime = $fromDate . ' 00:00:00';
$endDateTime = $toDate . ' 23:59:59';

try {
    $sql_products = "SELECT product_id, name FROM products";
    $result_products = $conn->query($sql_products);
    $report = [];

    while ($p = $result_products->fetch_assoc()) {
        $pid = $p['product_id'];

        // Nhập trước kỳ
        $sql_in_before = "SELECT SUM(ird.quantity) as total FROM import_receipt_detail ird 
                          JOIN import_receipt ir ON ird.receipt_id = ir.receipt_id 
                          WHERE ird.product_id = $pid AND ir.import_date < '$fromDate' AND ir.status = 'completed'";
        $res_in_before = $conn->query($sql_in_before)->fetch_assoc();
        
        // Xuất trước kỳ (Quan trọng: So sánh với created_at của orders)
        $sql_out_before = "SELECT SUM(od.quantity) as total FROM order_detail od 
                           JOIN orders o ON od.order_id = o.order_id 
                           WHERE od.product_id = $pid AND o.created_at < '$startDateTime' AND o.order_status = 'COMPLETED'";
        $res_out_before = $conn->query($sql_out_before)->fetch_assoc();

        $ton_dau = max(0, (int)($res_in_before['total'] ?? 0) - (int)($res_out_before['total'] ?? 0));

        // --- 2. NHẬP TRONG KỲ ($fromDate -> $toDate) ---
        $sql_in_period = "SELECT SUM(ird.quantity) as total FROM import_receipt_detail ird 
                          JOIN import_receipt ir ON ird.receipt_id = ir.receipt_id 
                          WHERE ird.product_id = $pid AND ir.import_date BETWEEN '$fromDate' AND '$toDate' AND ir.status = 'completed'";
        $res_in_period = $conn->query($sql_in_period)->fetch_assoc();
        $nhap_trong_ky = (int)($res_in_period['total'] ?? 0);

        // --- 3. XUẤT TRONG KỲ ($startDateTime -> $endDateTime) ---
        $sql_out_period = "SELECT SUM(od.quantity) as total FROM order_detail od 
                           JOIN orders o ON od.order_id = o.order_id 
                           WHERE od.product_id = $pid AND o.created_at BETWEEN '$startDateTime' AND '$endDateTime' AND o.order_status = 'COMPLETED'";
        $res_out_period = $conn->query($sql_out_period)->fetch_assoc();
        $xuat_trong_ky = (int)($res_out_period['total'] ?? 0);

        $report[] = [
            "product_id" => $pid,
            "product_name" => $p['name'],
            "ton_dau" => $ton_dau,
            "nhap_trong_ky" => $nhap_trong_ky,
            "xuat_trong_ky" => $xuat_trong_ky
        ];
    }

    echo json_encode($report);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>
