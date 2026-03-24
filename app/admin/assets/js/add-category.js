/**
 * Hàm load bảng danh sách danh mục từ Database
 * Đặt ngoài DOMContentLoaded để có thể gọi từ bất cứ đâu
 */
function loadCategoryData() {
    const tableContent = document.getElementById('category-table-content');
    if (!tableContent) return;

    tableContent.innerHTML = '<p style="text-align:center; padding:10px;">Đang tải dữ liệu...</p>';
    
    // Sử dụng ../ để lùi từ thư mục assets/js ra admin/ rồi vào Controllers
    fetch("Controllers/get-category.php") 
    .then(response => {
        if (!response.ok) throw new Error('Không tìm thấy file xử lý (404)');
        return response.text();
    })
    .then(html => {
        tableContent.innerHTML = html;
    })
    .catch(error => {
        console.error("Lỗi:", error);
        tableContent.innerHTML = '<p style="color:red; text-align:center;">Lỗi: ' + error.message + '</p>';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Khai báo các phần tử UI
    const form = document.getElementById('add-category-form');
    const save_suc = document.querySelector('.save-success');
    const blurOverlay = document.querySelector('.blur-overlay');
    const closeBtn = document.querySelector('.close');
    const btnShowList = document.getElementById('btn-show-list');
    const listArea = document.getElementById('category-list-under');

    /**
     * 1. XỬ LÝ SUBMIT FORM (THÊM DANH MỤC)
     */
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch("Controllers/add-category-process.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    save_suc.classList.add('active-popup');
                    blurOverlay.classList.add('active');
                    form.reset();
                } else {
                    alert("Thất bại: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Lỗi kết nối server.");
            });
        });
    }

    /**
     * 2. XỬ LÝ NÚT ĐÓNG (RELOAD VÀ ĐÁNH DẤU MỞ DANH SÁCH)
     */
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            // Lưu trạng thái vào bộ nhớ trình duyệt trước khi reload
            localStorage.setItem('showCategoryListAfterReload', 'true');
            window.location.reload();
        });
    }

    /**
     * 3. TỰ ĐỘNG MỞ DANH SÁCH SAU KHI RELOAD
     */
    if (localStorage.getItem('showCategoryListAfterReload') === 'true') {
        if (listArea && btnShowList) {
            loadCategoryData();
            listArea.style.display = "block";
            btnShowList.style.background = "#c52525";
            btnShowList.innerHTML = '<ion-icon name="chevron-up-outline"></ion-icon> Đóng danh sách';
        }
        // Xóa dấu để lần sau vào trang không bị tự mở
        localStorage.removeItem('showCategoryListAfterReload');
    }

    /**
     * 4. XỬ LÝ NÚT BẤM "XEM DANH SÁCH" (TOGGLE)
     */
    if (btnShowList) {
        btnShowList.addEventListener('click', function() {
            if (listArea.style.display === "none" || listArea.style.display === "") {
                loadCategoryData();
                listArea.style.display = "block";
                this.style.background = "#c52525";
                this.innerHTML = '<ion-icon name="chevron-up-outline"></ion-icon> Đóng danh sách';
            } else {
                listArea.style.display = "none";
                this.style.background = "#4e499e";
                this.innerHTML = '<ion-icon name="list-outline"></ion-icon> Xem danh sách danh mục đã có';
            }
        });
    }
});

/**
 * 5. XỬ LÝ SỬA TRẠNG THÁI (CLICK VÀO NÚT TRONG BẢNG)
 * Dùng Event Delegation (lắng nghe từ document) vì bảng được load động
 */
document.addEventListener('click', function(e) {
    // Kiểm tra xem phần tử bị click có class 'update-status-btn' không
    if (e.target && e.target.classList.contains('update-status-btn')) {
        const catId = e.target.getAttribute('data-id');
        const currentStatus = e.target.getAttribute('data-status');

        if (confirm("Bạn có muốn thay đổi trạng thái loại sản phẩm này không?")) {
            const formData = new FormData();
            formData.append('id', catId);
            formData.append('current_status', currentStatus);

            fetch("Controllers/update-category-status.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Gọi hàm load lại bảng ngay lập tức để thấy thay đổi
                    loadCategoryData(); 
                } else {
                    alert("Lỗi cập nhật: " + data.message);
                }
            })
            .catch(err => {
                console.error("Lỗi fetch:", err);
                alert("Không thể kết nối đến file update-category-status.php");
            });
        }
    }
});