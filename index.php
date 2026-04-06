<?php

session_name("user");
session_start();


$page = $_GET['page'] ?? 'home';

// Các trang cần login
$protected_pages = ['pay', 'receipt', 'profile'];

if (in_array($page, $protected_pages) && !isset($_SESSION['user'])) {
    header("Location: login");
    exit();
}

include_once 'app/config/data_connect.php'; // Kết nối database

// Nếu người dùng đã đăng nhập, kiểm tra trạng thái tài khoản
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $username = $_SESSION['user']['username'] ?? null;
    if ($username) {
        $stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $stmt->bind_result($status);
                $stmt->fetch();

                $userData['status'] = $status;
                $stmt->close();
                // Kiểm tra trạng thái tài khoản
                if (!empty($userData['status']) && $userData['status'] === 'locked') {
                
                    $_SESSION = [];
                    session_unset();
                    session_destroy();
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                        );
                    }
                    setcookie("remember_token", "", time() - 3600, "/");
                    header("Location: login&error=account_locked");
                    die();
                }
            } else {
                error_log("Query execution failed: " . $stmt->error);
            }
        } else {
            error_log("Statement preparation failed: " . $conn->error);
        }
    }
}


// Nếu chưa đăng nhập, hoặc sau khi kiểm tra thì lấy thông tin session (dành cho hiển thị thông tin)
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $username = $_SESSION['user']['username'] ?? 'Chưa đăng nhập';
    $role = $_SESSION['user']['role'] ?? 'Chưa đăng nhập';
} else {
    $username = "Không có dữ liệu";
    $role = "Không có dữ liệu";
}

// Danh sách các trang cần yêu cầu đăng nhập
$protectedPages = ['pay'];
// Nếu là trang cần bảo vệ và người dùng chưa đăng nhập
if (in_array($page, $protectedPages)) {
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        header('Location: login&error=method_not_allowed');
        
        exit();
    }
}

$page = $_GET['page'] ?? ($_GET['pages'] ?? 'home');

// Nếu URL rewrite /macaron hoặc /croissant ... thì dùng category và chuyển trang về home
$categoryPages = ['Macaron' => 'Macaron', 'croissant' => 'croissant', 'Drink' => 'Drink', 'all' => 'all'];
if (isset($categoryPages[$page])) {
    $_GET['category'] = $categoryPages[$page];
    $page = 'home';
}
// Nếu page là số hoặc không hợp lệ, về 'home'
if (is_numeric($page)) {
    $page = 'home';
} elseif (!in_array($page, ['home', 'advance', 'product', 'search', 'about', 'order','receipt', 'login', 'register', 'pay', 'profile'])) {
    $page = 'product';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/assets/css/style.css">

    <?php
    // Giả sử $page được lấy ở index.php hoặc set ở header trước khi include
    if ($page === 'login') {
        echo '<link rel="stylesheet" href="public/assets/css/login.css">';
    } elseif ($page === 'register') {
        echo '<link rel="stylesheet" href="public/assets/css/login.css">';
    } elseif ($page === 'about') {
        echo '<link rel="stylesheet" href="public/assets/css/about.css">';
    } elseif ($page === 'receipt') {
        echo '<link rel="stylesheet" href="public/assets/css/receipt.css">';
    } elseif ($page === 'advance') {
        echo '<link rel="stylesheet" href="public/assets/css/searchpro.css">';
        echo '<link rel="stylesheet" href="public/assets/css/index.css">';
    } elseif ($page === 'home') {
        echo '<link rel="stylesheet" href="public/assets/css/index.css">';
    } elseif ($page === 'product') {
        echo '<link rel="stylesheet" href="public/assets/css/product_detail.css">';
    }elseif ($page ==='pay') {
        echo '<link rel="stylesheet" href="public/assets/css/pay.css">';
    } elseif ($page === 'profile') {
    echo '<link rel="stylesheet" href="public/assets/css/profile.css">';
    }
    ?>

   
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" crossorigin="anonymous" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js" crossorigin="anonymous"></script>

    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@7.1.0/dist/ionicons/ionicons.esm.js" crossorigin="anonymous"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/ionicons@7.1.0/dist/ionicons/ionicons.js" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" crossorigin="anonymous"></script>
    <link rel="icon" href="<?php echo 'public/assets/Img/golden_crumb.png'; ?>" type="image/x-icon" />
    

    <title>Golden Crumb</title>
</head>

<body class="<?php echo isset($_SESSION['user']) ? 'logged-in' : ''; ?>">
    <?php include 'includes/header.php'; ?>
    <?php include 'pages/shopping_cart.php';?>
    
    <!-- main screen -->
    <div class="Home_main">
    <?php if ($page === 'advance'): ?>

        <div class="products-container">
            <?php include __DIR__ . '/includes/banner.php'; ?>
            <?php include 'includes/products.php'; ?>
        </div>

    <?php else: ?>

        <?php
        switch ($page) {
            case 'home':
            default:
                include __DIR__ . '/includes/banner.php';;
                include __DIR__ . '/includes/products.php';
                break;

            case 'about':
                include __DIR__ . '/includes/banner.php';
                require __DIR__ . '/pages/about.php';
                break;

            case 'receipt':
                require __DIR__ . '/pages/receipt.php';
                break;

            case 'login':
                $showRegister = false;
                require __DIR__ . '/pages/login.php';
                break;

            case 'register':
                $showRegister = true;
                require __DIR__ . '/pages/register.php';
                break;

            case 'product':
                require __DIR__ . '/pages/product-detail.php';
                break;

            case 'pay':
                require __DIR__ . '/pages/pay.php';
                break;
                
            case 'profile':
                require __DIR__ . '/pages/profile.php';
                break;

        }
        ?>

    <?php endif; ?>
</div>




    <?php include 'includes/footer.php'; ?>
    <?php
    // if ($page === 'home') :
    //     echo '<script src="public/assets/js/index.js"></script>';
    // endif;  // End of if statement
    if ($page === 'login' || $page === 'register') :
        echo '<script src="public/assets/js/login.js"></script>';
    endif;  // End of if statement
    if ($page === 'about') :
        echo '<script src="public/assets/js/about.js"></script>';
    endif;  // End of if statement
    if ($page === 'receipt') :
        echo '<script src="public/assets/js/receipt.js"></script>';
    endif;  // End of if statement
    if ($page === 'advance') :
        echo '<script src="public/assets/js/searchpro.js"></script>';
    endif;  // End of if statement
    if ($page === 'product') :
        echo '<script src="public/assets/js/product.js"></script>';
    endif;  // End of if statement
    if ($page === 'pay') :
        echo '<script src="public/assets/js/pay.js"></script>';
    endif;
    if ($page === 'profile') :
    // Nếu bạn có JS riêng cho profile, ví dụ profile.js
    echo '<script src="public/assets/js/profile.js"></script>';
    endif;
    ?>
    <script src="public/assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</body>

</html>