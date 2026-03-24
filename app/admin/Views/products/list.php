<?php
ob_start();
include __DIR__ . '/../../../config/data_connect.php';
// lấy thông tin sản phẩm:
$editingProduct = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $editingProduct = $stmt->get_result()->fetch_assoc();
}

// 🔹 Phân trang: cần đặt TRƯỚC khi truy vấn
$productsPerPage = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $productsPerPage;

// 🔹 Lấy tổng số sản phẩm (không bao gồm Hidden)
$totalQuery = "SELECT COUNT(*) as total FROM products";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

// // Sau khi có $totalProducts và $totalPages
// if ($page > $totalPages && $totalPages > 0) {
//     header("Location: ?page=" . $totalPages);
//     exit;
// }

// // Nếu không còn sản phẩm nào (danh sách trống hoàn toàn)
// if ($totalPages == 0) {
//     $page = 1;
// }


// 🔹 Truy vấn sản phẩm có phân trang (bỏ Hidden)
$sql = "SELECT 
            p.product_id,
            p.name,
            p.image,
            p.status,
            p.selling_price,
            c.name AS category_name
        FROM products p
        JOIN category c ON p.category_id = c.category_id
        ORDER BY p.product_id
        LIMIT $productsPerPage OFFSET $offset";
        

$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}

// 🔹 Xử lý cập nhật sản phẩm (nếu có POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['product_id'];
    $name = $_POST['products_name'];
    $status = $_POST['products_status'];
    $price = $_POST['products_price'];

if ($price < 0) {
    echo "<script>
        alert('Giá sản phẩm không được nhỏ hơn 0!');
        window.history.back();
    </script>";
    exit;
}
    $category = $_POST['products_category'];
    $description = $_POST['products_description'];

    $catStmt = $conn->prepare("SELECT name FROM category WHERE category_id = ?");
    $catStmt->bind_param("i", $category);
    $catStmt->execute();
    $catResult = $catStmt->get_result();
    $catRow = $catResult->fetch_assoc();
    $categoryName = $catRow['name'] ?? 'default';

    if (!empty($_FILES['products_image']['name'])) {
        $folderName = str_replace(' ', '', $categoryName);
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/DoAnWeb2/public/assets/Img/" . $folderName . "/";
        // tạo folder nếu chưa tồn tại
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $relative_path = "public/assets/Img/" . $folderName . "/";
        $image_name = basename($_FILES["products_image"]["name"]);
        $new_filename = time() . "_" . $image_name;
        $target_file = $target_dir . $new_filename;
        $db_image_path = $relative_path . $new_filename;

        //kiểm tra định dạng và kích thước hình ảnh
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 10MB
        
        $fileType = $_FILES['products_image']['type'];
        $fileSize = $_FILES['products_image']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('❌ Chỉ chấp nhận tệp .JPG, .JPEG, .PNG.'); window.history.back();</script>";
            exit;
        }
        
        if ($fileSize > $maxSize) {
            echo "<script>alert('❌ Kích thước hình ảnh vượt quá 5MB.'); window.history.back();</script>";
            exit;
        }
        

        if (move_uploaded_file($_FILES["products_image"]["tmp_name"], $target_file)) {
$stmt = $conn->prepare("
UPDATE products 
SET name=?, description=?, status=?, selling_price=?, category_id=?, image=? 
WHERE product_id=?
");

$stmt->bind_param(
"sssissi",
$name,
$description,
$status,
$price,
$category,
$db_image_path,
$id
);
        } else {
            $errorMsg = "❌ Tải hình ảnh thất bại!";
        }
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, status=?, selling_price=?, category_id=?, description=? WHERE product_id=?");
        $stmt->bind_param("sssisi", $name, $status, $price, $category, $description, $id);
    }

    if (isset($stmt) && $stmt->execute()) {
        echo "<script>
            alert('Cập nhật thành công!');
            window.location.href = 'list-product';
        </script>";
    }
}

