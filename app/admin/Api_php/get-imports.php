<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchTerm = "%{$q}%";

$sql = "
    SELECT 
        r.receipt_id, 
        r.import_date, 
        r.supplier_name, 
        r.status,
        COALESCE(SUM(d.quantity * d.import_price), 0) AS total_amount
    FROM import_receipt r
    LEFT JOIN import_receipt_detail d ON r.receipt_id = d.receipt_id
    WHERE r.receipt_id LIKE ? OR r.supplier_name LIKE ?
    GROUP BY r.receipt_id
    ORDER BY r.import_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$imports = [];
while ($row = $result->fetch_assoc()) {
    // Làm đẹp định dạng ngày giờ (vd: 16/03/2026 15:30)
    $date = date_create($row['import_date']);
    $row['import_date'] = date_format($date, "d/m/Y H:i");
    
    $imports[] = $row;
}

echo json_encode($imports);
?>