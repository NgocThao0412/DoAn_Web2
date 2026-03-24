<?php

// Nếu đã login rồi thì không cho vào trang login nữa
if (isset($_SESSION['user'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}
if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']);
}
?>

<div class="wrapper">
    <div class="form-box login">
        <h2>Đăng nhập</h2>

        <!-- Hiển thị lỗi -->
        <?php if (isset($_GET['error'])): ?>
            <p style="color:red; text-align:center; margin-bottom:10px;">
                <?php
                switch ($_GET['error']) {
                    case 'wrong_password':
                        echo 'Sai mật khẩu';
                        break;
                    case 'user_not_found':
                        echo 'Tài khoản không tồn tại';
                        break;
                    case 'account_locked':
                        echo 'Tài khoản đã bị khóa';
                        break;
                    case 'role_not_allowed':
                        echo 'Bạn không có quyền truy cập';
                        break;
                    default:
                        echo 'Đăng nhập thất bại';
                }
                ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="pages/Controllers/login_process.php">
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="person-outline"></ion-icon>
                </span>
                <input name="username" type="text" required>
                <label>Tên đăng nhập</label>
            </div>

            <div class="input-box">
                <span class="icon">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                </span>
                <input name="password" type="password" required>
                <label>Mật khẩu</label>
            </div>

            <button type="submit" name="login" class="btn">Đăng nhập</button>

            <div class="login-register">
                <p>Chưa có tài khoản ?
                    <a href="register" class="register-link">Đăng ký</a>
                </p>
            </div>
        </form>
    </div>
</div>
