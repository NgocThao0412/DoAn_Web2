document.addEventListener('DOMContentLoaded', function () {

    const citySelect = document.getElementById("registerCity");
    const districtSelect = document.getElementById("registerDistrict");
    const wardSelect = document.getElementById("registerWard");
    const streetInput = document.getElementById("registerStreet");

    const receiverName = document.getElementById("full_name");
    const receiverPhone = document.getElementById("phone");

    const autoFillRadio = document.getElementById("autoFill");
    const otherRadio = document.getElementById("sendOther");

    // thông tin địa chỉ của user từ server
    const userCity = userAddressInfo.city;
    const userDistrict = userAddressInfo.district;
    const userWard = userAddressInfo.ward;
    const userStreet = userAddressInfo.street;
    const fullName = userAddressInfo.full_name;
    const phone = userAddressInfo.phone;

    async function autoFillAddress() {
        // gán tên và số điện thoại
        receiverName.value = fullName;
        receiverPhone.value = phone;
        streetInput.value = userStreet;

        // reset select
        citySelect.innerHTML = "<option value=''>Chọn Thành phố</option>";
        districtSelect.innerHTML = "<option value=''>Chọn Quận/Huyện</option>";
        wardSelect.innerHTML = "<option value=''>Chọn Phường/Xã</option>";

        try {
            // 🔹 Load tất cả city từ DB
            const cities = await fetch("includes/getProvinces.php")
                .then(r => r.json());

            cities.forEach(c => {
                const option = new Option(c.provinceName, c.provinceID);
                citySelect.add(option);
            });

            // tìm cityID tương ứng với tên user
            const selectedCity = cities.find(c => c.provinceName === userCity);
            if (!selectedCity) return;
            citySelect.value = selectedCity.provinceID;

            // 🔹 Load districts theo cityID
            const districts = await fetch(`includes/getDistricts.php?provinceID=${selectedCity.provinceID}`)
                .then(r => r.json());

            districtSelect.innerHTML = "<option value=''>Chọn Quận/Huyện</option>";
            districts.forEach(d => {
                const option = new Option(d.districtName, d.districtID);
                districtSelect.add(option);
            });

            const selectedDistrict = districts.find(d => d.districtName === userDistrict);
            if (!selectedDistrict) return;
            districtSelect.value = selectedDistrict.districtID;

            // 🔹 Load wards theo districtID
            const wards = await fetch(`includes/getWards.php?districtID=${selectedDistrict.districtID}`)
                .then(r => r.json());

            wardSelect.innerHTML = "<option value=''>Chọn Phường/Xã</option>";
            wards.forEach(w => {
                const option = new Option(w.wardName, w.wardID);
                wardSelect.add(option);
            });

            const selectedWard = wards.find(w => w.wardName === userWard);
            if (selectedWard) wardSelect.value = selectedWard.wardID;

            // 🔹 Khóa các select và input nếu auto-fill
            citySelect.classList.add('select-disabled');
            districtSelect.classList.add('select-disabled');
            wardSelect.classList.add('select-disabled');

            receiverName.readOnly = true;
            receiverPhone.readOnly = true;
            streetInput.readOnly = true;

        } catch (err) {
            console.error("Lỗi load địa chỉ từ DB:", err);
        }
    }

    // nếu radio auto-fill được check
    autoFillRadio.addEventListener("change", function () {
        if (this.checked) autoFillAddress();
    });

    // Khi chọn "Gửi đến địa chỉ khác"
otherRadio.addEventListener("change", async function () {
    if (!this.checked) return;

    // --- Mở select và input để user điền ---
    citySelect.disabled = false;
    wardSelect.disabled = false;
    streetInput.readOnly = false;
    citySelect.classList.remove('select-disabled');
    wardSelect.classList.remove('select-disabled');
    streetInput.classList.remove('readonly-input');

    receiverName.readOnly = false;
    receiverPhone.readOnly = false;

    // --- Reset giá trị input ---
    receiverName.value = '';
    receiverPhone.value = '';
    streetInput.value = '';
    citySelect.innerHTML = "<option value=''>Chọn Thành phố</option>";
    wardSelect.innerHTML = "<option value=''>Chọn Phường / Xã</option>";

    // --- Load tỉnh/thành phố từ DB ---
    try {
        const provinces = await fetch("includes/getProvinces.php").then(res => res.json());
        provinces.forEach(p => citySelect.add(new Option(p.provinceName, p.provinceID)));
    } catch (err) {
        console.error("Lỗi tải tỉnh:", err);
        alert("Không thể tải danh sách tỉnh/thành phố.");
        return;
    }

    // focus vào ô tên người nhận để user nhập
    receiverName.focus();
});

// Khi chọn city, load wards tương ứng
citySelect.addEventListener("change", async function () {
    const provinceID = this.value;
    wardSelect.innerHTML = "<option value=''>Chọn Phường / Xã</option>";

    if (!provinceID) return;

    try {
        const wards = await fetch(`includes/getWard.php?provinceID=${provinceID}`).then(res => res.json());
        wards.forEach(w => wardSelect.add(new Option(w.wardName, w.wardID)));
    } catch (err) {
        console.error("Lỗi tải phường/xã:", err);
        alert("Không thể tải danh sách phường/xã.");
    }
});
    // hiện thông tin thanh toán
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
const momoFields = document.getElementById('Momo-fields');
const vnpayFields = document.getElementById('VNPay-fields');
const myOrder = document.querySelector('.my-order');

function updatePaymentView() {
    const selected = document.querySelector('input[name="payment_method"]:checked').value;

    // Hiển thị/ẩn các trường chi tiết
    momoFields.style.display = selected === 'Momo' ? 'block' : 'none';
    vnpayFields.style.display = selected === 'VNPay' ? 'block' : 'none';

    // Ẩn/hiện phần Your Orders
    myOrder.style.display = selected === 'COD' ? 'block' : 'none';
}

// Gán sự kiện change cho tất cả radio
paymentRadios.forEach(radio => {
    radio.addEventListener('change', updatePaymentView);
});

// Gọi lần đầu để thiết lập trạng thái mặc định
updatePaymentView();
     
     // Ẩn tất cả các phương thức thanh toán khi tải trang
    window.addEventListener('load', () => {
    momoFields.classList.remove('show');
    vnpayFields.classList.remove('show');
});
    
    // Lấy ngày hôm nay và định dạng lại theo định dạng yyyy-mm-dd
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); // Tháng bắt đầu từ 0
    var yyyy = today.getFullYear();

    today = yyyy + '-' + mm + '-' + dd;

    // Đặt ngày hôm nay là giá trị min của input
    document.getElementById("delivery_date").setAttribute("min", today);
    
    

    // Submit form và hiện confirmation
    document.getElementById('payment-form').addEventListener('submit', function (e) {

        // Kiểm tra số điện thoại nếu gửi đến địa chỉ khác
        if (otherRadio.checked) {
            const phone = receiverPhone.value.trim();
            const phoneRegex = /^(03|05|07|08|09)\d{8}$/;
        
            if (!phoneRegex.test(phone)) {
                alert("Số điện thoại không hợp lệ. Vui lòng nhập đúng 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09.");
                receiverPhone.focus();
                e.preventDefault();
                return false;
            }
        }
        
        

        const timeInput = document.getElementById('delivery_time');
        const selectedTime = timeInput.value;
    
        if (selectedTime) {
            const [hours, minutes] = selectedTime.split(':').map(Number);
            const totalMinutes = hours * 60 + minutes;
    
            if (totalMinutes < 480 || totalMinutes > 1200) {
                e.preventDefault();
                alert('Thời gian giao hàng phải nằm trong khoảng từ 08:00 đến 20:00.');
                timeInput.focus();
                return false;
            }
        }
        
        const urlPattern = /(https?:\/\/|www\.)[^\s]+|[^\s]+\.(com|net|org|vn|info|biz|edu)/i;
        const inputs = this.querySelectorAll('input[type="text"], input[type="number"], textarea');
    
        for (const input of inputs) {
            if (urlPattern.test(input.value)) {
                alert('Không được phép chèn liên kết vào các trường nhập liệu. Vui lòng xóa bất kỳ URL nào trước khi tiếp tục.');
                input.focus();
                e.preventDefault(); // Ngăn form được gửi
                return false;
            }
        }

        e.preventDefault();

        updateAddressNames(); // Cập nhật tên city/district/ward

        const formData = new FormData(this);

        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        fetch('pages/order_process.php', {
    method: 'POST',
    body: formData
})
.then(response => response.text())
.then(text => {
    console.log("SERVER:", text);

    let data;
    try {
        data = JSON.parse(text); // convert sang JSON
    } catch (e) {
        alert("Server trả về lỗi (không phải JSON)");
        return;
    }

    // 👉 xử lý luôn ở đây (KHÔNG dùng thêm .then nữa)
    if (data.success) {
    document.getElementById('confirmation-overlay').style.display = 'block';
    document.getElementById('confirmation').classList.add('show');

    document.getElementById('order-id-number').textContent = `#${data.order_id}`;
    document.getElementById('view-invoice-link').href = `receipt?order_id=${data.order_id}`;

    // 🔥 SỬA Ở ĐÂY
    fetch(`pages/get_last_order_items.php?order_id=${data.order_id}`)
        .then(res => res.json())
        .then(items => {
            let orderItemsHtml = '';
            let totalCost = 0;

            if (items.items.length > 0) {
                const infoHtml = `
                    <div class="receive-info">
                        <div><strong>Recipient:</strong> ${items.items[0].recipient_name || ''}</div>
                        <div><strong>Delivery address:</strong> 
                            ${items.items[0].shipping_street || ''}, 
                            ${items.items[0].shipping_ward || ''}, 
                            ${items.items[0].shipping_district || ''}, 
                            ${items.items[0].shipping_city || ''}
                        </div>
                    </div>
                `;
                document.getElementById('receive-address-display').innerHTML = infoHtml;
            }

            items.items.forEach(item => {
                orderItemsHtml += `
                    <div class="receipt-rev">
                        <div class="name-food">${item.name}</div>
                        <div class="number">x${item.quantity}</div>
                    </div>
                `;
                totalCost += item.price * item.quantity;
            });

            document.getElementById('order-items').innerHTML = orderItemsHtml;
            document.getElementById('total-cost-display').innerHTML =
                `Total: <span>${totalCost.toLocaleString()} VND</span>`;
        });

} else {
    alert(data.message || "Order failed.");
}
})
.catch(err => {
    console.error(err);
    alert("An error occurred while placing the order.");
})});



    function autoFillAddress() {
        receiverName.value = userAddressInfo.full_name;
        receiverPhone.value = userAddressInfo.phone;
        citySelect.innerHTML = `<option value="${userCity}" selected>${userCity}</option>`;
        districtSelect.innerHTML = `<option value="${userDistrict}" selected>${userDistrict}</option>`;
        wardSelect.innerHTML = `<option value="${userWard}" selected>${userWard}</option>`;
        streetInput.value = userStreet;
    
        citySelect.classList.add('select-disabled');
        districtSelect.classList.add('select-disabled');
        wardSelect.classList.add('select-disabled');
        streetInput.classList.add('readonly-input');

        // Disable inputs nếu muốn tránh chỉnh sửa
        receiverName.readOnly = true;
        receiverPhone.readOnly = true;

        console.log("userAddressInfo:", userAddressInfo);
        console.log("userAddressInfo.phone:", userAddressInfo?.phone);
        console.log("userAddressInfo.full_name:", userAddressInfo?.full_name);

    }
    

    autoFillRadio.addEventListener("change", function () {
        if (this.checked) {
            autoFillAddress();
        }
    });
    
    if (autoFillRadio.checked) {
        autoFillAddress();
    }
    
    function updateAddressNames() {
    const selectedCityText = citySelect.options[citySelect.selectedIndex]?.text || '';
    const selectedWardText = wardSelect.options[wardSelect.selectedIndex]?.text || '';

    document.getElementById("shipping_city_name").value = selectedCityText;
    document.getElementById("shipping_ward_name").value = selectedWardText;
}
    
    
});


