<div class="warning-container">
    <div class="warning-toolbar">
        <div class="input-group">
            <label>Ngưỡng cảnh báo (Tồn kho <=):</label>
            <input type="number" id="alert-threshold" value="10" min="1">
            <button class="btn-refresh" onclick="loadWarningData()">
                <ion-icon name="refresh-outline"></ion-icon> Làm mới
            </button>
        </div>
        <div class="summary-info">
            Có <span id="low-stock-count">0</span> sản phẩm cần nhập thêm hàng.
        </div>
    </div>

    <div class="table-wrapper">
        <table class="warning-table">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Tồn kho hiện tại</th>
                    <th>Đơn vị</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody id="warning-data-body">
                </tbody>
        </table>
    </div>
</div>