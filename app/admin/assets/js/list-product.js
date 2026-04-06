/* ==========================================
   1. XỬ LÝ ẢNH (PREVIEW & THAY ĐỔI)
   ========================================== */

// Xử lý khi chọn ảnh mới từ máy tính
function handleFileChange(inputElement) {
    const file = inputElement.files[0];
    const categorySelect = document.getElementById("products_category");
    const categoryName = categorySelect.options[categorySelect.selectedIndex].text.trim().replace(/\s+/g, '');
    
    if (file) {
        const previewPath = document.getElementById('preview-path');
        if (previewPath) {
            previewPath.value = `/assets/Img/${categoryName}/${file.name}`;
        }

        const imgPreview = document.getElementById('current_product_image');
        if (imgPreview) {
            imgPreview.src = URL.createObjectURL(file);
        }
        // Reset cờ xóa ảnh nếu người dùng chọn ảnh mới
        const flag = document.getElementById('remove_photo_flag');
        if (flag) flag.value = "0";
    }
}

// Chức năng xóa hình ảnh về ảnh mặc định (Giữ nguyên cho bạn)
function removeCurrentPhoto() {
    if (confirm("Bạn có muốn xóa ảnh này và dùng ảnh mặc định không?")) {
        const img = document.getElementById('current_product_image');
        const flag = document.getElementById('remove_photo_flag');
        const previewPath = document.getElementById('preview-path');

        if (img) {
            img.src = "../../public/assets/Img/default.png"; 
        }
        
        if (flag) flag.value = "1";

        if (previewPath) {
            previewPath.value = "Sử dụng ảnh mặc định";
        }
    }
}

/* ==========================================
   2. QUẢN LÝ ADMIN & SESSION
   ========================================== */

document.addEventListener('DOMContentLoaded', function() {
    function checkLoginStatus(callback) {
        fetch("Api_php/session-admin.php", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                window.location.href = "login";
                return;
            }
            if (data.status && data.status.toLowerCase() === "locked") {
                alert("Your account has been locked.");
                window.location.href = "login";
                return;
            }
            if (callback) callback(data.loggedIn);
        })
        .catch(error => console.error("Lỗi session:", error));
    }    

    checkLoginStatus((isLoggedIn) => {
        if (!isLoggedIn) localStorage.removeItem("welcomeShownAdmin");
    });

    function getCurrentUser() {
        const admins = localStorage.getItem('AdminUser');
        return admins ? JSON.parse(admins) : null;
    }

    // Hiển thị thông báo Welcome
    if (localStorage.getItem("loggedAd") === "true") {
        const currentUser = getCurrentUser();
        const notificate = document.getElementById("notificate");
        const message = document.getElementById("message");
    
        if (currentUser && notificate) {
            message.innerHTML = `Welcome back, ${currentUser.username}!<br>Have a good day!`;
            notificate.classList.add("show");
            setTimeout(() => {
                notificate.classList.remove("show");
                notificate.classList.add("hide");
                setTimeout(() => { notificate.style.display = "none"; }, 2000);
            }, 3000);
        }
        localStorage.removeItem("loggedAd");
    }

    // Nút Logout
    const logoutButton = document.getElementById('logout-btn');
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            localStorage.removeItem('AdminUser');
            window.location.href = '../index.html';
        });
    }

    // Gán sự kiện cho các nút Edit trong danh sách
    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            fetchAndEditProduct(btn.dataset.id);
        });
    });
});

/* ==========================================
   3. FORM EDIT & FETCH DATA
   ========================================== */

function fetchAndEditProduct(productId) {
    fetch(`list-product?product_id=${productId}`)
        .then(res => res.json())
        .then(product => {
            editProduct(product);
        })
        .catch(err => console.error("Error:", err));
}

function editProduct(product) {
    const overlay = document.getElementById('overlay');
    const editModal = document.getElementById('editModal');
    if (!overlay || !editModal) return;

    overlay.style.display = 'block';
    editModal.style.display = 'block';

    // Đổ dữ liệu vào Form
    document.getElementById('products_name').value = product.name;
    document.getElementById('products_price').value = product.selling_price;
    document.getElementById('products_status').value = product.status;
    document.getElementById('products_category').value = product.category_id;
    document.getElementById('products_description').value = product.description || '';

    if(document.getElementById('profit_percent')) 
        document.getElementById('profit_percent').value = product.profit_percent;
    if(document.getElementById('current_stock')) 
        document.getElementById('current_stock').value = product.current_stock;
    
    // Xử lý ảnh khi mở form
    const img = document.getElementById('current_product_image');
    const previewPath = document.getElementById('preview-path');
    const flag = document.getElementById('remove_photo_flag');

    if (flag) flag.value = "0"; // Reset cờ xóa ảnh mỗi khi mở sản phẩm mới

    if (img && product.image) {
        img.src = "../../" + product.image;
        img.style.display = 'block';
        const fileName = product.image.split('/').pop();
        if (previewPath) previewPath.value = "uploads/" + fileName;
    } else {
        if (img) img.style.display = 'none';
        if (previewPath) previewPath.value = '';
    }
}

/* ==========================================
   4. UI HELPER (NOTIFICATIONS, TOGGLES)
   ========================================== */

function toggleGrade(contentId, chevronId) {
    const chevrons = document.querySelectorAll('#' + chevronId);
    const contents = document.querySelectorAll('#' + contentId);
    chevrons.forEach(c => c.classList.toggle('up'));
    contents.forEach(c => c.style.display = (c.style.display === "none" ? "block" : "none"));
}

function hideNotification(notificationId) {
    const element = document.getElementById(notificationId);
    if (element) element.style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function showDeleteNotification() {
    document.getElementById('deleteNotification').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}