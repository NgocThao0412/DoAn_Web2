document.addEventListener('DOMContentLoaded', function() {
        // Function: Check login status
        console.log("JS loaded");
    function checkLoginStatus(callback) {
        console.log("Đang gọi checkLoginStatus...");
        fetch("Api_php/session-admin.php", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            console.log("Dữ liệu session trả về:", data);

            // Nếu chưa đăng nhập, chuyển hướng về trang login
            if (!data.loggedIn) {
                console.warn("Chưa đăng nhập. Chuyển về trang đăng nhập...");
                window.location.href = "login";
                return;
            }
    
            // Check tài khoản bị khóa
            if (data.status && data.status.toLowerCase() === "locked") {
                console.warn("Tài khoản đã bị khóa. Chuyển về trang đăng nhập...");
                alert("Your account has been locked. You will be redirected to the login page.");
                window.location.href = "login"; // Hoặc đúng link login của bạn
                return;
            }
    
            // Check trạng thái đăng nhập
            if (data.loggedIn) {
                document.body.classList.add("logged-in");
            } else {
                document.body.classList.remove("logged-in");
            }
    
            if (callback) {
                callback(data.loggedIn);
            }
        })
        .catch(error => console.error("Lỗi khi kiểm tra session:", error));
    }    
    

    // Kiểm tra trạng thái đăng nhập
    checkLoginStatus((isLoggedIn) => {
        if (!isLoggedIn) {
            console.log("Không đăng nhập, xóa flag welcomeShownAdmin");
            localStorage.removeItem("welcomeShownAdmin");
            console.log("welcomeShownAdmin flag removed:", localStorage.getItem("welcomeShownAdmin"));
        }
    });

    const save = document.querySelector('.save'); // Nút lưu
    const blurOverlay = document.querySelector('.blur-overlay');
    const save_suc = document.querySelector('.save-success');
    const close = document.querySelector('.close');
    const fileInput = document.getElementById("fileInput")
    const categorySelect = document.getElementById("category_id");
    
    // Biến flag để ngăn double submit
    let isSubmitting = false;

    // Bắt sự kiện khi ấn nút "Save"
    if (save) {
        save.addEventListener('click', function(event) {
            event.preventDefault(); // Ngăn chặn load lại trang

            // Nếu đang gửi, không thực hiện lại
            if(isSubmitting) return;
        isSubmitting = true;

        // Lấy dữ liệu từ form
        let name = document.getElementById("name").value.trim();
        console.log(document.getElementById("selling_price"));
        let price = document.getElementById("selling_price").value.trim();
        let status = document.getElementById("status").value.trim();
        let category = document.getElementById("category_id").value.trim();
        let unit = document.getElementById("unit").value.trim();
        let imagePath = document.getElementById("filePath").value.trim();
        let profit = document.getElementById("profit_percent").value.trim();
        let description = document.getElementById("description").value.trim();
       // Kiểm tra giá trị của giá trị đã nhập (Phải >= 0)
if (parseFloat(price) < 0) {
    alert("⚠️ Giá không được nhỏ hơn 0");
    isSubmitting = false;
    return;
}

if (parseFloat(profit) < 0) {
    alert("⚠️ Lợi nhuận không được nhỏ hơn 0");
    isSubmitting = false;
    return;
}      
        // In ra console để debug
        console.log("📌 Dữ liệu nhập vào:");
        console.log("🛒 Tên:", name);
        console.log("💲 Giá:", price);
        console.log("📌 Trạng thái:", status);
        console.log("📁 Danh mục:", category);
        console.log("📦 Đơn vị tính:", unit);
        console.log("📊 Số lượng ban đầu:", stock);
        console.log("📈 Lợi nhuận:", profit);
        console.log("📝 Mô tả:", description);
        console.log("🖼 Đường dẫn hình ảnh:", imagePath);

        // Kiểm tra nếu có trường nào bị thiếu
        if (!name || !price || !status || !category || !unit || !stock || !profit || !description) {
            alert("⚠️  Vui lòng điền đầy đủ thông tin!");
            isSubmitting = false;  // reset flag
            return;
        }

        // Tạo object dữ liệu gửi đi
        let formData = new FormData();
        formData.append("name", name);
        formData.append("selling_price", price);
        formData.append("status", status);
        formData.append("category_id", category);
        formData.append("unit", unit);
        formData.append("profit_percent", profit);
        formData.append("description", description);
        formData.append("image", document.getElementById("filePath").value);

        // Gửi request AJAX để lưu sản phẩm
        fetch("Controllers/add-product-process.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Phản hồi từ server:", data);

            if (data.success) {
                // Hiển thị popup thành công
                save_suc.classList.add('active-popup');
                blurOverlay.classList.add('active');
                
                // Hiển thị thông báo
                alert("✅ Thêm sản phẩm thành công!");
                
                // Reset form sau khi lưu
                let form = document.getElementById("add-product-form"); // 🔹 Đúng ID của form
                if (form) {
                    form.reset();
                } else {
                    console.error("❌ Lỗi: Không tìm thấy form với ID 'add-product-form'.");
                }                

            } else {
                alert("❌ Lỗi khi lưu sản phẩm: " + data.message);
            }
        })
        .catch(error => {
            console.error("❌ Lỗi khi gửi dữ liệu:", error);
            alert("❌ Lỗi khi gửi dữ liệu, vui lòng thử lại.");
        })
        .finally(() => {
            // Reset lại flag sau khi hoàn thành gửi
            isSubmitting = false;
        });

    });
}

    // Đóng popup khi bấm nút close
    if (close) {
    close.addEventListener('click', function(event) {
        event.stopPropagation();
        save_suc.classList.remove('active-popup');
        blurOverlay.classList.remove('active');
    });
}
    
    // const addCategory = document.querySelector('.add-category');
    // const addStatus = document.querySelector('.add-status');
    
    // addCategory.addEventListener('click', function(event) {
    //     event.preventDefault();
    //     alert("This function is still under development")
    // });
    
    // addStatus.addEventListener('click', function(event) {
    //     event.preventDefault();
    //     alert("This function is still under development")
    // });
    // Cập nhật danh sách category theo database
    // Đường dẫn ảnh
    if (fileInput) {
        fileInput.addEventListener("change", function (event) {
            const file = this.files[0];
            if (!file) return;

        // Kiểm tra nếu chưa chọn category
        if (!categorySelect.value) {
            alert("⚠️ Xin hãy chọn danh mục trước khi tải lên hình ảnh!");
            fileInput.value = "";
            return;
        }

        // Kiểm tra định dạng và kích thước
        const allowedTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            alert("❌ Định dạng tệp không hợp lệ. Chỉ cho phép JPG, JPEG, PNG và GIF.");
            fileInput.value = "";
            clearPreview();
            return;
        }

        if (file.size > maxSize) {
            alert("❌ File size exceeds 5MB. Please choose a smaller image.");
            fileInput.value = "";
            clearPreview();
            return;
        }

        // 👉 Nếu hợp lệ thì hiển thị preview và upload
        previewImage(event);

        const formData = new FormData();
        formData.append("file", file);
        formData.append("category_id", categorySelect.value);

        fetch("Api_php/upload-img.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                const filePathInput = document.getElementById("filePath");
                filePathInput.value = data.filePath;
            } else {
                alert("❌ Upload failed: " + data.error);
                clearPreview();
                fileInput.value = "";
            }
        })
        .catch(error => {
            console.error("❌ Lỗi khi upload ảnh:", error);
            clearPreview();
            fileInput.value = "";
        });
    });

    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        const filePathInput = document.getElementById('filePath');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        
        } else {
            clearPreview();
        }
    }

    function clearPreview() {
        const preview = document.getElementById('imagePreview');
        const filePathInput = document.getElementById('filePath');

        preview.src = '';
        preview.style.display = 'none';
        filePathInput.value = '';
    }


    // Xóa dữ liệu khi form reset
    const form = document.getElementById("add-product-form");
    if (!form) return;
    form.addEventListener("reset", function() {
        const preview = document.getElementById('imagePreview');
        preview.src = '';
        preview.style.display = 'none';
    
        // Cũng nên xóa đường dẫn trong input filePath:
        document.getElementById('filePath').value = '';
    });
}
});
