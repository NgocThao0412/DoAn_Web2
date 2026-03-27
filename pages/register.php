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
    <div class="form-box register">
        <h2>Đăng ký</h2>

        <!-- lỗi chung -->
        <div class="error" id="error-general"></div>

        <form id="registerForm" novalidate>

            <!-- hidden -->
            <input type="hidden" name="city_name" id="city_name">
            <input type="hidden" name="ward_name" id="ward_name">

            <div class="input-infor">

                <div class="left-input">
                    <div class="input-box">
                        <input id="registerUsername" type="text" name="username">
                        <label>Tên đăng nhập *</label>
                    </div>
                    <div class="error" id="error-username"></div>

                    <div class="input-box">
                        <input id="registerFirstname" type="text" name="fullname">
                        <label>Họ và tên</label>
                    </div>
                    <div class="error" id="error-fullname"></div>

                    <div class="input-box">
                        <input id="registerEmail" type="email" name="email">
                        <label>Email</label>
                    </div>
                    <div class="error" id="error-email"></div>

                    <div class="input-box">
                        <input id="registerPhone" type="tel" name="phone">
                        <label>SĐT</label>
                    </div>
                    <div class="error" id="error-phone"></div>
                </div>

                <div class="right-input">
                    <div class="input-box">
                        <input id="registerAddress" type="text" name="street">
                        <label>Địa chỉ</label>
                    </div>
                    <div class="error" id="error-street"></div>

                    <div class="input-box">
                        <select id="registerCity" name="city_id">
                            <option value="">Chọn Tỉnh / Thành phố</option>
                        </select>
                    </div>
                    <div class="error" id="error-city"></div>

                    <div class="input-box">
                        <select id="registerWard" name="ward_id">
                            <option value="">Chọn Phường / Xã</option>
                        </select>
                    </div>
                    <div class="error" id="error-ward"></div>

                    <div class="input-box">
                        <input id="registerPassword" type="password" name="password">
                        <label>Mật khẩu</label>
                    </div>
                    <div class="error" id="error-password"></div>

                    <div class="input-box">
                        <input id="registerConfirmPassword" type="password" name="confirm_password">
                        <label>Xác nhận mật khẩu</label>
                    </div>
                    <div class="error" id="error-confirm_password"></div>
                </div>

            </div>

            <button type="submit" class="btn" >Đăng ký</button>
        </form>
    </div>
</div>
