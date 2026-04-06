<?php
session_name("admin");
session_start();

require_once '../../config/data_connect.php';

$username = $_POST['username'] ?? '';
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';
$street = $_POST['street'] ?? '';
$city = $_POST['city_name'] ?? '';
$ward = $_POST['ward_name'] ?? '';

$is_update = isset($_POST['is_update']) && $_POST['is_update'] == '1';

/* ===== kiểm tra role hợp lệ ===== */
$allowedRoles = ['admin','customer'];
if (!in_array($role, $allowedRoles)) {
    echo "Vai trò không hợp lệ!";
    exit;
}

/* ================= UPDATE USER ================= */

if ($is_update) {

    $check = $conn->prepare("SELECT username FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        echo "Người dùng không tồn tại!";
        exit;
    }

    $check->close();

    $email_check = $conn->prepare("SELECT username FROM users WHERE email=? AND username<>?");
    $email_check->bind_param("ss", $email, $username);
    $email_check->execute();
    $email_check->store_result();

    if ($email_check->num_rows > 0) {
        echo "Email đã được sử dụng!";
        exit;
    }

    $email_check->close();

    $stmt = $conn->prepare("
        UPDATE users 
        SET fullname=?, phone=?, email=?, role=?, street=?, city=?, ward=? 
        WHERE username=?
    ");

    $stmt->bind_param(
        "ssssssss",
        $fullname,
        $phone,
        $email,
        $role,
        $street,
        $city,
        $ward,
        $username
    );

    if ($stmt->execute()) {
        echo "Cập nhật người dùng thành công!";
    } else {
        echo "Lỗi cập nhật: " . $stmt->error;
    }

    $stmt->close();

/* ================= THÊM USER ================= */

} else {

    $check = $conn->prepare("SELECT username FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Username đã tồn tại!";
        exit;
    }

    $check->close();

    $email_check = $conn->prepare("SELECT username FROM users WHERE email=?");
    $email_check->bind_param("s", $email);
    $email_check->execute();
    $email_check->store_result();

    if ($email_check->num_rows > 0) {
        echo "Email đã tồn tại!";
        exit;
    }

    $email_check->close();

    /* hash password */
    $password = password_hash($password, PASSWORD_DEFAULT);

    $status = "active";

    $stmt = $conn->prepare("
        INSERT INTO users 
        (username, password, fullname, phone, email, city, ward, street, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssssssss",
        $username,
        $password,
        $fullname,
        $phone,
        $email,
        $city,
        $ward,
        $street,
        $role,
        $status
    );

    if ($stmt->execute()) {
        echo "Thêm người dùng thành công!";
    } else {
        echo "Lỗi thêm user: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>