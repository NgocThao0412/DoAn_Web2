<?php
session_name("admin");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include_once __DIR__ . '/../../config/data_connect.php';

$response = [
    'loggedIn' => false,
    'username' => null,
    'fullname' => null,
    'email' => null,
    'phone' => null,
    'city' => null,
    'ward' => null,
    'street' => null,
    'role' => null,
    'status' => null,
    'message' => 'Bạn chưa đăng nhập.'
];

// Kiểm tra session admin
if (isset($_SESSION['admin']) && is_array($_SESSION['admin'])) {

    $username = $_SESSION['admin']['username'] ?? null;

    if ($username) {

        // LẤY THÊM fullname + email
        $stmt = $conn->prepare("SELECT fullname, email, phone, city, ward, street, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $stmt->close();

            if ($userData) {
                $response = [
                    'loggedIn' => true,
                    'username' => $username,
                    'fullname' => $userData['fullname'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'],
                    'city' => $userData['city'],
                    'ward' => $userData['ward'],
                    'street' => $userData['street'],
                    'role' => $userData['role'],
                    'status' => $userData['status'],
                    'message' => 'Phiên hợp lệ.'
                ];
            } else {
                $response['message'] = "Người dùng không tồn tại.";
            }
        } else {
            $response['message'] = "Lỗi truy vấn cơ sở dữ liệu.";
        }
    }
}

echo json_encode($response);
?>