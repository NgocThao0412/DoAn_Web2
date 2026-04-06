<div class="report-container">
    <div class="report-toolbar">
        <div class="filter-group">
            <label>Từ ngày:</label>
            <input type="date" id="date-from">
        </div>
        <div class="filter-group">
            <label>Đến ngày:</label>
            <input type="date" id="date-to">
        </div>
        <div class="filter-group">
            <label>Cảnh báo tồn < (số lượng):</label>
            <input type="number" id="low-stock-threshold" value="10" min="1">
        </div>
        <button class="btn-filter" onclick="loadReportData()">
            <ion-icon name="filter-outline"></ion-icon> Lọc báo cáo
        </button>
    </div>

    <div class="table-wrapper">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Tồn đầu kỳ</th>
                    <th>Nhập trong kỳ</th>
                    <th>Xuất trong kỳ</th>
                    <th>Tồn cuối kỳ</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody id="report-data-body">
                </tbody>
        </table>
    </div>
</div>