<?php
include __DIR__ . '/../app/config/data_connect.php'; // Kết nối database


// Lấy danh sách danh mục từ bảng CATEGORY
$categoryQuery = "SELECT category_id,name FROM categories";
$categoryResult = $conn->query($categoryQuery);
$categories = [];
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($catRow = $categoryResult->fetch_assoc()) {
        $categories[] = $catRow;
    }
}

// Số đơn hàng hiển thị mỗi trang

$ordersPerPage = 8;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $ordersPerPage;

// Kiểm tra nếu form đã được gửi
$searchName = isset($_GET['searchName']) ? trim($_GET['searchName']) : '';
// xài chung name=category với includes/header.php, nếu không có thì all
$searchCategory = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$searchCategoryId = 0;
$categoryMap = [
    'macaron' => 1,
    'croissant' => 2,
    'drink' => 3,
];
if (is_numeric($searchCategory) && (int)$searchCategory > 0) {
    $searchCategoryId = (int)$searchCategory;
} elseif (isset($categoryMap[strtolower($searchCategory)])) {
    $searchCategoryId = $categoryMap[strtolower($searchCategory)];
}

$minPrice = (isset($_GET['minPrice']) && is_numeric($_GET['minPrice']) && $_GET['minPrice'] >= 0)
    ? (int)$_GET['minPrice'] : null;

$maxPrice = (isset($_GET['maxPrice']) && is_numeric($_GET['maxPrice']) && $_GET['maxPrice'] >= 0)
    ? (int)$_GET['maxPrice'] : null;

// Đếm tổng số sản phẩm phù hợp điều kiện tìm kiếm
$countSql = "SELECT COUNT(*) AS total FROM products WHERE 1=1";

if (!empty($searchName)) {
    $searchNameLower = strtolower($conn->real_escape_string($searchName));
    $countSql .= " AND LOWER(name) LIKE '%$searchNameLower%'";
}
if ($searchCategoryId > 0) {
    $countSql .= " AND category_id = $searchCategoryId";
}
if ($minPrice !== null) {
    $countSql .= " AND selling_price >= $minPrice";
}
if ($maxPrice !== null) {
    $countSql .= " AND selling_price <= $maxPrice";
}

$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $ordersPerPage);



// Tạo câu lệnh SQL
$sql = "SELECT * FROM products WHERE 1=1";

if (!empty($searchName)) {
    $sql .= " AND LOWER(name) LIKE '%" . $conn->real_escape_string($searchName) . "%'";
}
if ($searchCategoryId > 0) {
    $sql .= " AND category_id = $searchCategoryId";
}
if (!is_null($minPrice)) {
    $sql .= " AND selling_price >= $minPrice";
}
if (!is_null($maxPrice)) {
    $sql .= " AND selling_price <= $maxPrice";
}

// ✅ Thêm LIMIT và OFFSET để phân trang
$sql .= " LIMIT $ordersPerPage OFFSET $offset";

// Thực hiện truy vấn
$result = $conn->query($sql);
if (!$result) {
    die("Lỗi SQL: " . $conn->error . "\nQuery: " . $sql);
}

?>

    <!-- Đã bỏ thanh tìm kiếm trong searchpro, sử dụng thanh search mặc định ở header -->
    <div class="bsearchpro" style="display: none;"></div>
    <div class="pg-12">
    <div class="tab_content">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="movie-item">
                    <a href="/webb/index.php?page=product&id=<?php echo isset($row['product_id']) ? $row['product_id'] : '#'; ?>">
                        <img class="poster-img" height="300" width="300" src="<?php echo isset($row['image']) ? $row['image'] : 'default.jpg'; ?>" 
                             alt="<?php echo isset($row['name']) ? htmlspecialchars($row['name']) : 'Sản phẩm'; ?>">
                    </a>    
                    <p class="title"> <?php echo isset($row['name']) ? htmlspecialchars($row['name']) : 'Không có tên'; ?> </p>
                    <button class="butn add-to-cart title"
                            data-id="<?php echo $row['product_id']; ?>"
                            onclick="addToCartBtn(<?php echo $row['product_id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', <?php echo $row['selling_price']; ?>)">
                        <p class="text-color">
                            Giá: <?php echo isset($row['selling_price']) ? number_format($row['selling_price'], 0, ',', '.') : '0'; ?> VND
                        </p>
                    </button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Không có sản phẩm phù hợp.</p>
        <?php endif; ?>
    </div>

    
    </div>


    

<!-- Hiển thị phân trang -->
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=advance&p=<?= $i ?>&searchName=<?= urlencode($searchName) ?>&category=<?= urlencode($searchCategory) ?>&minPrice=<?= $minPrice ?>&maxPrice=<?= $maxPrice ?>"
           class="btn <?= ($i == $currentPage) ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
