document.addEventListener('DOMContentLoaded', function() {
    const btnFilter = document.getElementById('btn-filter');
    const orderList = document.getElementById('order-list-content');

    // 1. Hàm load danh sách đơn hàng
    async function fetchOrders() {
    const from = document.getElementById('from_date').value;
    const to = document.getElementById('to_date').value;
    const status = document.getElementById('filter_status').value;

    // --- KIỂM TRA LOGIC NGÀY THÁNG ---
    if (from !== '' && to !== '') {
        if (new Date(from) > new Date(to)) {
            alert("Lỗi: Ngày bắt đầu không được lớn hơn ngày kết thúc!");
            return; // Dừng hàm, không tải dữ liệu
        }
    }
    // --------------------------------

    orderList.innerHTML = '<div style="text-align:center; padding:50px;">Đang tải đơn hàng...</div>';

    try {
        const response = await fetch(`Controllers/get-orders.php?from=${from}&to=${to}&status=${status}`);
        const html = await response.text();
        orderList.innerHTML = html;
    } catch (error) {
        console.error('Lỗi load đơn hàng:', error);
        orderList.innerHTML = '<div style="color:red; text-align:center;">Lỗi kết nối máy chủ!</div>';
    }
}

    if (btnFilter) {
        btnFilter.addEventListener('click', fetchOrders);
    }

    fetchOrders(); 
    window.reloadOrderTable = fetchOrders;
});

// 2. Cập nhật trạng thái (Khớp với ENUM: PENDING, PROCESSING, COMPLETED)
async function updateStatus(id, newStatus) {
    if (!confirm(`Xác nhận đổi trạng thái đơn hàng #${id}?`)) {
        if (window.reloadOrderTable) window.reloadOrderTable(); 
        return;
    }

    const formData = new FormData();
    formData.append('order_id', id);
    formData.append('status', newStatus);

    try {
        const response = await fetch('Controllers/update-order-status.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            const text = await response.text();
            throw new Error(`HTTP ${response.status}: ${text}`);
        }

        const data = await response.json();
        if (data.success) {
            alert("Cập nhật thành công!");
            if (window.reloadOrderTable) window.reloadOrderTable(); 
        } else {
            alert('Thông báo: ' + data.message);
            if (window.reloadOrderTable) window.reloadOrderTable(); 
        }
    } catch (error) {
        console.error('Lỗi fetch:', error);
        alert('Không thể kết nối đến máy chủ!\n' + error.message);
    }
}

// 3. Hiển thị Modal chi tiết (Khớp với trường dữ liệu ERD)
async function showOrderDetail(orderId) {
    const modal = document.getElementById('orderDetailModal');
    const content = document.getElementById('modal-data-content');
    const orderIdLabel = document.getElementById('md-order-id');

    modal.style.display = 'flex';
    orderIdLabel.innerText = '#' + orderId;
    content.innerHTML = '<div style="text-align:center; padding:30px;">Đang lấy dữ liệu sản phẩm...</div>';

    try {
       
        const response = await fetch(`order-detail.php?id=${orderId}`);
        const html = await response.text();
        content.innerHTML = html;
    } catch (error) {
        content.innerHTML = '<p style="color:red; text-align:center;">Lỗi kết nối máy chủ!</p>';
    }
}

function closeOrderModal() {
    const modal = document.getElementById('orderDetailModal');
    if (modal) modal.style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('orderDetailModal');
    if (event.target == modal) closeOrderModal();
}

document.getElementById('from_date').addEventListener('change', function() {
    const fromDate = this.value;
    const toDateInput = document.getElementById('to_date');
    
    
    toDateInput.min = fromDate; 
    
    if (toDateInput.value && toDateInput.value < fromDate) {
        toDateInput.value = fromDate; 
    }
});