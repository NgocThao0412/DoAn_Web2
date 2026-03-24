<?php
include '../../config/data_connect.php';

session_name("admin");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['admin_username'];
    $password = $_POST['admin_password'];

    // SỬA ĐÚNG TÊN TRƯỜNG
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['status'] === 'locked') {
            echo json_encode([
                "status" => "error",
                "message" => "Tài khoản của bạn đã bị khóa."
            ]);
            exit;
        }

        $allowed_roles = ['admin', 'staff', 'manager'];
        if (!in_array(strtolower($row['role']), $allowed_roles)) {
            echo json_encode([
                "status" => "error",
                "message" => "Tài khoản này không có quyền truy cập admin."
            ]);
            exit;
        }

        if (password_verify($password, $row['password'])) {

            // LƯU SESSION ĐÚNG TÊN
            $_SESSION['admin'] = [
                'username' => $row['username'], 
                'role'     => $row['role'],
                'status'   => $row['status']
            ];

            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "redirect" => "home",
                "user" => $_SESSION['admin']
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Sai mật khẩu"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Tài khoản không tồn tại"
        ]);
    }

    $stmt->close();
    $conn->close();
}
?>