<?php

include __DIR__ . '/../app/config/config.php';

$searchName = htmlspecialchars($_GET['searchName'] ?? '', ENT_QUOTES);
$searchCategory = $_GET['category'] ?? 'all';
$minPrice = htmlspecialchars($_GET['minPrice'] ?? '', ENT_QUOTES);
$maxPrice = htmlspecialchars($_GET['maxPrice'] ?? '', ENT_QUOTES);

// Mặc định hiển thị Login & Register
$authButtons = '
    <button id="login-btn" class="btnLogin-popup" onclick="window.location.href=\'login\'">Đăng nhập</button>
    <button id="register-btn" class="btnLogout-popup" onclick="window.location.href=\'register\'">Đăng ký</button>
';

// Nếu đã đăng nhập, hiển thị tên người dùng và nút Logout
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $username = htmlspecialchars($_SESSION['user']['username'] ?? 'Guest');
    $authButtons = '
        <div class="user-menu">
             <button id="user-btn" class="btnLogin-popup" onclick="window.location.href=\'index.php?page=profile\'">' . $username . '</button>
            <form action="pages/Controllers/logout.php" method="POST" style="display: inline;">
                <button type="submit" class="btnLogout-popup">Đăng xuất</button>
            </form>
        </div>
    ';                                                      
}
?>

<div class="header">
<meta charset="UTF-8">
    <button class="logo" style="border: none; background: none; cursor:pointer">
        <img src="<?= BASE_URL ?>Img/golden_crumb.png" alt="">
    </button>
    <nav class="navigation">
        <a href="home">TRANG CHỦ</a>
        <a href="about">GIỚI THIỆU</a>
        <a href="receipt">ĐƠN HÀNG</a>

        <button class="sp-cart" id="cart-btn">
            <ion-icon name="cart-outline"></ion-icon>
        </button>
        <span class="cart-count"></span>

      <form method="GET" action="index.php" class="search-container">
            <input type="hidden" name="page" value="advance">
            <input type="submit" style="display: none">
            <div class="input-wrapper">
                <input 
                    id="header-search-input"
                    type="text" 
                    class="search-input" 
                    name="searchName" 
                    value=""
                    placeholder="Tìm sản phẩm..."
                >
                <span class="search-icon">
                    <button class="searchBtn" type="submit">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </span>
            </div>

            <div class="advanced-search-fields" id="advanced-search-fields">
                <select name="category" class="search-criteria">
                    <option value="all"<?= $searchCategory === 'all' ? ' selected' : '' ?>>Tất cả danh mục</option>
                    <option value="macaron"<?= $searchCategory === 'macaron' ? ' selected' : '' ?>>Macaron</option>
                    <option value="croissant"<?= $searchCategory === 'croissant' ? ' selected' : '' ?>>Croissant</option>
                    <option value="Drink"<?= $searchCategory === 'drink' ? ' selected' : '' ?>>Đồ uống</option>
                </select>
                <input type="number" min="0" name="minPrice" class="search-criteria" value="<?= $minPrice ?>" placeholder="Giá từ">
                <input type="number" min="0" name="maxPrice" class="search-criteria" value="<?= $maxPrice ?>" placeholder="Giá đến">
            </div>

            <div class="hint-container"></div>
        </form>

        <button id="toggle-advance-search" type="button" class="searchAdvance">TÌM KIẾM NÂNG CAO</button>
        <div class="auth-container">
            <?= $authButtons ?>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var advButton = document.getElementById('toggle-advance-search');
            var advFields = document.getElementById('advanced-search-fields');
            var searchInput = document.getElementById('header-search-input');
            var searchForm = document.querySelector('.search-container');
            
            // Restore search value from URL parameter hoặc sessionStorage
            var urlParams = new URLSearchParams(window.location.search);
            if (advButton && advFields) {
                advButton.addEventListener('click', function () {
                    advFields.classList.toggle('active');
                    if (advFields.classList.contains('active')) {
                        advButton.textContent = 'ẨN TÌM KIẾM NÂNG CAO';
                    } else {
                        advButton.textContent = 'TÌM KIẾM NÂNG CAO';
                    }
                });
            }
        });
    </script>

    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </div>
</div>

<div class="mobile-menu" id="mobileMenu">
    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </div>

    <div class="mobile-menu-off">
        <a href="home">TRANG CHỦ</a>
        <a href="about">GIỚI THIỆU</a>
        <a href="receipt">ĐƠN HÀNG</a>
        <button class="sp-cart" id="cart-btn">
            <ion-icon name="cart-outline"></ion-icon>
        </button>
        <span class="cart-count cart-count-mobile"></span>

        <div class="btn-log">
            <?= $authButtons ?>
        </div>
    </div>
</div>

<div class="notificate" id="notificate">
    <p id="message"></p>
</div>

