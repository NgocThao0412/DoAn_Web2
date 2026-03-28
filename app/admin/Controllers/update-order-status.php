<?php
include '../../config/data_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $allowed_status = ['PENDING', 'PROCESSING', 'COMPLETED'];

    if ($order_id > 0 && in_array($status, $allowed_status)) {
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