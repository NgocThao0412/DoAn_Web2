let allPriceData = [];
let currentPage = 1;  
const rowsPerPage = 6;

$(document).ready(function() {
    loadPriceData();

    // 1. Hàm tìm kiếm
    function performSearch() {
        let value = $("#price-search").val().toLowerCase().trim();
        const filteredData = allPriceData.filter(item => 
            item.receipt_id.toString().toLowerCase().includes(value) || 
            item.product_name.toLowerCase().includes(value)
        );
        currentPage = 1; 
        renderTable(filteredData);
    }
    $("#price-search").on("keyup", performSearch);

    // CHỈNH SỬA TẠI ĐÂY: Chặn phím dấu trừ (-) ngay khi gõ
    $(document).on('keypress', '.input-percent', function(e) {
        if (e.which === 45) { // Mã phím của dấu '-'
            e.preventDefault();
        }
    });

    // 2. Tính toán giá bán và CHẶN SỐ ÂM khi thay đổi % lợi nhuận
    $(document).on('input', '.input-percent', function() {
        let input = $(this);
        let val = input.val();

        // Nếu cố tình nhập số âm hoặc dán số âm vào, ép về 0
        if (parseFloat(val) < 0) {
            input.val(0);
            val = 0;
        }

        let percent = parseFloat(val) || 0;
        let costPrice = parseFloat(input.attr('data-cost')) || 0; 
        let sellPrice = costPrice + (costPrice * percent / 100);
        let formattedPrice = new Intl.NumberFormat('vi-VN').format(sellPrice) + ' VNĐ';
        input.closest('tr').find('.price-suggest').text(formattedPrice);
    });

    // 3. Xử lý nút THAO TÁC
    $(document).on('click', '.btn-update', function() {
        let btn = $(this);
        let row = btn.closest('tr');
        let inputPercent = row.find('.input-percent');

        if (!btn.hasClass('is-editing')) {
            inputPercent.prop('disabled', false).focus();
            inputPercent.css('border', '1px solid #ff69b4');
            btn.addClass('is-editing').html('<ion-icon name="save-outline"></ion-icon> Lưu');
            btn.css('background-color', '#28a745');
        } else {
            // KIỂM TRA CUỐI CÙNG TRƯỚC KHI LƯU
            let currentVal = parseFloat(inputPercent.val());
            if (isNaN(currentVal) || currentVal < 0) {
                alert("Lợi nhuận không được để trống hoặc nhỏ hơn 0!");
                inputPercent.focus();
                return; // Dừng lại không gửi dữ liệu
            }

            let sellPriceRaw = row.find('.price-suggest').text().replace(/\./g, '').replace(/[^\d]/g, '');
            let data = {
                product_id: inputPercent.data('pid'),
                profit_percent: currentVal,
                selling_price: parseFloat(sellPriceRaw)
            };

            $.post('Api_php/update-price.php', data, function(res) {
                if(res.status === 'success') {
                    alert("Cập nhật giá thành công!");
                    inputPercent.prop('disabled', true).css('border', '1px solid #ddd');
                    btn.removeClass('is-editing').html('<ion-icon name="create-outline"></ion-icon> Sửa');
                    btn.css('background-color', '#6c757d');
                    
                    let itemIndex = allPriceData.findIndex(i => i.product_id == data.product_id);
                    if(itemIndex > -1) {
                        allPriceData[itemIndex].profit_percent = data.profit_percent;
                        allPriceData[itemIndex].selling_price = data.selling_price;
                    }
                } else {
                    alert("Lỗi: " + res.message);
                }
            }, 'json');
        }
    });

    $(document).on('click', '.page-btn', function() {
        currentPage = $(this).data('page');
        renderTable(allPriceData);
    });
});

function loadPriceData() {
    $.get('Api_php/get-prices.php', function(data) {
        if (!data || data.length === 0) {
            $('#price-data').html('<tr><td colspan="6">Không có dữ liệu.</td></tr>');
        } else {
            allPriceData = data;
            renderTable(allPriceData);
        }
    }, 'json');
}

// 4. Render bảng (Đã thêm thuộc tính min="0")
function renderTable(dataList) {
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const currentRows = dataList.slice(start, end);

    let html = '';
    currentRows.forEach(item => {
        let cost = parseFloat(item.cost_price) || 0;
        let profit = parseFloat(item.profit_percent) || 0;
        let sellPrice = cost + (cost * profit / 100);

        html += `
            <tr>
                <td><strong>#${item.receipt_id}</strong></td>
                <td>${item.product_name}</td>
                <td>${new Intl.NumberFormat('vi-VN').format(cost)} VNĐ</td>
                <td>
                    <input type="number" class="input-percent" 
                           data-cost="${cost}" data-pid="${item.product_id}" 
                           value="${profit}" step="0.1" min="0" disabled> 
                </td>
                <td class="price-suggest" style="font-weight: bold; color: #d9534f;">
                    ${new Intl.NumberFormat('vi-VN').format(sellPrice)} VNĐ
                </td>
                <td>
                    <button class="btn-update btn-save-price" 
                            style="background-color: #6c757d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">
                        <ion-icon name="create-outline"></ion-icon> Sửa
                    </button>
                </td>
            </tr>`;
    });
    $('#price-data').html(html);

    let totalPages = Math.ceil(dataList.length / rowsPerPage);
    let paginationHtml = '';
    if (totalPages > 1) {
        let prevDisabled = (currentPage === 1) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
        paginationHtml += `<button class="page-btn prev-btn" data-page="${currentPage - 1}" ${prevDisabled}><ion-icon name="chevron-back-outline"></ion-icon></button>`;

        for (let i = 1; i <= totalPages; i++) {
            let activeClass = (i === currentPage) ? 'active-page' : '';
            paginationHtml += `<button class="page-btn ${activeClass}" data-page="${i}">${i}</button>`;
        }

        let nextDisabled = (currentPage === totalPages) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
        paginationHtml += `<button class="page-btn next-btn" data-page="${currentPage + 1}" ${nextDisabled}><ion-icon name="chevron-forward-outline"></ion-icon></button>`;
    }
    $('#pagination-container').html(paginationHtml);
}