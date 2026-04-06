<?php
include '../../config/data_connect.php';
header("Content-Type: application/json");
$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['cat_name']) ? trim($_POST['cat_name']) : '';

    if (empty($name)) {
        $response["message"] = "Tên loại sản phẩm không được để trống.";
        echo json_encode($response); exit();
    }

    $check_stmt = $conn->prepare("SELECT category_id FROM category WHERE name = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $response["message"] = "Tên loại sản phẩm này đã tồn tại!";
    } else {
        $sql = "INSERT INTO category (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Thành công.";
        } else {
            $response["message"] = "Lỗi: " . $conn->error;
        }
    }
    echo json_encode($response);
    exit();
}
?>