<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name("admin");
session_start();

include '../../config/data_connect.php';

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

$categoryName = "Other";

if ($category_id > 0) {
    $stmt = $conn->prepare("SELECT name FROM category WHERE category_id=?");
    $stmt->bind_param("i",$category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){
        $categoryName = $row["name"];
    }

    $stmt->close();
}
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : ''; // Đưa description lên trên
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $unit = isset($_POST['unit']) ? trim($_POST['unit']) : '';
    $stock = isset($_POST['current_stock']) ? intval($_POST['current_stock']) : 0;
    $profit = isset($_POST['profit_percent']) ? floatval($_POST['profit_percent']) : 0;
    $price = isset($_POST['selling_price']) ? floatval($_POST['selling_price']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    $response = ["status" => "error", "message" => ""];
    

    // Kiểm tra các trường bắt buộc
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

    // ✅ Trường hợp upload file từ form
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileType, $allowedTypes)) {
            $response["message"] = "Chỉ cho phép các tệp JPG, JPEG, PNG và GIF.";
            echo json_encode($response);
            exit();
        }

        // Chuyển category name thành thư mục (viết hoa chữ đầu + không dấu cách)
        $folderName = "products";
        $targetDir = "../../public/assets/Img/$folderName/";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        do {
            $uniqueName = uniqid() . "_" . basename($_FILES['image']['name']);
            $targetFilePath = $targetDir . $uniqueName;
        } while (file_exists($targetFilePath)); // Lặp lại nếu tên đã tồn tại
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = "public/assets/Img/$folderName/" . $uniqueName;
        } else {
            $response["message"] = "Tải ảnh lên thất bại.";
            echo json_encode($response);
            exit();
        }
        
        
    } 
    // Nhận link ảnh từ input 'image' (do upload-img.php gửi qua AJAX)
    elseif (isset($_POST['image']) && !empty(trim($_POST['image']))) {
        $imagePath = ltrim(trim($_POST['image']), '/'); 
    }
    // LƯU Ý quan trọng: Tuyệt đối không dùng preg_replace để xóa hash ở đây.

        // Thêm public/ nếu chưa có
        if (strpos($imagePath, 'public/') !== 0) {
            $imagePath = 'public' . (substr($imagePath, 0, 1) === '/' ? '' : '/') . $imagePath;
        }

        // Thay img thành Img và viết hoa chữ đầu danh mục
        $imagePath = preg_replace_callback('/public\/assets\/Img\/([^\/]+)\//', function ($matches) {
            return 'public/assets/Img/' . ucfirst(strtolower($matches[1])) . '/';
        }, $imagePath);

        // Bỏ phần hash nếu có (ví dụ: /mousse/67d8e37fc0884_filename.png => /mousse/filename.png)
        $cleanPath = preg_replace('/\/[a-z0-9]{6,}_(.+)$/i', '/$1', $imagePath);
        if (!file_exists("../../" . $cleanPath)) {
            $cleanPath = $imagePath; // Giữ nguyên nếu file không tồn tại
        }
        $imagePath = $cleanPath;

    

// 1. SQL khớp với thứ tự cột trong DB của bạn: name, description, category_id, unit, image...
$sql = "INSERT INTO products (name, description, category_id, unit, image, current_stock, profit_percent, selling_price, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// 2. Bind param theo đúng định dạng (ssissisds)
// s: string, i: integer, d: double/decimal
$stmt->bind_param(
    "ssissisds", 
    $name,          // name (s)
    $description,   // description (s)
    $category_id,   // category_id (i) -> Sẽ là số 1, 2, hoặc 3 khớp với phpMyAdmin
    $unit,          // unit (s)
    $imagePath,     // image (s)
    $stock,         // current_stock (i)
    $profit,        // profit_percent (d)
    $price,         // selling_price (d)
    $status         // status (s)
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
