<?php
session_name("admin");
session_start();
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

// Nhận dữ liệu JSON từ file JS gửi lên
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['details'])) {
    echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ hoặc phiếu trống!"]);
    exit;
}

// Lấy user_id của người đang tạo phiếu
$username = $_SESSION['admin']['username'] ?? '';
if (!$username) {
    echo json_encode(["success" => false, "message" => "Lỗi phiên đăng nhập!"]);
    exit;
}

// Tìm user_id dựa vào username
$stmtUser = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmtUser->bind_param("s", $username);
$stmtUser->execute();
$userResult = $stmtUser->get_result()->fetch_assoc();
$user_id = $userResult['user_id'] ?? 1;

$import_id = $data['import_id'] ?? '';
$supplier_name = $data['supplier_name'] ?? '';
$supplier_phone = $data['supplier_phone'] ?? '';
$supplier_address = $data['supplier_address'] ?? '';
$status = $data['status'] ?? 'draft'; // 'draft' hoặc 'completed'
$details = $data['details'];

try {
    // Bắt đầu Transaction (Bảo toàn dữ liệu)
    $conn->begin_transaction();

    if (empty($import_id)) {
        // TẠO PHIẾU MỚI
        $sqlImport = "INSERT INTO import_receipt (user_id, supplier_name, supplier_phone, supplier_address, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlImport);
        $stmt->bind_param("issss", $user_id, $supplier_name, $supplier_phone, $supplier_address, $status);
        $stmt->execute();
        $import_id = $conn->insert_id; // Lấy ID phiếu vừa tạo
    } else {
        // CẬP NHẬT PHIẾU NHÁP CŨ
        $sqlImport = "UPDATE import_receipt SET supplier_name=?, supplier_phone=?, supplier_address=?, status=? WHERE receipt_id=?";
        $stmt = $conn->prepare($sqlImport);
        $stmt->bind_param("ssssi", $supplier_name, $supplier_phone, $supplier_address, $status, $import_id);
        $stmt->execute();

        // Xóa chi tiết cũ để thêm lại
        $conn->query("DELETE FROM import_receipt_detail WHERE receipt_id = $import_id");
    }

    // Câu lệnh chuẩn bị thêm chi tiết sản phẩm
    $sqlDetail = "INSERT INTO import_receipt_detail (receipt_id, product_id, quantity, import_price) VALUES (?, ?, ?, ?)";
    $stmtDetail = $conn->prepare($sqlDetail);

    // Câu lệnh chuẩn bị cộng dồn số lượng kho (Chỉ dùng khi chốt phiếu)
    $sqlUpdateStock = $conn->prepare("UPDATE products SET current_stock = current_stock + ? WHERE product_id = ?");

    // Lặp qua mảng sản phẩm JS gửi lên
    foreach ($details as $item) {
        $p_id = $item['product_id'];
        $qty = $item['quantity'];
        $price = $item['import_price'];

        // Lưu vào bảng import_receipt_detail
        $stmtDetail->bind_param("iiid", $import_id, $p_id, $qty, $price);
        $stmtDetail->execute();

        // NẾU CHỐT PHIẾU: Cộng tồn kho ngay lập tức
        if ($status === 'completed') {
            $sqlUpdateStock->bind_param("ii", $qty, $p_id);
            $sqlUpdateStock->execute();
        }
    }

    // Hoàn tất Transaction
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Lưu phiếu thành công!", "import_id" => $import_id]);

} catch (Exception $e) {
    // Nếu có lỗi, rollback (hủy) toàn bộ thay đổi
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Lỗi cơ sở dữ liệu: " . $e->getMessage()]);
}
?>