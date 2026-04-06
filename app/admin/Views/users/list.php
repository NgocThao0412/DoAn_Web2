<div class="toolbar">
    <a href="import-add" class="btn-add">
        <ion-icon name="add-circle-outline"></ion-icon> Tạo phiếu mới
    </a>
    
    <div class="search-box">
        <input type="text" id="searchImport" class="search-input" placeholder="Tìm theo mã hoặc NCC...">
        <button onclick="searchImportList()" class="btn-search">Tìm kiếm</button>
    </div>
</div>

<div id="importTableContainer" class="import-container" style="padding: 0; overflow: hidden;">
    <table class="table-custom" style="margin-bottom: 0; border-style: hidden;">
        <thead>
            <tr>
                <th>Mã phiếu</th>
                <th>Ngày lập</th>
                <th>Nhà cung cấp</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody id="importListBody">
            <tr>
                <td colspan="6" style="padding: 30px; color: #888;">Đang tải dữ liệu...</td>
            </tr>
        </tbody>
    </table>
</div>