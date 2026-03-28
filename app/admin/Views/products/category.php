<form id="add-category-form" action="../../Controllers/add-category-process.php" method="post">

    <div class="top-actions" style="margin: 10px 20px 20px 20px; display: flex; justify-content: flex-start;">
        <button type="button" id="btn-show-list" style="background: #4e499e; color: white; display: flex; align-items: center; gap: 8px; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
            <ion-icon name="list-outline"></ion-icon>
            Xem danh sách loại sản phẩm đã có
        </button>
    </div>

    <div id="category-list-under" style="display: none; margin: 0 20px 20px 20px; background: #fff; border-radius: 10px; padding: 15px; border: 1px solid #4e499e;">
        <div id="category-table-content">
            </div>
    </div>

    <div class="product-grid">
        <div class="top">
            <div class="name" style="width: 66%;">
                <div class="product-head">Tên loại sản phẩm</div>
                <div class="product-items">
                    <input type="text" name="cat_name" id="cat_name" placeholder="Ví dụ: Bánh kem, Kẹo..." required>
                </div>
            </div>

            <div class="status" style="width: 33%;">
                <div class="product-head">Trạng thái</div>
                <div class="product-items">
                    <select class="select" name="cat_status" id="cat_status" required>
                        <option value="1">Hiển thị</option>
                        <option value="0">Ẩn</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="button-click" style="margin-top: 30px;">
        <button type="submit" class="save">
            Lưu
            <ion-icon name="save-outline"></ion-icon>
        </button>

        <button class="cancel" type="reset">
            Hủy
            <ion-icon name="close-outline"></ion-icon>
        </button>
    </div>

    <div class="save-success">
        <div class="icon-suc">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
        </div>
        <div class="text">
            <h1>Thành công</h1>
            <p>Danh mục đã được tạo. Bạn có thể dùng danh mục này khi thêm sản phẩm mới.</p>
        </div>
        <div class="button1">
            <button class="close" type="reset">Đóng</button>
        </div>
    </div>

</form>