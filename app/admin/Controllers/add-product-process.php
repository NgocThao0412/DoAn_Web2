<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name("admin");
session_start();

include '../../config/data_connect.php';

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$categoryName = "Other";

if ($category_id > 0) {
    $stmt = $conn->prepare("SELECT name FROM category WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $categoryName = $row["name"];
    }
    $stmt->close();
}

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $unit = isset($_POST['unit']) ? trim($_POST['unit']) : '';
    $profit = isset($_POST['profit_percent']) ? floatval($_POST['profit_percent']) : 0;
    $price = isset($_POST['selling_price']) ? floatval($_POST['selling_price']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    $response = ["status" => "error", "message" => ""];

    // 1. Kiểm tra các trường bắt buộc
    $missingFields = [];
    if (empty($name)) $missingFields[] = "Name";
    if (empty($price)) $missingFields[] = "Price";
    if (empty($category_id)) $missingFields[] = "Category";
    if (empty($unit)) $missingFields[] = "Unit";
    if (empty($status)) $missingFields[] = "Status";
    if (empty($profit)) $missingFields[] = "Profit percent";
    if (empty($description)) $missingFields[] = "Description";
    
    if (!empty($missingFields)) {
        $response["message"] = "Vui lòng điền vào các trường bắt buộc: " . implode(", ", $missingFields);
        echo json_encode($response);
        exit();
    }
    
    // 2. Kiểm tra tính hợp lệ của số liệu
    if (!is_numeric($price) || $price < 0) {
        $response["message"] = "Giá không hợp lệ (phải từ 0 trở lên).";
        echo json_encode($response);
        exit();
    }

    if (!is_numeric($profit) || $profit < 0) {
        $response["message"] = "Lợi nhuận không hợp lệ (phải từ 0 trở lên).";
        echo json_encode($response);
        exit();
    }

    $imagePath = "public/assets/Img/default.png";

$imagePath = "public/assets/Img/default.png"; 

    // 3. Xử lý hình ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($fileType, $allowedTypes)) {
            $folderName = "products"; 
            $targetDir = "../../public/assets/Img/$folderName/";

            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $uniqueName = time() . "_" . basename($_FILES['image']['name']);
            $targetFilePath = $targetDir . $uniqueName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $imagePath = "public/assets/Img/$folderName/$uniqueName";
            }
        }
    } 
    
    elseif (isset($_POST['image']) && !empty(trim($_POST['image']))) {
        $imagePath = ltrim(trim($_POST['image']), '/'); 
    }

$sql = "INSERT INTO products (name, description, category_id, unit, image, profit_percent, selling_price, status, current_stock) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)"; 
$stmt = $conn->prepare($sql);

$stmt->bind_param("ssissdds", $name, $description, $category_id, $unit, $imagePath, $profit, $price, $status);

    if ($stmt->execute()) {
        $response["status"] = "success";
        $response["success"] = true;
        $response["message"] = "Thêm sản phẩm thành công.";
    } else {
        $response["message"] = "Lỗi cơ sở dữ liệu: " . $stmt->error;
    }

    echo json_encode($response);
    exit();
}
?>