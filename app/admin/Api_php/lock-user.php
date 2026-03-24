<?php
session_name("admin");
session_start();
include '../../config/data_connect.php';

$currentUser = $_SESSION['admin']['username'] ?? '';
if (!isset($_SESSION['admin'])) {
    echo "Bạn chưa đăng nhập.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {

    $username = $_POST['username'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!$username || !in_array($action, ['lock','unlock'])) {
        echo "Dữ liệu không hợp lệ";
        exit();
    }
    
    if ($username === $currentUser && $action === 'lock') {
        echo "Bạn không thể tự khóa tài khoản của mình.";
        exit();
    }

    $status = $action === 'lock' ? 'locked' : 'active';

    $stmt = $conn->prepare("UPDATE users SET status=? WHERE username=?");
    $stmt->bind_param("ss", $status, $username);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo $action === 'lock' ? "Khóa thành công" : "Mở khóa thành công";
    } else {
        echo "Không tìm thấy user.";
    }
}
?>