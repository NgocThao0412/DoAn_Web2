<?php
include '../../app/config/data_connect.php';

header('Content-Type: application/json');


ini_set('display_errors', 0);
error_reporting(0);

$errors = [];

// ================= LẤY DỮ LIỆU =================
$username = trim($_POST['username'] ?? '');
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$street   = trim($_POST['street'] ?? '');
$city     = trim($_POST['city_name'] ?? '');
$ward     = trim($_POST['ward_name'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// ================= VALIDATE =================

// username
if (!$username) {
    $errors['username'] = "Vui lòng nhập tên đăng nhập";
} elseif (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    $errors['username'] = "Tên đăng nhập không được chứa dấu hoặc ký tự đặc biệt";
}

// fullname
if (!$fullname) {
    $errors['fullname'] = "Vui lòng nhập họ tên";
} elseif (!preg_match('/^[\p{L} ]+$/u', $fullname)) {
    $errors['fullname'] = "Họ tên không được chứa ký tự đặc biệt";
}

// email
if (!$email) {
    $errors['email'] = "Vui lòng nhập email";
} 
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Email không hợp lệ";
} 
elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
    $errors['email'] = "Email phải có dạng @gmail.com";
}

// phone
if (!$phone) {
    $errors['phone'] = "Vui lòng nhập số điện thoại";
} elseif (!preg_match('/^(03|05|07|08|09)\d{8}$/', $phone)) {
    $errors['phone'] = "Số điện thoại phải có 10 chữ số và bắt đầu bằng 03, 05, 07, 08 hoặc 09";
}

// street
if (!$street) {
    $errors['street'] = "Vui lòng nhập địa chỉ";
}

// tỉnh / phường
if (!$city) {
    $errors['city'] = "Vui lòng chọn tỉnh/thành phố";
}

if (!$ward) {
    $errors['ward'] = "Vui lòng chọn phường/xã";
}

// mật khẩu mạnh
if (!$password) {
    $errors['password'] = "Vui lòng nhập mật khẩu";
} elseif (strlen($password) < 8) {
    $errors['password'] = "Mật khẩu phải >= 8 ký tự";
} elseif (
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password)
) {
    $errors['password'] = "Phải có chữ hoa, chữ thường và số";
}

// confirm password
if (!$confirm) {
    $errors['confirm_password'] = "Vui lòng nhập lại mật khẩu";
} elseif ($password !== $confirm) {
    $errors['confirm_password'] = "Mật khẩu không khớp";
}

// ================= CHECK TRÙNG =================
if (empty($errors)) {

    $stmt = $conn->prepare("SELECT username, email, phone FROM users WHERE username=? OR email=? OR phone=?");
    $stmt->bind_param("sss", $username, $email, $phone);

    if (!$stmt->execute()) {
        echo json_encode([
            "success" => false,
            "errors" => ["general" => "Lỗi truy vấn DB"]
        ]);
        exit;
    }

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        if ($row['username'] === $username) {
            $errors['username'] = "Username đã tồn tại";
        }

        if ($row['email'] === $email) {
            $errors['email'] = "Email đã tồn tại";
        }

        if ($row['phone'] === $phone) {
            $errors['phone'] = "SĐT đã tồn tại";
        }
    }

    $stmt->close();
}

// ================= INSERT =================

if (empty($errors)) {

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("
        INSERT INTO users (username, fullname, email, phone, street, city, ward, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssssssss", $username, $fullname, $email, $phone, $street, $city, $ward, $hash);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Đăng ký thành công"
        ]);
        exit;
    } else {
        echo json_encode([
            "success" => false,
            "errors" => ["general" => "Lỗi insert DB"]
        ]);
        exit;
    }

} else {
    echo json_encode([
        "success" => false,
        "errors" => $errors
    ]);
    exit;
}


$conn->close();