<?php
// Kết nối CSDL
if (!isset($conn)) {
    require_once __DIR__ . '/../app/config/data_connect.php';
}

// Lấy từ khóa tìm kiếm (nếu có)
$term = '';
if (isset($_GET['term'])) {
    $term = trim($_GET['term']);
} elseif (isset($_GET['searchName'])) {
    $term = trim($_GET['searchName']);
}
$term_sql = '';
$params = [];
$types = '';

// Nếu có từ khóa, thêm điều kiện LIKE
if ($term !== '') {
    $term_sql = " AND name LIKE ? ";
    $params[] = "%" . $term . "%";
    $types .= 's';
}

// Lấy category (ưu tiên category, fallback searchCategory, mặc định all)
$category = 'all';
if (!empty($_GET['category'])) {
    $category = trim($_GET['category']);
} elseif (!empty($_GET['searchCategory'])) {
    $category = trim($_GET['searchCategory']);
}

$category = strtolower($category);
$categoryAliases = [
    'macaron' => 'macaron',
    'croissant' => 'croissant',
    'cross' => 'croissant',
    'drink' => 'drink',
    'all' => 'all',
];
$category = $categoryAliases[$category] ?? 'all';

// Lấy min/max price (tìm kiếm nâng cao)
$minPrice = (isset($_GET['minPrice']) && is_numeric($_GET['minPrice']) && $_GET['minPrice'] >= 0)
    ? (int)$_GET['minPrice'] : '';
$maxPrice = (isset($_GET['maxPrice']) && is_numeric($_GET['maxPrice']) && $_GET['maxPrice'] >= 0)
    ? (int)$_GET['maxPrice'] : '';

// Map tên category sang id
$category_map_reverse = [
    'macaron' => 1,
    'croissant' => 2,
    'drink' => 3,
];

// Điều kiện lọc category
$category_sql = '';
if ($category !== 'all' && isset($category_map_reverse[$category])) {
    $category_sql = " AND category_id = ? ";
    $params[] = $category_map_reverse[$category];
    $types .= 'i';
}

// Điều kiện lọc giá
$price_sql = '';
if ($minPrice !== '') {
    $price_sql .= " AND selling_price >= ? ";
    $params[] = $minPrice;
    $types .= 'i';
}
if ($maxPrice !== '') {
    $price_sql .= " AND selling_price <= ? ";
    $params[] = $maxPrice;
    $types .= 'i';
}

// Thiết lập phân trang
$itemsPerPage = 8;
$currentPage  = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset       = ($currentPage - 1) * $itemsPerPage;

// Đếm tổng sản phẩm
$countSql = "SELECT COUNT(*) AS total 
             FROM products 
             WHERE status = 'AVAILABLE' 
             $term_sql $category_sql $price_sql";

$stmtCount = $conn->prepare($countSql);

if (!$stmtCount) {
    die("Prepare failed: " . $conn->error);
}

if ($types !== '') {
    $stmtCount->bind_param($types, ...$params);
}

if (!$stmtCount->execute()) {
    die("Execute failed: " . $stmtCount->error);
}

$stmtCount->bind_result($totalProducts);
$stmtCount->fetch();
$stmtCount->close();

