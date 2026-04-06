<?php
session_name("user");
session_start();
include('../../app/config/data_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
        echo json_encode(["success" => false, "message" => "Bạn chưa đăng nhập!"]);
        exit();
    }

    $user_name = $_SESSION['user']['username'];

    
    $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
    if ($check_column && $check_column->num_rows > 0) {
        $sql = "UPDATE users SET remember_token = NULL WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $user_name);
            $stmt->execute();
            $stmt->close();
        }
    }

    
    $_SESSION = [];
    session_unset();
    session_destroy();

    
    setcookie(session_name(), '', time() - 3600, '/');
    setcookie("remember_token", "", time() - 3600, "/");

    
    header("Location: ../../index.php?page=home");
    exit();
}


header("Location: ../../home");
exit();
?>
