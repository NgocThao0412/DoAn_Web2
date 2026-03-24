document.addEventListener('DOMContentLoaded', function() {
    const btnFilter = document.getElementById('btn-filter');
    const orderList = document.getElementById('order-list-content');

    // 1. Hàm load danh sách đơn hàng
    async function fetchOrders() {
        const from = document.getElementById('from_date').value;
        const to = document.getElementById('to_date').value;
        const status = document.getElementById('filter_status').value;

        orderList.innerHTML = '<div style="text-align:center; padding:50px;">Đang tải đơn hàng...</div>';

        try {
            // Đảm bảo file get-orders.php đã có ORDER BY shipping_ward
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

    fetchOrders(); // Tự động load lần đầu
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
        
        const data = await response.json();
        if (data.success) {
            alert("Cập nhật thành công!");
            if (window.reloadOrderTable) window.reloadOrderTable(); 
        } else {
            alert('Lỗi: ' + data.message);
        }
    } catch (error) {
        console.error('Lỗi fetch:', error);
        alert('Không thể kết nối đến máy chủ!');
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
        // File này sẽ lấy recipient_name, shipping_ward, unit, price...
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