<?php
session_name("admin");
session_start();
include('../../config/data_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['username'])) {
        header("Location: ../");
        exit();
    }

    $admin = $_SESSION['admin']['username'];

    $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
    if ($check_column && $check_column->num_rows > 0) {
        $sql = "UPDATE users SET remember_token = NULL WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $admin);
            $stmt->execute();
            $stmt->close();
        }
    }

    session_regenerate_id(true);

    
    $_SESSION = [];
    session_unset();
    session_destroy();

   
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params["path"], $params["domain"], 
            $params["secure"], $params["httponly"]
        );
    }


    setcookie("remember_token", "", time() - 3600, "/");

   
    header("Location: ../");
    exit();
}

header("Location: ../");
exit();
?>