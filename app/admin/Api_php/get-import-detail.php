<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "ID không hợp lệ"]);
    exit;
}

try {
    // 1. Lấy thông tin chung của phiếu
    $sqlImport = "SELECT * FROM import_receipt WHERE receipt_id = ?";
    $stmt = $conn->prepare($sqlImport);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $import = $stmt->get_result()->fetch_assoc();

    if (!$import) {
        echo json_encode(["success" => false, "message" => "Không tìm thấy phiếu"]);
        exit;
    }

    // 2. Lấy danh sách sản phẩm trong phiếu đó
    $sqlDetails = "
        SELECT d.*, p.name 
        FROM import_receipt_detail d
        JOIN products p ON d.product_id = p.product_id
        WHERE d.receipt_id = ?
    ";
    $stmtDetail = $conn->prepare($sqlDetails);
    $stmtDetail->bind_param("i", $id);
    $stmtDetail->execute();
    $result = $stmtDetail->get_result();

    $details = [];
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }

    echo json_encode([
        "success" => true,
        "import" => $import,
        "details" => $details
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>