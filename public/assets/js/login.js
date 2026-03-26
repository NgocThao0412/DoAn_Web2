document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registerForm");
    if (!form) return;

    const citySelect = document.getElementById("registerCity");
    const wardSelect = document.getElementById("registerWard");

    const cityNameHidden = document.getElementById("city_name");
    const wardNameHidden = document.getElementById("ward_name");

    // ================= SHOW ERROR =================
    function showError(field, message) {
        if (field === "confirm_password") field = "confirm-password";
        const el = document.getElementById("error-" + field);
        if (el) el.innerText = message;
    }

    // ================= LOAD TỈNH =================
    fetch("includes/getProvinces.php")
        .then(res => res.json())
        .then(data => {

            citySelect.innerHTML =
                "<option value=''>Chọn Tỉnh / Thành phố</option>";

            data.forEach(item => {

                const name = item.provinceName || item.name;
                const id   = item.provinceID || item.id;

                const option = new Option(name, id);
                option.dataset.name = name;

                citySelect.add(option);
            });
        });

    // ================= CHỌN TỈNH =================
    citySelect.addEventListener("change", function () {

        const selectedOption = this.options[this.selectedIndex];

        // ⭐ LƯU TÊN
        cityNameHidden.value = selectedOption?.dataset.name || "";

        // Reset phường
        wardSelect.innerHTML =
            "<option value=''>Chọn Phường / Xã</option>";

        wardNameHidden.value = "";

        const provinceID = this.value;
        if (!provinceID) return;

        fetch(`includes/getWard.php?provinceID=${provinceID}`)
            .then(res => res.json())
            .then(data => {

                data.forEach(item => {

                    const name = item.wardName || item.name;
                    const id   = item.wardID || item.id;

                    const option = new Option(name, id);
                    option.dataset.name = name;

                    wardSelect.add(option);
                });
            });
    });

    // ================= CHỌN PHƯỜNG =================
    wardSelect.addEventListener("change", function () {

        const selectedOption = this.options[this.selectedIndex];

        // ⭐ LƯU TÊN
        wardNameHidden.value = selectedOption?.dataset.name || "";
    });

    // ================= SUBMIT =================
    form.addEventListener("submit", function (e) {

        e.preventDefault();

        // ❌ KHÔNG reset select

        // Xóa lỗi cũ
        document.querySelectorAll(".error")
            .forEach(el => el.innerText = "");

        // ⭐ ÉP LƯU TÊN TRƯỚC KHI GỬI
        const cityOption =
            citySelect.options[citySelect.selectedIndex];

        const wardOption =
            wardSelect.options[wardSelect.selectedIndex];

        cityNameHidden.value =
            cityOption?.dataset.name || "";

        wardNameHidden.value =
            wardOption?.dataset.name || "";

        // ⭐ XÓA NAME CỦA SELECT → KHÔNG GỬI ID
        citySelect.removeAttribute("name");
        wardSelect.removeAttribute("name");

        const formData = new FormData(form);

        fetch("pages/Controllers/register_process.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            if (data.success) {

                alert(data.message);
                window.location.href = "login";

            } else {

                // Hiện lỗi nhưng KHÔNG reset select
                for (let field in data.errors) {
                    showError(field, data.errors[field]);
                }
            }
        })
        .catch(err => {
            console.error(err);
            showError("general", "Không kết nối server");
        });
    });

});
