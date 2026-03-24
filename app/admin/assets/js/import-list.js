// Hàm load danh sách phiếu nhập khi vừa mở trang
window.onload = function() {
    loadImportList();
};

// 1. Sử dụng addEventListener thay vì window.onload để tránh xung đột với các script khác
document.addEventListener("DOMContentLoaded", function() {
    loadImportList();
});

async function loadImportList(keyword = '') {
    const tbody = document.getElementById('importListBody');
    if (!tbody) return; // Bảo vệ nếu chuyển trang mà script vẫn chạy

    tbody.innerHTML = '<tr><td colspan="6" style="padding: 30px; color: #888;">🔍 Đang tải dữ liệu...</td></tr>';

    try {
        // Đường dẫn chuẩn xác dựa theo cấu trúc thư mục của bạn
        const response = await fetch(`Api_php/get-imports.php?q=${encodeURIComponent(keyword)}`);
        
        // Kiểm tra nếu file không tồn tại (Lỗi 404)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const imports = await response.json();
        tbody.innerHTML = '';

        if (!imports || imports.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="padding: 30px; color: #d9534f;">⚠️ Không tìm thấy phiếu nhập nào.</td></tr>';
            return;
        }

        imports.forEach(item => {
            // Định nghĩa Badge trạng thái xịn hơn
            const isCompleted = (item.status === 'completed');
            const statusBadge = isCompleted 
                ? '<span>Đã chốt</span>' 
                : '<span>Bản nháp</span>';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><b>PN-${item.receipt_id}</b></td>
                <td>${item.import_date}</td>
                <td>${item.supplier_name || '<i style="color:#ccc">Trống</i>'}</td>
                <td style="color: #001f3f; font-weight: bold;">${Number(item.total_amount).toLocaleString('vi-VN')} đ</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn-search" onclick="viewImportDetail(${item.receipt_id})" style="padding: 6px 12px; cursor: pointer;">
                        <ion-icon name="eye-outline" style="vertical-align: middle;"></ion-icon> Xem
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error("Lỗi khi tải danh sách phiếu:", error);
        // Hiển thị lỗi trực quan hơn trên bảng
        tbody.innerHTML = `<tr><td colspan="6" style="padding: 30px; color: #d9534f;">
            <b>❌ Lỗi kết nối máy chủ!</b><br>
        </td></tr>`;
    }
}

// 2. Tìm kiếm danh sách phiếu
function searchImportList() {
    const keyword = document.getElementById('searchImport').value.trim();
    keyword = keyword.replace(/^pn-?/i, '').trim();
    loadImportList(keyword);
}

// Cho phép nhấn Enter trong ô tìm kiếm để thực hiện tìm luôn
document.getElementById('searchImport')?.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        searchImportList();
    }
});

// 3. Chuyển sang trang xem/sửa chi tiết phiếu
function viewImportDetail(id) {
    // Chuyển hướng sang trang create và kèm theo ID để load dữ liệu cũ lên
    window.location.href = `import-add?id=${id}`;
}

// Tìm kiếm danh sách phiếu
function searchImportList() {
    const keyword = document.getElementById('searchImport').value.trim();
    loadImportList(keyword);
}

// Chuyển sang trang xem/sửa chi tiết phiếu
function viewImportDetail(id) {
    // Sẽ truyền ID qua URL để trang kia lấy dữ liệu
    window.location.href = `import-add?id=${id}`;
}