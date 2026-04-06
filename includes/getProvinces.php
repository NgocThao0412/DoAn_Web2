<?php
include "../app/config/data_connect.php"; // Kết nối DB

header('Content-Type: application/json; charset=UTF-8');

$provinces = [];
$provinceQuery = mysqli_query($conn, "SELECT provinceID, provinceName FROM provinces ORDER BY provinceName ASC");

if (!$provinceQuery) {
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

while ($row = mysqli_fetch_assoc($provinceQuery)) {
    $provinces[] = $row;
}

echo json_encode($provinces, JSON_UNESCAPED_UNICODE);