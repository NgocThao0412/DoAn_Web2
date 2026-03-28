<?php
ob_start();
include __DIR__ . '/../../../config/data_connect.php';

$editingProduct = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $editingProduct = $stmt->get_result()->fetch_assoc();
}

$productsPerPage = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $productsPerPage;

$totalQuery = "SELECT COUNT(*) as total FROM products";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

$sql = "SELECT 
            p.product_id,
            p.name,
            p.image,
            p.status,
            p.selling_price,
            p.profit_percent,
            p.current_stock,
            c.name AS category_name
        FROM products p
        JOIN category c ON p.category_id = c.category_id
        ORDER BY p.product_id
        LIMIT $productsPerPage OFFSET $offset";

$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['product_id'];
    $name = $_POST['products_name'];
    $status = $_POST['products_status'];
    $description = $_POST['products_description'];
    $category = $_POST['products_category'];
    $current_stock = $_POST['current_stock'];
    $remove_photo = $_POST['remove_photo_flag'];

    $db_image_path = null;

    if (!empty($_FILES['products_image']['name'])) {
        // Xử lý upload file mới (Giữ nguyên logic cũ)
    } elseif ($remove_photo == "1") {
        $db_image_path = "public/assets/Img/default.png";
    }

    if ($db_image_path) {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, status=?, category_id=?, image=?, current_stock=? WHERE product_id=?");
        $stmt->bind_param("sssissi", $name, $description, $status, $category, $db_image_path, $current_stock, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, status=?, category_id=?, description=?, current_stock=? WHERE product_id=?");
        $stmt->bind_param("ssisii", $name, $status, $category, $description, $current_stock, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href = 'list-product';</script>";
    }

    if ($price < 0) {
        echo "<script>alert('Giá sản phẩm không được nhỏ hơn 0!'); window.history.back();</script>";
        exit;
    }

    $catStmt = $conn->prepare("SELECT name FROM category WHERE category_id = ?");
    $catStmt->bind_param("i", $category);
    $catStmt->execute();
    $catResult = $catStmt->get_result();
    $catRow = $catResult->fetch_assoc();
    $categoryName = $catRow['name'] ?? 'default';

    if (!empty($_FILES['products_image']['name'])) {
        $folderName = str_replace(' ', '', $categoryName);
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/DoAn_Web2/public/assets/Img/" . $folderName . "/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $relative_path = "public/assets/Img/" . $folderName . "/";
        $image_name = basename($_FILES["products_image"]["name"]);
        $new_filename = time() . "_" . $image_name;
        $target_file = $target_dir . $new_filename;
        $db_image_path = $relative_path . $new_filename;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024;
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
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, status=?, category_id=?, image=?, current_stock=? WHERE product_id=?");
            $stmt->bind_param("sssissi", $name, $description, $status, $category, $db_image_path, $_POST['current_stock'], $id);
        }
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, status=?, category_id=?, description=?, current_stock=? WHERE product_id=?");
        $stmt->bind_param("ssisii", $name, $status, $category, $description, $_POST['current_stock'], $id);
    }

    if (isset($stmt) && $stmt->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href = 'list-product';</script>";
    }
}

if (isset($_GET['product_id'])) {
    $id = $_GET['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
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
                previewPath.value = `/assets/Img/${categoryOption}/${fileName}`;
            };
            reader.readAsDataURL(file);
        }
    });
});

