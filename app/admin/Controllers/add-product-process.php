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
    $stock = isset($_POST['current_stock']) ? intval($_POST['current_stock']) : 0;
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
    if (empty($stock)) $missingFields[] = "Stock";
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

    if (!is_numeric($stock) || $stock < 0) {
        $response["message"] = "Số lượng không hợp lệ (phải từ 0 trở lên).";
        echo json_encode($response);
        exit();
    }

    if (!is_numeric($profit) || $profit < 0) {
        $response["message"] = "Lợi nhuận không hợp lệ (phải từ 0 trở lên).";
        echo json_encode($response);
        exit();
    }

    $imagePath = "";

    // 3. Xử lý hình ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileType, $allowedTypes)) {
            $response["message"] = "Chỉ cho phép các tệp JPG, JPEG, PNG và GIF.";
            echo json_encode($response);
            exit();
        }

        $folderName = "products";
        $targetDir = "../../public/assets/Img/$folderName/";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        do {
            $uniqueName = uniqid() . "_" . basename($_FILES['image']['name']);
            $targetFilePath = $targetDir . $uniqueName;
        } while (file_exists($targetFilePath));
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = "public/assets/Img/$folderName/" . $uniqueName;
        } else {
            $response["message"] = "Tải ảnh lên thất bại.";
            echo json_encode($response);
            exit();
        }
    } elseif (isset($_POST['image']) && !empty(trim($_POST['image']))) {
        $imagePath = ltrim(trim($_POST['image']), '/'); 
    }

    // Chuẩn hóa đường dẫn ảnh
    if ($imagePath !== "") {
        if (strpos($imagePath, 'public/') !== 0) {
            $imagePath = 'public' . (substr($imagePath, 0, 1) === '/' ? '' : '/') . $imagePath;
        }

        $imagePath = preg_replace_callback('/public\/assets\/Img\/([^\/]+)\//', function ($matches) {
            return 'public/assets/Img/' . ucfirst(strtolower($matches[1])) . '/';
        }, $imagePath);

        $cleanPath = preg_replace('/\/[a-z0-9]{6,}_(.+)$/i', '/$1', $imagePath);
        if (!file_exists("../../" . $cleanPath)) {
            $cleanPath = $imagePath;
        }
        $imagePath = $cleanPath;
    }

    // 4. Lưu vào cơ sở dữ liệu
    $sql = "INSERT INTO products (name, description, category_id, unit, image, current_stock, profit_percent, selling_price, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssissisds", 
        $name,           
        $description,    
        $category_id,    
        $unit,           
        $imagePath,      
        $stock,          
        $profit,         
        $price,          
        $status          
    );

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