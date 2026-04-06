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

<div class="mobile-menu-overlay" id="mobileOverlay" onclick="toggleMenu()"></div>

<!-- MENU MOBILE -->
<div class="mobile-menu" id="mobileMenu">
    <button class="mobile-menu-close" aria-label="Đóng menu" onclick="toggleMenu()">&times;</button>
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


        <!-- QUẢN LÝ SẢN PHẨM -->
            <button class="grade-button" onclick="toggleGrade('gradeProduct', 'chevronProduct')">
                <span class="text-head">
                    <ion-icon name="fast-food-outline"></ion-icon>
                    Quản lý sản phẩm
                </span>
                <span class="chevron up" id="chevronProduct"></span>
            </button>

            <div class="subject-list" id="gradeProduct" style="display:none;">
                <div class="subject-item">
                    <span class="text-in">
                        <ion-icon name="clipboard-outline"></ion-icon>
                        <a class="colorForLink" href="list-product">Danh sách sản phẩm</a>
                    </span>
                </div>

                <div class="subject-item">
                    <span class="text-in">
                        <ion-icon name="add-circle-outline"></ion-icon>
                        <a class="colorForLink" href="add-product">Thêm sản phẩm</a>
                    </span>
                </div>
                <div class="subject-item">
                    <span class="text-in">
                        <ion-icon name="grid-outline"></ion-icon>
                        <a class="colorForLink" href="add-category">Thêm loại sản phẩm</a>
                    </span>
                </div>
            </div>


            <!-- QUẢN LÝ NGƯỜI DÙNG -->
           <button class="grade-button" onclick="location.href='manager-user'">
    <span class="text-head">
        <ion-icon name="person-circle-outline"></ion-icon>
        Quản lý người dùng
    </span>
    </button>


            <!-- QUẢN LÝ PHIẾU NHẬP HÀNG -->
            <button class="grade-button" onclick="toggleGrade('gradeReceipt', 'chevronReceipt')">
                <span class="text-head">
                    <ion-icon name="receipt-outline"></ion-icon>
                    Phiếu nhập hàng
                </span>
                <span class="chevron up" id="chevronReceipt"></span>
            </button>

            <div class="subject-list" id="gradeReceipt" style="display:none;">
                <div class="subject-item">
                    <span class="text-in">
                        <ion-icon name="add-circle-outline"></ion-icon>
                        <a class="colorForLink" href="import-add">Tạo phiếu nhập</a>
                    </span>
                </div>

                <div class="subject-item">
                    <span class="text-in">
                        <ion-icon name="documents-outline"></ion-icon>
                        <a class="colorForLink" href="import-list">Danh sách phiếu nhập</a>
                    </span>
                </div>
            </div>


            <!-- QUẢN LÝ GIÁ BÁN -->
            <button class="grade-button" onclick="location.href='price-manager'">
    <span class="text-head">
        <ion-icon name="cash-outline"></ion-icon>
        Quản lý giá bán
    </span>
    </button>


            <!-- QUẢN LÝ ĐƠN HÀNG -->
           <button class="grade-button" onclick="location.href='order-manager'">
    <span class="text-head">
        <ion-icon name="cart-outline"></ion-icon>
        Quản lý đơn hàng
    </span>
</button>


            <!-- TỒN KHO & THỐNG KÊ -->
            <button class="grade-button" onclick="toggleGrade('gradeStatistical', 'chevronStatistical')">
                <span class="text-head">
                    <ion-icon name="stats-chart-outline"></ion-icon>
                    Tồn kho & Thống kê
                </span>
                <span class="chevron up" id="chevronStatistical"></span>
            </button>

            <div class="subject-list" id="gradeStatistical" style="display:none;">

                <div class="subject-item">
                    <span class="text-in">
                        <ion-icon name="analytics-outline"></ion-icon>
                        <a class="colorForLink" href="stock-report">Báo cáo nhập – xuất – tồn</a>
                    </span>
                </div>

                <div class="subject-item">
                    <span class="text-in">
                        <ion-icon name="alert-circle-outline"></ion-icon>
                        <a class="colorForLink" href="stock-warning">Cảnh báo sắp hết hàng</a>
                    </span>
                </div>

            </div>
    </div>
</div>