function openFileChooserIfCategorySelected() {
    const categorySelect = document.getElementById("products_category");
    if (!categorySelect.value) {
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
    <div class="product-head">LỢI NHUẬN</div>
    <div class="product-head">TỒN KHO</div>
    <div class="product-head">LOẠI SẢN PHẨM</div>
    <div class="product-head">CHỨC NĂNG</div>

    <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="product-items"><?php echo $row['product_id']; ?></div>
        <div class="product-items"><?php echo htmlspecialchars($row['name']); ?></div>
        <div class="product-items">
            <?php $image_path = "../../" . htmlspecialchars($row['image']); ?>
            <img src="<?php echo $image_path; ?>" width="90" height="90" alt="" style="object-fit: cover; border-radius: 5px;">
        </div>
        <div class="product-items">
            <span class="<?= 'status-label ' . strtolower(str_replace(' ', '-', $row['status'])) ?>">
                <?php 
                    switch (strtolower(trim($row['status']))) {
                        case 'available': echo "HIỂN THỊ"; break;
                        case 'hidden': echo "ĐÃ ẨN"; break;
                        default: echo htmlspecialchars($row['status']);
                    }
                ?>
            </span>
        </div>
        <div class="product-items"><?php echo number_format($row['selling_price']); ?> VND</div>
        <div class="product-items"><?php echo number_format($row['profit_percent'], 2); ?>%</div>
        <div class="product-items"><?php echo number_format($row['current_stock']); ?></div>
        <div class="product-items"><?php echo htmlspecialchars($row['category_name']); ?></div>
        <div class="product-items">
            <a href="list-product?edit_id=<?= $row['product_id'] ?>#editModal" class="edit-btn">
                <i class="fas fa-edit"></i>
            </a>
            <form method="GET" action="Controllers/hidden.php" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
                <button type="submit" class="delete-button">
                    <i class="fas <?= $row['status'] == 'HIDDEN' ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                </button>
            </form>
        </div>
    <?php } ?>
</div>

<?php if ($editingProduct): ?>
    <div id="overlay" class="overlay"></div>
    <div id="editModal">
        <div id="editNotification" class="notification edit-notification">
            <h2>Chỉnh sửa sản phẩm</h2>
            <form enctype="multipart/form-data" method="post">
                <input type="hidden" name="product_id" value="<?= $editingProduct['product_id'] ?>">

                <label style="font-weight: bold;">Tên sản phẩm:</label>
                <input type="text" id="products_name" name="products_name" value="<?= htmlspecialchars($editingProduct['name']) ?>">

                <label style="font-weight: bold;">Thay đổi hình ảnh:</label>
                <div id="image-preview-container" style="display: flex; gap: 30px; align-items: flex-start; margin-bottom: 10px;">
                    <div style="flex: 1;">
                        <label>Hiện tại:</label><br>
                        <div style="position: relative; display: inline-block;">
                            <?php if (!empty($editingProduct['image'])): ?>
                                <img id="current_product_image" src="../../<?= htmlspecialchars($editingProduct['image']) ?>" width="120" height="90" style="border: 1px solid #ccc; border-radius: 4px; object-fit: cover;">
                            <?php else: ?>
                                <span>Không có hình ảnh!</span>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="removeCurrentPhoto()" style="width: fit-content; background-color: #e74c3c; color: white; border: none; border-radius: 6px; padding: 6px 12px; cursor: pointer; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; margin-top: 5px;">
                            <i class="fas fa-trash-alt"></i> Xóa hình ảnh
                        </button>
                        <input type="hidden" id="remove_photo_flag" name="remove_photo_flag" value="0">
                    </div>

                    <div style="flex: 1;">
                        <label>Xem trước:</label><br>
                        <div id="preview-wrapper" style="margin-bottom: 5px;"></div>
                        <input type="file" id="products_image" name="products_image" accept=".jpg,.jpeg,.png" style="display: none;">
                        <input type="button" class="browse-button" value="Duyệt..." onclick="openFileChooserIfCategorySelected()" style="margin-top: 5px;">
                    </div>
                </div>

                <div style="display: flex; gap: 30px; margin-bottom: 15px;">
                    <input type="text" readonly class="image-path-input" value="/assets/Img/<?= basename(dirname($editingProduct['image'])) ?>/<?= basename($editingProduct['image']) ?>">
                    <input type="text" id="preview-path" readonly class="image-path-input" placeholder="Đường dẫn sẽ xuất hiện ở đây...">
                </div>

                <label style="font-weight: bold;">Trạng thái</label>
                <select id="products_status" name="products_status">
                    <option value="AVAILABLE" <?= $editingProduct['status']=='AVAILABLE'?'selected':'' ?>>Hiển thị</option>
                    <option value="HIDDEN" <?= $editingProduct['status']=='HIDDEN'?'selected':'' ?>>Ẩn</option>
                </select>

                <label style="font-weight: 800; color: #333;">Giá bán:</label>
                <input type="number" id="products_price" name="products_price" value="<?= $editingProduct['selling_price'] ?>" readonly tabindex="-1" style="background-color: #f5f5f5; color: #888; cursor: not-allowed; border: 1px solid #ddd; margin-bottom: 15px;">

                <label style="font-weight: 800; color: #333;">Tỉ lệ lợi nhuận:</label>
                <input type="number" id="profit_percent" name="profit_percent" value="<?= $editingProduct['profit_percent'] ?>" readonly tabindex="-1" style="background-color: #f5f5f5; color: #888; cursor: not-allowed; border: 1px solid #ddd;">

                <label style="font-weight: bold;">Số lượng tồn kho:</label>
                <input type="number" id="current_stock" name="current_stock" value="<?= $editingProduct['current_stock'] ?>" min="0" required>

                <label style="font-weight: bold;">Loại sản phẩm:</label>
                <select id="products_category" name="products_category">
                    <?php
                    $catResult = $conn->query("SELECT * FROM category");
                    while ($cat = $catResult->fetch_assoc()): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $editingProduct['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label style="font-weight:bold; grid-column: 1 / -1; margin-top: 10px;">Mô tả:</label>
                <textarea id="products_description" name="products_description" rows="5" style="grid-column: 1 / -1; width: 100%; min-height: 120px; padding: 10px; border: 1px solid #D5909f; border-radius: 8px; resize: vertical;"><?= htmlspecialchars($editingProduct['description'] ?? '') ?></textarea>

                <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px;">
                    <button type="submit" style="font-weight: bold; background-color: #4CAF50; color: white; padding: 10px 30px; border: none; border-radius: 8px; cursor: pointer;">Lưu</button>
                    <a href="list-product" class="cancel-button" style="text-decoration: none; color: #9b59b6; font-weight: bold; padding: 0 10px; display: flex; align-items: center;">Hủy</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1; ?>" class="btn"><</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>" class="btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1; ?>" class="btn">></a>
    <?php endif; ?>
</div>