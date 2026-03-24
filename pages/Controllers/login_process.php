<?php
session_name("user");
session_start();
require_once '../../app/config/data_connect.php';

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../login");
    exit();
}

// Kiểm tra dữ liệu
if (!isset($_POST['username'], $_POST['password'])) {
    header("Location: ../../login");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if ($username === '' || $password === '') {
    header("Location: ../../login");
    exit();
}

// Truy vấn user
$sql = "SELECT user_id, username, password, role, status FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Không tìm thấy user
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Bạn chưa có tài khoản!";
    header("Location: ../../register");
    exit();
}

$user = $result->fetch_assoc();

// Tài khoản bị khóa
if (strtolower($user['status']) === 'locked') {
    $_SESSION['error'] = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
    header("Location: ../../login");
    exit();
}

// Không đúng role
if (strtolower($user['role']) !== 'customer') {
    header("Location: ../../login&error=role_not_allowed");
    exit();
}

$db_password = $user['password'];

// Nếu mật khẩu chưa hash → hash lại
if (!password_get_info($db_password)['algo']) {
    if ($password !== $db_password) {
        $_SESSION['error'] = "Sai mật khẩu. Vui lòng thử lại.";
        header("Location: ../../login");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $update->bind_param("si", $hashed, $user['user_id']);
    $update->execute();
} else {
    // Mật khẩu đã hash
    if (!password_verify($password, $db_password)) {
        $_SESSION['error'] = "Sai mật khẩu. Vui lòng thử lại.";
        header("Location: ../../login");
        exit();
    }
}

// Login thành công → lưu session
$_SESSION['user'] = $user;

// Chuyển về home
header("Location: ../../index.php?page=home");
exit();
