<?php
include '../../config/data_connect.php';

// Thiết lập phản hồi trả về dạng JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ FormData gửi qua
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    // 1. SỬA: Danh sách trạng thái phải khớp 100% với ENUM trong sơ đồ Database của bạn
    $allowed_status = ['PENDING', 'PROCESSING', 'COMPLETED'];

    if ($order_id > 0 && in_array($status, $allowed_status)) {
        
        // 2. SỬA: Tên cột là order_status (theo ảnh sơ đồ bạn gửi)
        $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi truy vấn: ' . $conn->error
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ hoặc trạng thái không cho phép!'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức yêu cầu không hợp lệ!'
    ]);
}

$conn->close();
?>