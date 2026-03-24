<link rel="stylesheet" href="assets/css/style.css">
<div class="right-screen">
    <div class="Function-use">

        <!-- Logo -->
        <button class="logo" style="border: none; background: none; cursor:pointer">
            <img src="../../public/assets/Img/golden_crumb.png" alt="Logo">
        </button>

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
</div>