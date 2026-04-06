/**
 * Hàm load bảng danh sách danh mục từ Database
 */
function loadCategoryData() {
    const tableContent = document.getElementById('category-table-content');
    if (!tableContent) return;

    tableContent.innerHTML = '<p style="text-align:center; padding:10px;">Đang tải dữ liệu...</p>';
    
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
     * 2. XỬ LÝ NÚT ĐÓNG POPUP THÀNH CÔNG
     */
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            // Sau khi thêm thành công, tự động mở danh sách để xem kết quả
            localStorage.setItem('showCategoryListAfterReload', 'true');
            window.location.reload();
        });
    }

    /**
     * 3. TỰ ĐỘNG MỞ DANH SÁCH SAU KHI THÊM
     */
    if (localStorage.getItem('showCategoryListAfterReload') === 'true') {
        if (listArea && btnShowList) {
            loadCategoryData();
            listArea.style.display = "block";
            btnShowList.style.background = "#c52525";
            btnShowList.innerHTML = '<ion-icon name="chevron-up-outline"></ion-icon> Đóng danh sách';
        }
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
                this.innerHTML = '<ion-icon name="list-outline"></ion-icon> Xem danh sách loại sản phẩm đã có';
            }
        });
    }
});