if (isset($_GET['product_id'])) {
    $id = $_GET['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    echo json_encode($product);
    exit;
}
ob_end_flush();
?>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const inputFile = document.getElementById("products_image");
    const previewWrapper = document.getElementById("preview-wrapper");
    const previewPath = document.getElementById("preview-path");
    const categorySelect = document.getElementById("products_category");

    inputFile?.addEventListener("change", function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const reader = new FileReader();
            const fileName = file.name;
            const categoryOption = categorySelect.options[categorySelect.selectedIndex].text.trim().replace(/\s+/g, '');

            reader.onload = function (e) {
                previewWrapper.innerHTML = '';

                const previewImg = document.createElement("img");
                previewImg.src = e.target.result;
                previewImg.style.maxWidth = "120px";
                previewImg.style.height = "90px";
                previewImg.style.border = "1px solid #ccc";
                previewImg.style.borderRadius = "4px";
                previewImg.alt = "Preview";

                previewWrapper.appendChild(previewImg);

                // Gán đường dẫn giả định
                previewPath.value = `/assets/Img/${categoryOption}/${fileName}`;

            };
            reader.readAsDataURL(file);
        }
    });
});


function openFileChooserIfCategorySelected() {
    const categorySelect = document.getElementById("products_category");
    const selectedValue = categorySelect.value;

    if (!selectedValue) {
        alert("Vui lòng chọn loại sản phẩm trước khi tải lên hình ảnh!");
        categorySelect.focus();
        return;
    }

    document.getElementById("products_image").click();
}
</script>

<div class="product-grid">
    <div class="product-head">ID</div>
    <div class="product-head">TÊN</div>
    <div class="product-head">HÌNH ẢNH</div>
    <div class="product-head">TRẠNG THÁI</div>
    <div class="product-head">GIÁ TIỀN</div>
    <div class="product-head">LOẠI SẢN PHẨM</div>
    <div class="product-head">CHỨC NĂNG</div>

    
    <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="product-items"> <?php echo $row['product_id']; ?> </div>
        <div class="product-items"> <?php echo $row['name']; ?> </div>
        <div class="product-items">
            <?php 
            $image_path = "../../" . htmlspecialchars($row['image']);
            // Vì 'image' đã là đường dẫn tương đối

            ?>
            <img src="<?php echo $image_path; ?>" width="90" height="90" alt="">
        </div>
        <div class="product-items">
            <span class="<?= 'status-label ' . strtolower(str_replace(' ', '-', $row['status'])) ?>">
                <?php 
                   switch (strtolower(trim($row['status']))) {
    case 'available':
        echo "HIỂN THỊ";
        break;
    case 'hidden':
        echo "ĐÃ ẨN";
        break;
    default:
        echo htmlspecialchars($row['status']);
}
                ?>
            </span>
        </div>


        <div class="product-items"> <?php echo number_format($row['selling_price']); ?> VND </div>
        <div class="product-items"> <?php echo $row['category_name']; ?> </div>
        <div class="product-items">
        <a href="list-product?edit_id=<?= $row['product_id'] ?>#editModal"
class="edit-btn"
data-id="<?= $row['product_id'] ?>">
            <i class="fas fa-edit"></i>
        </a>

<form method="GET" action="Controllers/hidden.php" style="display:inline;">
    <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
    <input type="hidden" name="delete_image" id="delete_image" value="0">

   <button type="submit" class="delete-button">
<?php if ($row['status'] == 'HIDDEN'): ?>
    <i class="fas fa-eye"></i>
<?php else: ?>
    <i class="fas fa-eye-slash"></i>
<?php endif; ?>
</button>
</form>
        </div>
    <?php } ?>
</div>



<!-- Edit Notification -->
<?php if ($editingProduct): ?>
    <div id="overlay" class="overlay"></div>
