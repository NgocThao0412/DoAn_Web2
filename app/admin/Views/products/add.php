<form id="add-product-form" action="../../Controllers/add-product-process.php" method="post" enctype="multipart/form-data">
    <!-- <div class="add-more">
        <button class="add-category">
            <ion-icon name="add-circle-outline"></ion-icon>
            add more category
        </button>
        <button class="add-status">
            <ion-icon name="add-circle-outline"></ion-icon>
            add more status
        </button>
    </div> -->

    <div class="product-grid">

        <div class="top">
            <!-- <div class="id">
                <div class="product-head">Id</div>
                <div class="product-items"><input type="text"></div>
            </div> -->
            <div class="name">
                <div class="product-head">Tên</div>
                <div class="product-items"><input type="text" name="name" id="name" required></div>
            </div>
            <div class="price">
                <div class="product-head">Giá</div>
                <div class="product-items"><input type="number" name="selling_price" id="selling_price" min="0" required></div>
            </div>
            <div class="status">
                <div class="product-head">Trạng thái</div>
                <div class="product-items">
                <select class="select" name="status" id="status" required>
                    <option value="">--Chọn trạng thái--</option>
                    <option value="AVAILABLE">Hiển thị</option>
                    <option value="HIDDEN">Ẩn</option>
                </select>
                </div>
            </div>

        </div>
                            
        <div class="bottom">
        <div class="category">
    <div class="product-head">Loại sản phẩm</div>
    <div class="product-items">
        <select class="select" name="category_id" id="category_id" required>
            <option value="" selected disabled>-- Chọn loại sản phẩm --</option>
            <?php
            // Kết nối database ngay tại đây hoặc dùng biến $conn đã include ở đầu trang
            include_once '../../config/data_connect.php'; 
            
            $sql_cat = "SELECT * FROM category WHERE status = 1 ORDER BY name ASC";
            $result_cat = $conn->query($sql_cat);

            if ($result_cat->num_rows > 0) {
                while($row_cat = $result_cat->fetch_assoc()) {
                    // category_id là ID để lưu vào bảng products
                    // name là tên hiển thị cho người dùng thấy
                    echo '<option value="' . $row_cat['category_id'] . '">' . $row_cat['name'] . '</option>';
                }
            } else {
                echo '<option value="">Chưa có loại sản phẩm nào</option>';
            }
            ?>
        </select>
    </div>
</div>

<div class="unit">
    <div class="product-head">Đơn vị tính</div>
    <div class="product-items">
        <select class="select" name="unit" id="unit" required>
            <option value="" selected disabled>-- Chọn đơn vị tính --</option>
            <option value="Cái">Cái</option>
            <option value="Hộp">Hộp</option>
            <option value="Ly">Ly</option>
        </select>
    </div>
</div>

            <div class="profit">
                <div class="product-head">Lợi nhuận (%)</div>
                <div class="product-items">
                    <input type="number" name="profit_percent" id="profit_percent" min="0" max="100" step="0.01" required>
                </div>
            </div>
        </div>

        <div class="stock">
            <div class="product-head">Số lượng ban đầu</div>
            <div class="product-items">
                <input type="number" name="current_stock" id="current_stock" min="0" required>
            </div>
        </div>

    </div>
    
    <div class="items-pic-3">
        <div class="items-pic">
            <div class="product-pic">Thêm hình ảnh</div>
            <label class="insert">
                <input type="button" value="Browse..." onclick="document.getElementById('fileInput').click();" />
                <!-- <ion-icon name="cloud-upload-outline"></ion-icon> -->
            </label>
            <input type="text" id="filePath" name="filePath" readonly />
            <input type="file" id="fileInput" name="image" style="display:none;" />
            <div class="product-pic describe">Mô tả</div>
            <div class="write-describe">

                <textarea name="description" id="description" rows="4" cols="145" style="overflow:auto;" required></textarea>
            </div>
        </div>

        <div class="imgPreview">
            <img id="imagePreview" />
        </div>

    </div>



    <div class="button-click">
        <button type="submit" class="save">
            Lưu
            <ion-icon name="save-outline"></ion-icon>
        </button>

        <button  class="cancel" type="reset">
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
            <p>Sản phẩm của bạn đã được thêm thành công. Nếu bạn muốn ở lại, hãy nhấn <span style="color: red;">"đóng"</span> hoặc đi đến trang danh sách sản phẩm bằng cách nhấn <span style="color: blue;">"đi đến danh sách sản phẩm"</span>.</p>

        </div>
        
        <div class="button1">
            <button class="close" id="productForm" type="reset">Đóng</button>
            <button class="go-to"><a href="list-product">Đi đến danh sách sản phẩm</a></button>
        </div>
    </div>

    <div class="cancel-success">
        <div class="icon-suc2">
            <ion-icon name="close-circle-outline"></ion-icon>
        </div>
        <div class="text2">
            <h1>Cảnh báo</h1>
            <p>Bạn có chắc chắn muốn hủy không?</p>
        </div>
        <div class="button2">
            <input type="reset" class="close2" name="yes" id="button1" value="Có" />
            <input type="button" class="close2" name="no" id="button2" value="Không" />
        </div>
    </div>
</form>