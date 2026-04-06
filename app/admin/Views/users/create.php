<div class="import-container">
    <input type="hidden" id="import_id" value="">

    <h4 class="section-title">1. Thông tin nhà cung cấp</h4>
    <div class="supplier-info-box">
        <div class="form-group">
            <label>Tên nhà cung cấp:</label>
            <input type="text" id="supplier_name" placeholder="VD: Công ty TNHH Bột mì ABC...">
        </div>
        <div class="form-group">
            <label>Số điện thoại:</label>
            <input type="text" id="supplier_phone" placeholder="Nhập SĐT...">
        </div>
        <div class="form-group">
            <label>Địa chỉ chi tiết:</label>
            <input type="text" id="supplier_address" placeholder="Nhập địa chỉ chi tiết...">
        </div>
    </div>

    <h4 class="section-title">2. Chi tiết sản phẩm nhập</h4>
    <div class="search-product-section">
        <input type="text" id="searchProductInput" class="search-product-input" placeholder="🔍 Gõ tên sản phẩm để tìm và thêm vào phiếu..." oninput="searchProductAPI()">
        <div id="productSuggestBox" class="suggest-box"></div>
    </div>

    <table class="table-custom">
        <thead>
            <tr>
                <th>Tên sản phẩm</th>
                <th style="width: 15%;">Số lượng</th>
                <th style="width: 20%;">Giá nhập (VNĐ)</th>
                <th style="width: 20%;">Thành tiền</th>
                <th style="width: 10%;">Xóa</th>
            </tr>
        </thead>
        <tbody id="importDetailBody">
            <tr>
                <td colspan="5" style="padding: 30px; color: #888;">Chưa có sản phẩm nào. Hãy tìm kiếm ở trên!</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="total-label">Tổng cộng:</td>
                <td colspan="2" id="totalImportAmount" class="total-amount">0 VNĐ</td>
            </tr>
        </tfoot>
    </table>

    <div class="action-buttons">
        <button onclick="saveImport('draft')" class="btn-draft">Lưu nháp</button>
        <button onclick="saveImport('completed')" class="btn-complete">Chốt phiếu nhập</button>
    </div>
</div>