$totalPages = ceil($totalProducts / $itemsPerPage);
// Lấy sản phẩm
$sql = "SELECT product_id, name, selling_price, image, category_id, status
        FROM products
        WHERE status = 'AVAILABLE'
        $term_sql $category_sql $price_sql
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($types !== '') {
    // Gộp tham số phân trang vào mảng
    $paramsWithPaging = [...$params, $itemsPerPage, $offset];
    $typesWithPaging = $types . 'ii';
    $stmt->bind_param($typesWithPaging, ...$paramsWithPaging);
} else {
    $stmt->bind_param('ii', $itemsPerPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Map category id sang tên
$category_map = [
    1 => "macaron",
    2 => "croissant",
    3 => "drink"
];

$productsAll = [];
while ($row = $result->fetch_assoc()) {
    $row['category'] = $category_map[$row['category_id']] ?? 'other';
    $productsAll[] = $row;
}

// Hàm in class active cho filter
function isActiveCategory($current, $expected) {
    return $current === $expected ? 'active' : '';
}
?>

<?php
$isAdvancePage = isset($_GET['page']) && $_GET['page'] === 'advance';
// Luôn hiển thị search container, bất kể page=advance hay home
?>
<div class="products-search-container" id="search-container">
    <div class="search-wrapper">
        <form method="GET" action="index.php" class="search-form" id="main-search-form">
            <input type="hidden" name="page" value="home">
            <div class="input-wrapper">
                <input 
                    id="header-search-input"
                    type="text" 
                    class="search-input" 
                    name="searchName" 
                    value="<?= htmlspecialchars($term) ?>"
                    placeholder="Tìm sản phẩm..."
                >
                <button type="submit" class="filter-submit-btn">
                    <ion-icon name="search-outline"></ion-icon>
                </button>
            </div>
        </form>
        
        <button id="toggle-search-fields" type="button">TÌM KIẾM NÂNG CAO</button>
    </div>
    
    <div id="advanced-filters" style="display: none; width: 100%;">
        <form method="GET" action="index.php" class="filters-form" id="filters-form">
            <input type="hidden" name="page" value="home">
            <input type="hidden" name="searchName" value="<?= htmlspecialchars($term) ?>">
            
            <select name="searchCategory" class="search-criteria">
                <option value="all"<?= $category === 'all' ? ' selected' : '' ?>>Tất cả danh mục</option>
                <option value="macaron"<?= $category === 'macaron' ? ' selected' : '' ?>>Macaron</option>
                <option value="croissant"<?= $category === 'croissant' ? ' selected' : '' ?>>Croissant</option>
                <option value="drink"<?= $category === 'drink' ? ' selected' : '' ?>>Đồ uống</option>
            </select>
            
            <input type="number" min="0" name="minPrice" class="search-criteria" 
                   value="<?= htmlspecialchars($minPrice) ?>" placeholder="Giá từ">
            
            <input type="number" min="0" name="maxPrice" class="search-criteria" 
                   value="<?= htmlspecialchars($maxPrice) ?>" placeholder="Giá đến">
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleBtn = document.getElementById('toggle-search-fields');
        var advancedFilters = document.getElementById('advanced-filters');
        var mainSearchForm = document.getElementById('main-search-form');
        var filtersForm = document.getElementById('filters-form');
        
        // Toggle advanced filters visibility
        if (toggleBtn && advancedFilters) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (advancedFilters.style.display === 'none') {
                    advancedFilters.style.display = 'block';
                    toggleBtn.textContent = 'TÌM KIẾM NÂNG CAO';
                } else {
                    advancedFilters.style.display = 'none';
                    toggleBtn.textContent = 'TÌM KIẾM NÂNG CAO';
                }
            });
        }
        
        // Connect search button with advanced filters
        if (mainSearchForm && filtersForm) {
            mainSearchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get filter values
                var category = filtersForm.querySelector('[name="searchCategory"]').value;
                var minPrice = filtersForm.querySelector('[name="minPrice"]').value;
                var maxPrice = filtersForm.querySelector('[name="maxPrice"]').value;
                
                // Add to main form
                var mainForm = this;
                
                // Remove old filter fields if exist
                mainForm.querySelectorAll('[data-filter="true"]').forEach(el => el.remove());
                
                // Add filter fields
                if (category && category !== 'all') {
                    var catInput = document.createElement('input');
                    catInput.type = 'hidden';
                    catInput.name = 'searchCategory';
                    catInput.value = category;
                    catInput.setAttribute('data-filter', 'true');
                    mainForm.appendChild(catInput);
                }
                
                if (minPrice) {
                    var minInput = document.createElement('input');
                    minInput.type = 'hidden';
                    minInput.name = 'minPrice';
                    minInput.value = minPrice;
                    minInput.setAttribute('data-filter', 'true');
                    mainForm.appendChild(minInput);
                }
                
                if (maxPrice) {
                    var maxInput = document.createElement('input');
                    maxInput.type = 'hidden';
                    maxInput.name = 'maxPrice';
                    maxInput.value = maxPrice;
                    maxInput.setAttribute('data-filter', 'true');
                    mainForm.appendChild(maxInput);
                }
                
                // Submit
                mainForm.submit();
            });
        }
    });
</script>



