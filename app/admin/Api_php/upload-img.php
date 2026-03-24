<?php
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "error" => "Phương thức yêu cầu không hợp lệ."
    ]);
    exit();
}

// Lấy category
$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

// Map category -> folder
$categoryFolders = [
    "1" => "Macaron",
    "3" => "Drink",
    "2" => "Croissant"
];

$folder = "products";

// Đường dẫn lưu file
$uploadDir = __DIR__ . "/../../../public/assets/Img/$folder/";

// Nếu thư mục chưa tồn tại thì tạo
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode([
            "success" => false,
            "error" => "Không thể tạo thư mục lưu ảnh."
        ]);
        exit();
    }
}

// Kiểm tra file upload
if (!isset($_FILES["file"])) {
    echo json_encode([
        "success" => false,
        "error" => "Không có file được tải lên."
    ]);
    exit();
}

if ($_FILES["file"]["error"] !== 0) {
    echo json_encode([
        "success" => false,
        "error" => "Có lỗi xảy ra khi tải file."
    ]);
    exit();
}

$fileName = basename($_FILES["file"]["name"]);
$fileSize = $_FILES["file"]["size"];
$tmpName = $_FILES["file"]["tmp_name"];

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

$allowedTypes = ["jpg","jpeg","png","gif"];
$maxSize = 5 * 1024 * 1024; // 5MB

// Kiểm tra định dạng
if (!in_array($fileExt, $allowedTypes)) {
    echo json_encode([
        "success" => false,
        "error" => "Chỉ cho phép file JPG, JPEG, PNG hoặc GIF."
    ]);
    exit();
}

// Kiểm tra kích thước
if ($fileSize > $maxSize) {
    echo json_encode([
        "success" => false,
        "error" => "Kích thước file vượt quá 5MB."
    ]);
    exit();
}

// Tạo tên file duy nhất
$uniqueName = uniqid() . "_" . $fileName;
$targetPath = $uploadDir . $uniqueName;

// Upload file
if (move_uploaded_file($tmpName, $targetPath)) {

    $filePath = "/public/assets/Img/$folder/$uniqueName";

    echo json_encode([
        "success" => true,
        "message" => "Tải ảnh lên thành công.",
        "filePath" => $filePath
    ]);

} else {

    echo json_encode([
        "success" => false,
        "error" => "Không thể lưu file đã tải lên."
    ]);

}
?>
