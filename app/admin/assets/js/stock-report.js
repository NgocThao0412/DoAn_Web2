$(document).ready(function() {
    // 1. Lấy ngày hiện tại theo múi giờ Việt Nam
    let now = new Date();
    let year = now.getFullYear();
    let month = String(now.getMonth() + 1).padStart(2, '0');
    let day = String(now.getDate()).padStart(2, '0');

    let today = `${year}-${month}-${day}`; 
    let firstDay = `${year}-${month}-01`;

    // 2. Gán vào input ngày (Chỉ gán nếu ô đó đang trống)
    if (!$('#date-from').val()) $('#date-from').val(firstDay);
    if (!$('#date-to').val()) $('#date-to').val(today);
    
    // 3. Gọi hàm tải dữ liệu ngay khi load trang
    loadReportData();

    // Tự động render lại khi thay đổi ngưỡng cảnh báo số lượng
    $('#low-stock-threshold').on('input', function() {
        renderTable(); 
    });
    
    // Tự động load lại khi người dùng thay đổi khoảng ngày
    $('#date-from, #date-to').on('change', function() {
        loadReportData();
    });
});

let globalReportData = []; // Biến lưu trữ dữ liệu tạm thời

async function loadReportData() {
    const from = $('#date-from').val();
    const to = $('#date-to').val();

    // Kiểm tra tính hợp lệ của ngày
    if (from && to && from > to) {
        alert("Lỗi: Ngày bắt đầu không thể lớn hơn ngày kết thúc!");
        $('#date-to').val(from);
        return;
    }

    try {
        // Gọi API lấy dữ liệu từ PHP
        const response = await fetch(`Api_php/get-stock-report.php?from=${from}&to=${to}`);
        globalReportData = await response.json();
        renderTable();
    } catch (error) {
        console.error("Lỗi tải báo cáo:", error);
    }
}

function renderTable() {
    // Lấy ngưỡng cảnh báo từ ô input (ví dụ: 10)
    const threshold = parseInt($('#low-stock-threshold').val()) || 0;
    let html = '';

    if (!globalReportData || globalReportData.length === 0) {
        $('#report-data-body').html('<tr><td colspan="6" style="padding: 20px;">Không có dữ liệu trong khoảng thời gian này.</td></tr>');
        return;
    }

    globalReportData.forEach(item => {
        // XỬ LÝ LOGIC: Chặn số âm cho Tồn đầu kỳ
        let ton_dau_sach = Math.max(0, parseInt(item.ton_dau) || 0);
        
        // Tính tồn cuối kỳ thực tế
        let ton_cuoi = ton_dau_sach + (parseInt(item.nhap_trong_ky) || 0) - (parseInt(item.xuat_trong_ky) || 0);
        
        // XỬ LÝ LOGIC: Chặn số âm cho Tồn cuối kỳ
        let ton_cuoi_sach = Math.max(0, ton_cuoi);
        
        // LOGIC CẢNH BÁO: Nếu Tồn cuối kỳ <= ngưỡng thì mới báo "Sắp hết hàng"
        let isLowStock = ton_cuoi_sach <= threshold;
        
        let statusHtml = isLowStock 
            ? `<span class="status-warning" style="color: #d9534f; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 5px;">
                <ion-icon name="alert-circle"></ion-icon> Sắp hết hàng
               </span>` 
            : `<span class="status-ok" style="color: #28a745; font-weight: bold;">Ổn định</span>`;

        // Render từng dòng với style in đậm (font-weight) cho các con số
        html += `
            <tr>
                <td style="text-align: left; font-weight: 600; padding-left: 15px;">${item.product_name}</td>
                <td style="font-weight: bold;">${ton_dau_sach}</td> 
                <td style="font-weight: bold;">${item.nhap_trong_ky}</td>
                <td style="font-weight: bold;">${item.xuat_trong_ky}</td>
                <td style="font-weight: 900; font-size: 1.1em; color: ${isLowStock ? '#d9534f' : '#333'};">
                    ${ton_cuoi_sach}
                </td>
                <td>${statusHtml}</td>
            </tr>
        `;
    });

    $('#report-data-body').html(html);
}