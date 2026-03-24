$(document).ready(function() {
    loadPriceData();

    // 1. Hàm tìm kiếm theo Mã phiếu hoặc Tên sản phẩm
    function performSearch() {
        let value = $("#price-search").val().toLowerCase().trim();
        
        $("#price-data tr").each(function() {
            let receiptId = $(this).find("td:nth-child(1)").text().toLowerCase();
            let productName = $(this).find("td:nth-child(2)").text().toLowerCase();

            if (receiptId.indexOf(value) > -1 || productName.indexOf(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    $("#price-search").on("keyup", performSearch);
    
    $(document).on('click', '.btn-search-price', function(e) {
        e.preventDefault();
        performSearch();
    });

    // 2. Tính toán giá bán khi thay đổi % lợi nhuận
    $(document).on('input', '.input-percent', function() {
        let percent = parseFloat($(this).val()) || 0;
        let costPrice = parseFloat($(this).attr('data-cost')) || 0; 
        
        let sellPrice = costPrice + (costPrice * percent / 100);
        
        // Hiển thị định dạng VNĐ cho Admin dễ nhìn
        let formattedPrice = new Intl.NumberFormat('vi-VN').format(sellPrice) + ' đ';
        $(this).closest('tr').find('.price-suggest').text(formattedPrice);
    });

    // 3. Cập nhật giá bán vào bảng products
    $(document).on('click', '.btn-update', function() {
        let row = $(this).closest('tr');
        let inputPercent = row.find('.input-percent');
        
        // Lấy giá từ cột gợi ý, xóa dấu chấm và chữ 'đ' để gửi SỐ THUẦN về server
        let sellPriceRaw = row.find('.price-suggest').text().replace(/\./g, '').replace(/[^\d]/g, '');

        let data = {
            product_id: inputPercent.data('pid'),
            profit_percent: inputPercent.val(),
            selling_price: parseFloat(sellPriceRaw)
        };

        $.post('Api_php/update-price.php', data, function(res) {
            if(res.status === 'success') {
                alert("Cập nhật thành công!");
                loadPriceData();
            } else {
                alert("Lỗi: " + res.message);
            }
        }, 'json');
    });
});

// 4. Load dữ liệu
function loadPriceData() {
    $.get('Api_php/get-prices.php', function(data) {
        let html = '';
        if (!data || data.length === 0) {
            html = '<tr><td colspan="6">Không có dữ liệu.</td></tr>';
        } else {
            data.forEach(item => {
                let cost = parseFloat(item.cost_price) || 0;
                let profit = parseFloat(item.profit_percent) || 0;
                let sellPrice = cost + (cost * profit / 100);

                html += `
                    <tr>
                        <td><strong>#${item.receipt_id}</strong></td>
                        <td>${item.product_name}</td>
                        <td>${new Intl.NumberFormat('vi-VN').format(cost)} đ</td>
                        <td>
                            <input type="number" class="input-percent" 
                                   data-cost="${cost}" 
                                   data-pid="${item.product_id}" 
                                   value="${profit}" step="0.1">
                        </td>
                        <td class="price-suggest" style="font-weight: bold; color: #d9534f;">
                            ${new Intl.NumberFormat('vi-VN').format(sellPrice)} đ
                        </td>
                        <td>
                            <button class="btn-update btn-save-price" data-id="${item.detail_id}">
                                <ion-icon name="save-outline"></ion-icon> Lưu
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#price-data').html(html);
    }, 'json');
}