<?php
include '../../config/data_connect.php';

if (isset($_POST['id']) && isset($_POST['current_status'])) {
    $id = intval($_POST['id']);
    $newStatus = ($_POST['current_status'] == 1) ? 0 : 1; // Đảo ngược trạng thái

    $sql = "UPDATE category SET status = ? WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newStatus, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $conn->error]);
    }
    exit();
}
?>