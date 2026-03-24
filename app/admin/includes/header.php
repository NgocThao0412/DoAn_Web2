<link rel="stylesheet" href="assets/css/style.css">
<?php
// Xác định file hiện tại
$page = basename($_SERVER['PHP_SELF']);

// Xác định tiêu đề trang
switch ($page) {
    case "list-product.php": $title = "Danh sách sản phẩm"; break;
    case "add-product.php": $title = "Thêm sản phẩm"; break;
    case "edit-product.php": $title = "Sửa sản phẩm"; break;
    case "category.php": $title = "Danh mục sản phẩm"; break;
    case "add-category.php": $title = "Thêm loại sản phẩm"; break;

    case "manager-user.php": $title = "Quản lý người dùng"; break;

    case "import-list.php": $title = "Danh sách phiếu nhập"; break;
    case "import-add.php": $title = "Tạo phiếu nhập"; break;

    case "price-manager.php": $title = "Quản lý giá bán"; break;

    case "order-manager.php": $title = "Quản lý đơn hàng"; break;

    case "stock-report.php": $title = "Báo cáo tồn kho"; break;
    case "stock-warning.php": $title = "Cảnh báo sắp hết hàng"; break;

    default: $title = "Bảng điều khiển";
}



// Kiểm tra đăng nhập admin
$authButtons = '<button id="login-btn" class="btnLogin-popup">Đăng nhập</button>';

if (isset($_SESSION['admin']) && isset($_SESSION['admin']['username'])) {
    $username = htmlspecialchars($_SESSION['admin']['username']); 
    $authButtons = '
        <div class="navigation">
            <button id="user-btn" class="btnLogin-popup" onclick="window.location.href=\'profit\'">' . $username . '</button>
            <form action="Login_processing/logout_processing.php" method="POST" style="display:inline;">
                <button type="submit" id="logout-btn" class="btnLogout-popup">Thoát</button>
            </form>
        </div>
    ';
}
?>
<div class="header">
    <div class="text-heading">
        <h1><?php echo $title; ?></h1>
    </div>
    <?php echo $authButtons; ?>
    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </div>
</div>

<!-- MENU MOBILE -->
<div class="mobile-menu" id="mobileMenu">
    <button class="logo" style="border:none; background:none;">
        <img src="../../public/assets/Img/golden_crumb.png" alt="Logo">
    </button>
    <?php if (isset($_SESSION['admin']) && isset($_SESSION['admin']['username'])): ?>
        <div class="mobile-user" style="text-align:center; margin:10px 0;">
            <button id="mobile-user-btn" class="btnLogin-popup" onclick="window.location.href='profit'">
                <?php echo htmlspecialchars($_SESSION['admin']['username']); ?>
            </button>
        </div>
    <?php endif; ?>

    <div class="menu-container">

        <!-- QUẢN LÝ NGƯỜI DÙNG -->
        <button class="grade-button" onclick="toggleGrade('gradeUser', 'chevronUser')">
            <span class="text-head">
                <ion-icon name="person-circle-outline"></ion-icon>
                Quản lý người dùng
            </span>
        </button>

        <!-- QUẢN LÝ SẢN PHẨM -->
        <button class="grade-button" onclick="toggleGrade('gradeProduct', 'chevronProduct')">
            <span class="text-head">
                <ion-icon name="fast-food-outline"></ion-icon>
                Quản lý sản phẩm
            </span>
            <span class="chevron up" id="chevronProduct"></span>
        </button>

        <div class="subject-list" id="gradeProduct">
            <div class="subject-item">
                <a href="list-product">Danh sách sản phẩm</a>
            </div>
            <div class="subject-item">
                <a href="add-product">Thêm sản phẩm</a>
            </div>
            <div class="subject-item">
                <a href="add-category">Thêm loại sản phẩm</a>
            </div>
        </div>

        <!-- QUẢN LÝ NHẬP HÀNG -->
        <button class="grade-button" onclick="toggleGrade('gradeImport', 'chevronImport')">
            <span class="text-head">
                <ion-icon name="receipt-outline"></ion-icon>
                Phiếu nhập hàng
            </span>
            <span class="chevron up" id="chevronImport"></span>
        </button>

        <div class="subject-list" id="gradeImport">
            <div class="subject-item">
                <a href="import-add">Tạo phiếu nhập</a>
            </div>
            <div class="subject-item">
                <a href="import-list">Danh sách phiếu nhập</a>
            </div>
        </div>

        <!-- QUẢN LÝ GIÁ -->
        <button class="grade-button" onclick="toggleGrade('gradePrice', 'chevronPrice')">
            <span class="text-head">
                <ion-icon name="cash-outline"></ion-icon>
                Quản lý giá bán
            </span>
            <span class="chevron up" id="chevronPrice"></span>
        </button>

        <!-- QUẢN LÝ ĐƠN HÀNG -->
        <button class="grade-button" onclick="toggleGrade('gradeOrder', 'chevronOrder')">
            <span class="text-head">
                <ion-icon name="cart-outline"></ion-icon>
                Quản lý đơn hàng
            </span>
            <span class="chevron up" id="chevronOrder"></span>
        </button>

        <div class="subject-list" id="gradeOrder">
            <div class="subject-item">
                <a href="order-manager">Danh sách đơn hàng</a>
            </div>
        </div>

        <!-- TỒN KHO & BÁO CÁO -->
        <button class="grade-button" onclick="toggleGrade('gradeStock', 'chevronStock')">
            <span class="text-head">
                <ion-icon name="stats-chart-outline"></ion-icon>
                Tồn kho & Thống kê
            </span>
            <span class="chevron up" id="chevronStock"></span>
        </button>

        <div class="subject-list" id="gradeStock">
            <div class="subject-item">
                <a href="stock-report">Báo cáo nhập – xuất – tồn</a>
            </div>
            <div class="subject-item">
                <a href="stock-warning">Cảnh báo hết hàng</a>
            </div>
        </div>

    </div>
</div>