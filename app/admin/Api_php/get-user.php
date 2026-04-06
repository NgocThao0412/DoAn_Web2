<?php
include '../../config/data_connect.php';
header("Content-Type: application/json; charset=UTF-8");

if (isset($_GET['username']) && !empty($_GET['username'])) {
    $user_name = trim($_GET['username']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?"); // Sử dụng prepared statement để tránh SQL injection
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user['address'] = trim($user['street'] . ', ' . $user['ward'] . ', ' . $user['city'], ', ');

        echo json_encode([
            'username'    => $user['username'],
            'fullname'   => $user['fullname'],
            'password'    => $user['password'],
            'email'       => $user['email'],
            'phone'       => $user['phone'],
            'city'        => $user['city'],
            'ward'        => $user['ward'],
            'street'      => $user['street'],
            'role'        => $user['role'],
            'address'     => $user['address']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'Missing or invalid user ID']);
}
