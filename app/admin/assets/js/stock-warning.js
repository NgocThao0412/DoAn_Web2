$(document).ready(function() {
    loadWarningData();

    // Khi thay đổi ngưỡng, tự động lọc lại danh sách
    $('#alert-threshold').on('input', function() {
        loadWarningData();
    });
});

async function loadWarningData() {
    const threshold = $('#alert-threshold').val() || 10;

    try {
        // Gọi API lấy danh sách sản phẩm sắp hết hàng
        const response = await fetch(`Api_php/get-stock-warning.php?threshold=${threshold}`);
        const data = await response.json();

        let html = '';
        let count = 0;

        if (data.length > 0) {
            data.forEach(item => {
                count++;
                html += `
                    <tr>
                        <td><img src="../../${item.image}" 
                            class="img-product-warning" 
                            style="width:50px; height:50px; object-fit:cover;" 
                            alt="sp"></td>
                        <td style="text-align: left;">${item.name}</td>
                        <td>${item.category_name}</td>
                        <td class="stock-red">${item.current_stock}</td>
                        <td>${item.unit}</td>
                        <td>
                            <a href="import-add" class="btn-import-now">
                                <ion-icon name="cart-outline"></ion-icon> Nhập hàng ngay
                            </a>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="6" style="padding:30px; color:#888;">Tuyệt vời! Không có sản phẩm nào dưới ngưỡng báo động.</td></tr>';
        }

        $('#warning-data-body').html(html);
        $('#low-stock-count').text(count);

    } catch (error) {
        console.error("Lỗi tải dữ liệu cảnh báo:", error);
    }
}