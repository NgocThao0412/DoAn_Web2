<?php
include '../../app/config/data_connect.php';

header('Content-Type: application/json; charset=UTF-8');

// Tắt warning phá JSON
ini_set('display_errors', 0);
error_reporting(0);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ======================
    // LẤY DỮ LIỆU
    // ======================
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city_name'] ?? '');
    $ward = trim($_POST['ward_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $default_role = "customer";

    // ======================
    // VALIDATE
    // ======================

    if (empty($username)) $errors['username'] = "Vui lòng nhập tên đăng nhập.";
    if (empty($fullname)) $errors['fullname'] = "Vui lòng nhập họ tên.";
    if (empty($phone)) $errors['phone'] = "Vui lòng nhập số điện thoại.";
    if (empty($email)) {
        $errors['email'] = "Vui lòng nhập email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email không hợp lệ.";
    }

    if (empty($street)) $errors['street'] = "Vui lòng nhập địa chỉ.";
    if (empty($city)) $errors['city'] = "Vui lòng chọn tỉnh/thành.";
    if (empty($ward)) $errors['ward'] = "Vui lòng chọn phường/xã.";

    if (empty($password)) {
        $errors['password'] = "Vui lòng nhập mật khẩu.";
    } else {
        $password_pattern = '/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/';
        if (!preg_match($password_pattern, $password)) {
            $errors['password'] = "Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, số và ký tự đặc biệt.";
        }
    }

    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Vui lòng xác nhận mật khẩu.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Mật khẩu xác nhận không khớp.";
    }

    // Check username chứa link
    if (!empty($username)) {
        if (preg_match('/\b((https?:\/\/)|(www\.))[^\s]+|[^\s]+\.(com|net|org|vn|info|biz|edu)/i', $username)) {
            $errors['username'] = "Tên đăng nhập không được chứa link.";
        }
    }

    // Check phone
    if (!empty($phone)) {
        $phone_pattern = '/^(03[2-9]|05[2,6,8,9]|07[0-9]|08[1-9]|09[0-9])\d{7}$/';
        if (!preg_match($phone_pattern, $phone)) {
            $errors['phone'] = "Số điện thoại không hợp lệ.";
        }
    }

    // ======================
    // CHECK TRÙNG DB
    // ======================
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? OR email = ? OR phone = ?");
        $stmt->bind_param("sss", $username, $email, $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors['general'] = "Tên đăng nhập, email hoặc số điện thoại đã tồn tại.";
        }

        $stmt->close();
    }

    // ======================
    // INSERT
    // ======================
    if (empty($errors)) {

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, fullname, phone, email, street, ward, city, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $username, $fullname, $phone, $email, $street, $ward, $city, $default_role, $hashed_password);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => "Đăng ký thành công! Vui lòng đăng nhập."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'errors' => ['general' => "Lỗi đăng ký. Vui lòng thử lại."]
            ]);
        }

        $stmt->close();

    } else {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
    }

} else {
    echo json_encode([
        'success' => false,
        'errors' => ['general' => 'Phương thức không hợp lệ.']
    ]);
}

$conn->close();