<div class="pg-12">
    <div class="flex-full">
        <div class="film-title">
            <div class="vertical-line"></div>
        </div>

                <!-- Input Filter (ẩn) -->
        <!-- <input type="radio" name="filter" id="filter-all" class="filter-input" checked>
        <input type="radio" name="filter" id="filter-mousse" class="filter-input">
        <input type="radio" name="filter" id="filter-croissant" class="filter-input">
        <input type="radio" name="filter" id="filter-drink" class="filter-input"> -->
        
        <!-- Filter category -->
        <div class="category-filters" style="display: none;">
            <a href="index.php?page=home&p=1&term=<?= urlencode($term) ?>&category=all" class="<?= isActiveCategory($category, 'all') ?>">All</a>
            <a href="index.php?page=home&p=1&term=<?= urlencode($term) ?>&category=macaron" class="<?= isActiveCategory($category, 'macaron') ?>">Macaron</a>
            <a href="index.php?page=home&p=1&term=<?= urlencode($term) ?>&category=croissant" class="<?= isActiveCategory($category, 'croissant') ?>">Croissant</a>
            <a href="index.php?page=home&p=1&term=<?= urlencode($term) ?>&category=drink" class="<?= isActiveCategory($category, 'drink') ?>">Drink</a>
        </div>

        <!-- Navigation (bạn có thể dùng hoặc bỏ nếu không dùng) -->
        <?php if (empty($term)): ?>
            <nav class="nav-container">
            <ul class="nav-links">
            <li>
                <a class="nav-item <?= isActiveCategory($category, 'all') ?>" href="home">
                   Tất cả
                </a>
            </li>
            <li>/</li>
            <li>
                <a class="nav-item <?= isActiveCategory($category, 'macaron') ?>" href="Macaron">
                   Macaron
                </a>

            </li>
            <li>/</li>
            <li>
                <a class="nav-item <?= isActiveCategory($category, 'croissant') ?>" href="croissant">
                   Bánh Sừng Bò
                </a>
                
            </li>
            <li>/</li>
            <li>
              <a class="nav-item <?= isActiveCategory($category, 'drink') ?>" href="Drink">
                   Đồ Uống
                </a>
            </li>
        </ul>
</nav>
        <?php else: ?>
            <p class="search-summary">Kết quả tìm kiếm <strong><?= $totalProducts ?></strong> sản phẩm cho: "<?= htmlspecialchars($term) ?>"</p>
        <?php endif; ?>

    </div>

    <div class="tab_content" id="product-container">
        <?php if (!empty($productsAll)): ?>
            <?php foreach ($productsAll as $item): ?>
                <div class="movie-item" data-category="<?= htmlspecialchars($item['category']) ?>">
                    <a href="index.php?page=product&id=<?= $item['product_id'] ?>" >
                        <img class="poster-img" height="300" width="300" src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    </a>
                    <p class="title"><?= htmlspecialchars($item['name']) ?></p>

                    <?php if ($item['status'] === 'AVAILABLE'): ?>
                        <button class="add-to-cart butn title" data-id="<?= htmlspecialchars($item['product_id']) ?>">
                            <p class="text-color">Giá: <?= number_format($item['selling_price']) ?> VND</p>
                        </button>
                    <?php else: ?>
                        <button class="butn title disabled-btn" disabled>
                            <p class="text-color"><?= htmlspecialchars($item['status']) ?></p>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-result-message">Không có sản phẩm nào phù hợp.</p>
        <?php endif; ?>
    </div>
    <!-- Phân trang -->
    <div class="container">
    <div class="pagination">
<?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <?php if ($isAdvancePage): ?>
        <a href="index.php?page=advance&p=<?= $i ?>&searchName=<?= urlencode($term) ?>&searchCategory=<?= urlencode($category) ?>&minPrice=<?= urlencode($minPrice) ?>&maxPrice=<?= urlencode($maxPrice) ?>" 
           class="page-link <?= ($i == $currentPage) ? 'active' : '' ?>">
           <?= $i ?>
        </a>
    <?php else:
        $pageUrl = 'index.php?page=home&p=' . $i;
        if ($category !== 'all' && $category !== '') {
            $pageUrl .= '&category=' . urlencode($category);
        }
        if ($term !== '') {
            $pageUrl .= '&term=' . urlencode($term);
        }
        if ($minPrice !== '') {
            $pageUrl .= '&minPrice=' . urlencode($minPrice);
        }
        if ($maxPrice !== '') {
            $pageUrl .= '&maxPrice=' . urlencode($maxPrice);
        }
    ?>
        <a href="<?= $pageUrl ?>" 
           class="page-link <?= ($i == $currentPage) ? 'active' : '' ?>">
           <?= $i ?>
        </a>
    <?php endif; ?>
<?php endfor; ?>
</div>
</div>

