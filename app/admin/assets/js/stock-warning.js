$(document).ready(function() {
    // 1. Gọi ngay lập tức khi trang vừa load xong
    loadWarningData();

    // 2. Tự động load lại khi admin gõ số thay đổi ngưỡng
    // Dùng sự kiện 'input' để gõ đến đâu nhảy đến đó
    $('#alert-threshold').on('input', function() {
        loadWarningData();
    });

    // 3. Nút làm mới (vẫn giữ để admin bấm nếu muốn reload thủ công)
    $('.btn-refresh').on('click', function(e) {
        e.preventDefault();
        loadWarningData();
    });
});

async function loadWarningData() {
    // Thêm giá trị mặc định là 10 nếu ô input bị trống
    let threshold = $('#alert-threshold').val();
    if (threshold === '' || threshold === undefined) {
        threshold = 10;
        $('#alert-threshold').val(10); // Điền sẵn số 10 vào ô cho đẹp
    }

    try {
        const response = await fetch(`Api_php/get-stock-warning.php?threshold=${threshold}`);
        const data = await response.json();

        let html = '';
        let count = 0;

        if (data && data.length > 0) {
            data.forEach(item => {
                count++;
                // Chặn số âm hiển thị
                let displayStock = Math.max(0, item.current_stock);
                
                html += `
                    <tr>
                        <td><img src="../../${item.image}" 
                            class="img-product-warning" 
                            style="width:50px; height:50px; object-fit:cover;" 
                            alt="sp"></td>
                        <td style="text-align: left;">${item.name}</td>
                        <td>${item.category_name}</td>
                        <td class="stock-red" style="color: #d9534f; font-weight: bold;">${displayStock}</td>
                        <td>${item.unit}</td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="5" style="padding:30px; color:#888;">Tuyệt vời! Không có sản phẩm nào dưới ngưỡng báo động.</td></tr>';
        }

        $('#warning-data-body').html(html);
        $('#low-stock-count').text(count);

    } catch (error) {
        console.error("Lỗi tải dữ liệu cảnh báo:", error);
        $('#warning-data-body').html('<tr><td colspan="5" style="color: red;">Không thể kết nối với máy chủ.</td></tr>');
    }
}