<div id="editModal">
    <div id="editNotification" class="notification edit-notification">
        <h2>Chỉnh sửa sản phẩm</h2>
        <form enctype="multipart/form-data" method="post">
            <input type="hidden" name="product_id" value="<?= $editingProduct['product_id'] ?>">

            <label for="products_name" style="font-weight: bold;">Tên sản phẩm:</label>

            <input type="text" id="products_name" name="products_name" value="<?= htmlspecialchars($editingProduct['name']) ?>">
            <label for="products_image" style="font-weight: bold;">Thay đổi hình ảnh:</label>

            <!-- Phần chứa ảnh hiện tại và ảnh preview -->
            <div id="image-preview-container" style="display: flex; gap: 30px; align-items: flex-start; margin-bottom: 10px;">
                <!-- NOW -->
                <div style="flex: 1;">
                    <label>Hiện tại:</label><br>
                    <?php if (!empty($editingProduct['image'])): ?>
                        <img 
id="current_product_image"
src="../../<?= htmlspecialchars($editingProduct['image']) ?>" 
width="120" 
height="90" alt="Current Image" style="border: 1px solid #ccc; border-radius: 4px;">

<br>
                    <?php else: ?>
                        <span>Không có hình ảnh nào hợp lệ !</span>
                    <?php endif; ?>
                </div>

                <!-- PREVIEW -->
                <div style="flex: 1;">
                    <label>Xem trước:</label><br>
                    <div id="preview-wrapper" style="margin-bottom: 5px;"></div>
                    <input type="file" id="products_image" name="products_image" accept=".jpg,.jpeg,.png" style="display: none;">

                    <input type="button" class="browse-button" value="Duyệt..." onclick="openFileChooserIfCategorySelected()" style="margin-top: 5px;">
                </div>
            </div>

            <!-- 🆕 Đặt 2 ô input đường dẫn ngang hàng -->
            <div style="display: flex; gap: 30px; margin-bottom: 15px;">
                <!-- Path NOW -->
                <input 
                    type="text" 
                    readonly 
                    class="image-path-input" 
                    value="/assets/Img/<?= basename(dirname($editingProduct['image'])) ?>/<?= basename($editingProduct['image']) ?>"
                >

                <!-- Path PREVIEW -->
                <input 
                    type="text" 
                    id="preview-path" 
                    readonly 
                    class="image-path-input" 
                    placeholder="Đường dẫn sẽ xuất hiện ở đây..."
                >
            </div>



<label for="products_status" style="font-weight: bold;">Trạng thái</label>
<select id="products_status" name="products_status">

<option value="AVAILABLE"
<?= $editingProduct['status']=='AVAILABLE'?'selected':'' ?>>
Hiển thị
</option>

<option value="HIDDEN"
<?= $editingProduct['status']=='HIDDEN'?'selected':'' ?>>
Ẩn
</option>

</select>

            <label for="products_price" style="font-weight: bold;">Giá:</label>
            <input type="number" id="products_price" name="products_price"
value="<?= $editingProduct['selling_price'] ?>" 
min="0" step="1" required>

            <label for="products_category" style="font-weight: bold;">Loại sản phẩm:</label>
<select id="products_category" name="products_category">
<?php
$catResult = $conn->query("SELECT * FROM category");
while ($cat = $catResult->fetch_assoc()): ?>
    <option value="<?= $cat['category_id'] ?>" 
        <?= $cat['category_id'] == $editingProduct['category_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($cat['name']) ?>
    </option>
<?php endwhile; ?>
</select>
            <label for="products_description" style="font-weight:bold;">Mô tả</label>

<textarea
id="products_description"
name="products_description"
rows="3"><?= htmlspecialchars($editingProduct['description'] ?? '') ?></textarea>

            <button type="submit" style="font-weight: bold;">Lưu</button>
            <a href="list-product" class="cancel-button">Hủy</a>
        </form>
    </div>
</div>
<?php endif; ?>


<!-- Phân trang -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1; ?>" class="btn"><</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>" class="btn <?= ($i == $page) ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1; ?>" class="btn">></a>
    <?php endif; ?>
</div>
