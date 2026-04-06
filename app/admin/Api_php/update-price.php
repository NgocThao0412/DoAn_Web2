<?php
header("Content-Type: application/json; charset=UTF-8");
include '../../config/data_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Lấy dữ liệu từ POST
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $profit_percent = isset($_POST['profit_percent']) ? floatval($_POST['profit_percent']) : 0;
    $selling_price = isset($_POST['selling_price']) ? floatval($_POST['selling_price']) : 0;

    // 2. Kiểm tra dữ liệu đầu vào
    if ($product_id <= 0) {
        echo json_encode([
            "status" => "error", 
            "message" => "ID sản phẩm không hợp lệ."
        ]);
        exit;
    }

    $sql = "UPDATE products 
            SET profit_percent = ?, 
                selling_price = ? 
            WHERE product_id = ?";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ddi", $profit_percent, $selling_price, $product_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0 || $stmt->errno === 0) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "Hệ thống đã ghi nhận giá bán mới!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Dữ liệu không đổi hoặc sản phẩm không tồn tại."
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Lỗi thực thi: " . $stmt->error
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Lỗi database: " . $conn->error
        ]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Yêu cầu không hợp lệ."
    ]);
}

$conn->close();
?>