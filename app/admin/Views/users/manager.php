<div class="add-more">
    <button class="add-user" onclick="showAddUserForm()">
        <ion-icon name="add-circle-outline"></ion-icon>
        Thêm người dùng
    </button>
    <div class="find-user">
        <input class="find" type="text" placeholder="Tìm kiếm người dùng"  />
        <button class="search" type="button" >
            Tìm kiếm
        </button>
    </div>

</div>


<div id="userTableContainer">
    <!-- Phần table sẽ được load vào đây từ manager_processing.php -->
</div>
<div id="paginationContainer" class="pagination"></div>

    <!-- Modal for adding/editing users -->
<div id="userModal">
    <div id="userFormContainer">
        <h2 style="text-align: center; margin-bottom: 30px" id="modalTitle">Thêm người dùng mới</h2>
        <div class="form-grid">
            <div class="form-group">
                <label for="username">Tên:</label>
                <input type="text" id="username" name="username" placeholder="Điền tên" />
            </div>
            <div class="form-group">
                <label for="fullname">Họ và tên:</label>
                <input type="text" id="fullname" name="fullname" placeholder="Điền họ và tên" />
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" placeholder="Điền mật khẩu" />
            </div>
            <div class="form-group" id="confirm_password_group">
                <label for="confirm_password">Xác nhận Mật khẩu:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" />
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Điền email" />
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại:</label>
                <input type="tel" id="phone" name="phone" placeholder="Điền số điện thoại" />
            </div>
            <div class="form-group">
                <label for="city">Thành phố:</label>
                <select id="city" name="city" onchange="loadWardsFromCity(this.value)">
                    <option value="">Chọn thành phố</option>
                </select>
            </div>
            <div class="form-group">
                <label for="ward">Phường/Xã:</label>
                <select id="ward" name="ward" >
                    <option value="">Chọn phường/xã</option>
                </select>
            </div>

            <div class="form-group">
                <label for="street">Đường:</label>
                <input type="text" id="street" name="street" placeholder="Điền đường" />
            </div>
            <div class="form-group">
                <label for="role">Vai trò:</label>
                <select id="role" disabled>
                    <option value="admin">admin</option>
                    <option value="customer">customer</option>
                </select>
                <input type="hidden" name="role" id="role_hidden">
            </div>
            

        </div>
        <div class="modal-buttons">
            <button class="save-btn" onclick="saveUser()">Lưu</button>
            <button class="close-btn" onclick="closeModal()">Hủy</button>
        </div>
    </div>
</div>



<!-- Confirmation Modal for Lock -->
<div id="confirmLockModal" style="display: none">
    <div class="modal-content">
    <div class="modal-header">
        <span class="warning-icon">&#10060;</span>
    </div>
    <h2>Cảnh báo</h2>
    <p>Bạn có chắc chắn muốn khóa/mở khóa người dùng này không?</p>
    <div class="modal-buttons">
        <button id="confirmLockBtn" class="yes-button">Có</button> 
        <button class="no-button" onclick="closeConfirmModal()">Không</button>
    </div>
    </div>
</div>