$(document).ready(function() {
    // 1. Lấy ngày hiện tại theo múi giờ Việt Nam
    let now = new Date();
    let year = now.getFullYear();
    let month = String(now.getMonth() + 1).padStart(2, '0');
    let day = String(now.getDate()).padStart(2, '0');

    let today = `${year}-${month}-${day}`;       // Sẽ ra đúng 2026-03-23
    let firstDay = `${year}-${month}-01`;      // Sẽ ra 2026-03-01

    // 2. Gán vào input (Chỉ gán nếu ô đó đang trống)
    if (!$('#date-from').val()) $('#date-from').val(firstDay);
    if (!$('#date-to').val()) $('#date-to').val(today);
    
    // 3. Gọi hàm tải dữ liệu
    loadReportData();

    // Tự động lọc khi thay đổi ngưỡng cảnh báo
    $('#low-stock-threshold').on('input', function() {
        renderTable(); 
    });
    
    // Thêm sự kiện khi người dùng đổi ngày thì tự load lại luôn cho xịn
    $('#date-from, #date-to').on('change', function() {
        loadReportData();
    });
});

let globalReportData = []; // Lưu trữ dữ liệu để render lại nhanh

async function loadReportData() {
    const from = $('#date-from').val();
    const to = $('#date-to').val();
    if (from && to && from > to) {
        alert("Lỗi: Ngày bắt đầu không thể lớn hơn ngày kết thúc!");
        // Tự động chỉnh ngày 'Đến ngày' bằng với 'Từ ngày' cho hợp lệ
        $('#date-to').val(from);
        return;
    }

    // Gọi API lấy dữ liệu nhập xuất tồn
    try {
        const response = await fetch(`Api_php/get-stock-report.php?from=${from}&to=${to}`);
        globalReportData = await response.json();
        renderTable();
    } catch (error) {
        console.error("Lỗi tải báo cáo:", error);
    }
}

function renderTable() {
    const threshold = parseInt($('#low-stock-threshold').val()) || 0;
    let html = '';

    globalReportData.forEach(item => {
        // Tồn cuối = Tồn đầu + Nhập - Xuất
        let ton_cuoi = item.ton_dau + item.nhap_trong_ky - item.xuat_trong_ky;
        
        // Kiểm tra cảnh báo hết hàng
        let statusHtml = ton_cuoi <= threshold 
            ? `<span class="status-warning"><ion-icon name="alert-circle"></ion-icon> Sắp hết hàng</span>` 
            : `<span class="status-ok">Ổn định</span>`;

        html += `
            <tr>
                <td style="text-align: left;">${item.product_name}</td>
                <td>${item.ton_dau}</td>
                <td style="color: #28a745; font-weight: bold;">+${item.nhap_trong_ky}</td>
                <td style="color: #d9534f; font-weight: bold;">-${item.xuat_trong_ky}</td>
                <td style="font-size: 1.1em; font-weight: bold;">${ton_cuoi}</td>
                <td>${statusHtml}</td>
            </tr>
        `;
    });

    $('#report-data-body').html(html || '<tr><td colspan="6">Không có dữ liệu trong khoảng thời gian này.</td></tr>');
}