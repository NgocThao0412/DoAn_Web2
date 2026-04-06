<?php
if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($showRegister)) {
    $showRegister = false;
}
?>

<div class="wrapper">
    <div class="form-box register" style="display: <?= $showRegister ? 'block' : 'none' ?>;">
        <h2>Đăng ký</h2>
        <form id="registerForm" novalidate action="pages/Controllers/register_process.php" method="POST">

            <input type="hidden" name="city_name" id="city_name">
            <input type="hidden" name="ward_name" id="ward_name">

            <div class="input-infor">
                <div class="left-input">
                    <div class="input-box">
                        <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                        <input id="registerUsername" type="text" name="username" required>
                        <label>Tên đăng nhập <span style="color:red">*</span></label>
                    </div>
                    <div class="error" id="error-username"></div>
                    

                    <div class="input-box">
                        <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                        <input id="registerFirstname" type="text" name="fullname" required>
                        <label>Họ và tên</label>
                    </div>
                    <div class="error" id="error-fullname"></div>

                    <div class="input-box">
                        <span class="icon"><ion-icon name="mail-outline"></ion-icon></span>
                        <input id="registerEmail" type="email" name="email" required>
                        <label>Email</label>
                    </div>
                    <div class="error" id="error-email"></div>

                    <div class="input-box">
                        <span class="icon"><ion-icon name="call-outline"></ion-icon></span>
                        <input id="registerPhone" type="tel" name="phone" required>
                        <label>Số điện thoại</label>
                    </div>
                    <div class="error" id="error-phone"></div>

                </div>

                <div class="right-input">
                    <div class="input-box">
                        <span class="icon"><ion-icon name="location-outline"></ion-icon></span>
                        <input id="registerAddress" type="text" name="street" required>
                        <label>Địa chỉ (Số nhà, đường)</label>
                    </div>
                    <div class="error" id="error-street"></div>

                    <div class="input-box">
                        <span class="icon"><ion-icon name="business-outline"></ion-icon></span>
                        <select id="registerCity" name="city_name" required>
                            <option value="">Chọn Tỉnh / Thành phố</option>
                        </select>
                    </div>
                    <div class="error" id="error-city"></div>
                    

                    <div class="input-box">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <select id="registerWard" name="ward_name" required>
                            <option value="">Chọn Phường / Xã</option>
                        </select>
                    </div>
                    <div class="error" id="error-ward"></div>

                    <div class="input-box">
                        <span class="icon"><ion-icon name="lock-closed-outline"></ion-icon></span>
                        <input id="registerPassword" type="password" name="password" required>
                        <label>Mật khẩu</label>
                    </div>
                    <div class="error" id="error-password"></div>

                    <div class="input-box">
                        <span class="icon"><ion-icon name="lock-closed-outline"></ion-icon></span>
                        <input id="registerConfirmPassword" type="password" name="confirm_password" required>
                        <label>Xác nhận mật khẩu</label>
                    </div>
                    <div class="error" id="error-confirm-password"></div>
                    
                </div>
            </div>

            <button type="submit" class="btn">Đăng ký</button>

            <div class="login-register">
                <p>Đã có tài khoản? <a href="login" class="login-link">Đăng nhập</a></p>
            </div>

        </form>
    </div>
</div>
