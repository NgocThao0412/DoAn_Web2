<?php
require_once '../../config/data_connect.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_provinces') {
    $sql = "SELECT provinceID as code, provinceName as name FROM provinces ORDER BY provinceName ASC";
    $result = $conn->query($sql);
    $data = [];
    while($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
} 

elseif ($action == 'get_wards' && isset($_GET['provinceID'])) {
    $pID = $conn->real_escape_string($_GET['provinceID']);
    $sql = "SELECT wardName as name FROM wards WHERE provinceID = '$pID' ORDER BY wardName ASC";
    $result = $conn->query($sql);
    $data = [];
    while($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}
$conn->close();
?>