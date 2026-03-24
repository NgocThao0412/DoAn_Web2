document.addEventListener("DOMContentLoaded", function () {

    const citySelect = document.getElementById("registerCity");
    const wardSelect = document.getElementById("registerWard");
    const form = document.getElementById("registerForm");

    if (!form) return;

    const cityNameHidden = document.getElementById("city_name");
    const wardNameHidden = document.getElementById("ward_name");

    // ==============================
    // LOAD TỈNH
    // ==============================
    fetch("includes/getProvinces.php")
        .then(res => res.json())
        .then(data => {
            citySelect.innerHTML = "<option value=''>Chọn Tỉnh / Thành phố</option>";
            data.forEach(city => {
                const option = new Option(city.provinceName, city.provinceID);
                citySelect.add(option);
            });
        })
        .catch(err => {
            console.error("Lỗi tải tỉnh:", err);
        });

    // ==============================
    // LOAD PHƯỜNG
    // ==============================
    citySelect.addEventListener("change", function () {
        const provinceID = citySelect.value;

        cityNameHidden.value = citySelect.options[citySelect.selectedIndex]?.text || "";

        wardSelect.innerHTML = "<option value=''>Chọn Phường / Xã</option>";
        wardNameHidden.value = "";

        if (provinceID) {
            fetch(`includes/getWard.php?provinceID=${provinceID}`)
                .then(res => res.json())
                .then(wards => {
                    wards.forEach(ward => {
                        const option = new Option(ward.wardName, ward.wardID);
                        wardSelect.add(option);
                    });
                })
                .catch(err => {
                    console.error("Lỗi tải phường:", err);
                });
        }
    });

    wardSelect.addEventListener("change", function () {
        wardNameHidden.value = wardSelect.options[wardSelect.selectedIndex]?.text || "";
    });

    // ==============================
    // SUBMIT FORM
    // ==============================
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Xóa lỗi cũ
        document.querySelectorAll(".error").forEach(e => e.innerText = "");

        const usernameInput = document.getElementById("registerUsername");
        const usernameValue = usernameInput?.value?.trim() || "";

        // Check username chứa link
        const urlPattern = /\b((http|https):\/\/|www\.)[^\s]+|[^\s]+\.(com|net|org|vn|info|biz|edu)(\b|\/)/i;
        if (urlPattern.test(usernameValue)) {
            document.getElementById("error-username").innerText = "Tên đăng nhập không được chứa liên kết.";
            usernameInput.focus();
            return;
        }

        // Gán tên tỉnh/xã
        cityNameHidden.value = citySelect.options[citySelect.selectedIndex]?.text || "";
        wardNameHidden.value = wardSelect.options[wardSelect.selectedIndex]?.text || "";

        const formData = new FormData(form);

        fetch("pages/Controllers/register_process.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text()) // 🔥 dùng text để debug an toàn
        .then(text => {
            try {
                const data = JSON.parse(text);

                if (data.success) {
                    alert(data.message);
                    window.location.href = "login";
                } else {
                    // Hiển thị lỗi dưới từng input
                    for (let field in data.errors) {
                        const errorDiv = document.getElementById("error-" + field);
                        if (errorDiv) {
                            errorDiv.innerText = data.errors[field];
                        }
                    }
                }

            } catch (err) {
                console.error("LỖI JSON:", text); // 🔥 in ra lỗi PHP thật
                alert("Server đang lỗi, xem console!");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("Không thể kết nối server.");
        });
    });

    // ==============================
    // HIỆU ỨNG INPUT
    // ==============================
    document.querySelectorAll(".input-box input").forEach(input => {
        input.addEventListener("input", function () {
            if (this.value.trim() !== "") {
                this.classList.add("has-content");
            } else {
                this.classList.remove("has-content");
            }
        });
    });

});