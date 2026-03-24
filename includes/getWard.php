<?php
include "../app/config/data_connect.php"; // Kết nối DB

header('Content-Type: application/json; charset=UTF-8');

// Kiểm tra xem có nhận được provinceID từ client không
if (!isset($_GET['provinceID'])) {
    echo json_encode(['error' => 'Missing provinceID']);
    exit;
}

$provinceID = intval($_GET['provinceID']); // Chuyển sang số nguyên để tránh SQL Injection

$wards = [];
$wardQuery = mysqli_query($conn, "SELECT wardID, wardName FROM wards WHERE provinceID = $provinceID ORDER BY wardName ASC");

if (!$wardQuery) {
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

while ($row = mysqli_fetch_assoc($wardQuery)) {
    $wards[] = $row;
}

echo json_encode($wards, JSON_UNESCAPED_UNICODE);