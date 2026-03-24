<!-- footer.php -->
<?php

// include __DIR__ . '/../app/config/config.php';

echo '
<div class="footer">
    <div class="Footer_footer_contain">
        <div class="footer_section ">
            <h3 class="Footer_footer_title ">GIỚI THIỆU</h3>
            <ul>
             <a href="about"><li><button class="Foot_footer_list ">Về Chúng Tôi</button></li></a>
                <a href="about"><li><button class="Foot_footer_list ">Liên hệ</button></li></a>
                <a href="about"><li><button class="Foot_footer_list ">Quy Chế Hoạt Động</button></li></a>
            </ul>
        </div>
        <div class="footer_section ">
            <h3 class="Footer_footer_title ">THỰC ĐƠN</h3>
            <ul>
            <a href="macaron"><li><button class="Foot_footer_list ">Macaron</button></li></a>
            <a href="croissant"><li><button class="Foot_footer_list ">Bánh Sừng Bò</button></li></a>
            <a href="drink"><li><button class="Foot_footer_list ">Đồ Uống</button></li></a>
            </ul>
        </div>
        <div class="footer_section ">
            <h3 class="Footer_footer_title ">HỖ TRỢ</h3>
            <ul>
                <li><button class="Foot_footer_list ">Góp Ý</button></li>
                <li><button class="Foot_footer_list ">Tuyển Dụng</button></li>
                <li><button class="Foot_footer_list ">FAQ</button></li>
            </ul>
        </div>
        <div class="footer_section">
            <div class="footer_logo">
                <img src="' . BASE_URL . 'Img/golden_crumb.png" alt="">
            </div>
            <ul class="Footer_connect_icon ">
                <li><a href="#"><ion-icon name="logo-youtube"></ion-icon></a></li>
                <li><a href="#"><ion-icon name="logo-facebook"></ion-icon></a></li>
                <li><a href="#"><ion-icon name="logo-instagram"></ion-icon></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom-line"></div>
    <div class="copy-right">
        <p>© copyright 2026 by The GOLDEN Group | Công ty TNHH Golden Crumb | Design by The MESince </p>
    </div>
</div>

<button id="backToTop" onclick="scrollToTop()">
    <ion-icon name="chevron-up-outline"></ion-icon>
</button>';
?>