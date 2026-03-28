<?php
session_name("admin");
session_start();
include '../../config/data_connect.php';

$currentUser = $_SESSION['admin']['username'] ?? '';
if (!isset($_SESSION['admin'])) {
    echo "Bạn chưa đăng nhập.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'] ?? '';
    $action = $_POST['action'] ?? '';

    // CẬP NHẬT: Thêm 'reset_password' vào danh sách action cho phép
    if (!$username || !in_array($action, ['lock', 'unlock', 'reset_password'])) {
        echo "Dữ liệu không hợp lệ";
        exit();
    }
    
    if ($username === $currentUser && $action === 'lock') {
        echo "Bạn không thể tự khóa tài khoản của mình.";
        exit();
    }

    // NHÁNH 1: Xử lý Khóa / Mở khóa
    if ($action === 'lock' || $action === 'unlock') {
        $status = ($action === 'lock') ? 'locked' : 'active';
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE username=?");
        $stmt->bind_param("ss", $status, $username);

        if ($stmt->execute()) {
            echo ($action === 'lock') ? "Khóa thành công" : "Mở khóa thành công";
        } else {
            echo "Lỗi khi cập nhật trạng thái.";
        }
    } 
    // NHÁNH 2: Xử lý Reset mật khẩu (MỚI THÊM)
    else if ($action === 'reset_password') {
        // Mật khẩu mặc định là 123456, mã hóa theo chuẩn Bcrypt
        $defaultPass = "123456";
        $hashedPass = password_hash($defaultPass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
        $stmt->bind_param("ss", $hashedPass, $username);

        if ($stmt->execute()) {
            echo "Khởi tạo lại mật khẩu thành công! Mật khẩu mới là: " . $defaultPass;
        } else {
            echo "Lỗi hệ thống: Không thể reset mật khẩu.";
        }
    }

    if (isset($stmt)) {
        $stmt->close();
    }
}
$conn->close();
?>