<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đảm bảo tên session khớp với hệ thống của bạn
session_name("admin");
session_start();

include '../../config/data_connect.php';

// Thiết lập phản hồi dạng JSON
header("Content-Type: application/json");

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu và làm sạch
    $name = isset($_POST['cat_name']) ? trim($_POST['cat_name']) : '';
    $description = isset($_POST['cat_description']) ? trim($_POST['cat_description']) : '';
    $status = isset($_POST['cat_status']) ? intval($_POST['cat_status']) : 1;

    // Kiểm tra nghiệp vụ cơ bản
    if (empty($name)) {
        $response["message"] = "Tên loại sản phẩm không được để trống.";
        echo json_encode($response);
        exit();
    }

    // Kiểm tra xem tên loại sản phẩm đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT category_id FROM category WHERE name = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response["message"] = "Tên loại sản phẩm này đã tồn tại!";
        $check_stmt->close();
    } else {
        $check_stmt->close();
        
        // Thực hiện thêm mới
        $sql = "INSERT INTO category (name, description, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $description, $status);

        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Thêm loại sản phẩm thành công.";
        } else {
            $response["message"] = "Lỗi Database: " . $conn->error;
        }
        $stmt->close();
    }

    echo json_encode($response);
    exit();
}

// Nếu truy cập trực tiếp file này không qua POST
$response["message"] = "Yêu cầu không hợp lệ.";
echo json_encode($response);