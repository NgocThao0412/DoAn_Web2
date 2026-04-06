document.addEventListener('DOMContentLoaded', function () {

    const citySelect = document.getElementById("registerCity");
    const districtSelect = document.getElementById("registerDistrict");
    const wardSelect = document.getElementById("registerWard");
    const streetInput = document.getElementById("registerStreet");

    const receiverName = document.getElementById("full_name");
    const receiverPhone = document.getElementById("phone");

    const autoFillRadio = document.getElementById("autoFill");
    const otherRadio = document.getElementById("sendOther");

    
    const userCity = userAddressInfo.city;
    const userDistrict = userAddressInfo.district;
    const userWard = userAddressInfo.ward;
    const userStreet = userAddressInfo.street;
    const fullName = userAddressInfo.full_name;
    const phone = userAddressInfo.phone;

    async function autoFillAddress() {
        
        receiverName.value = fullName;
        receiverPhone.value = phone;
        streetInput.value = userStreet;

        
        citySelect.innerHTML = "<option value=''>Chọn Thành phố</option>";
        districtSelect.innerHTML = "<option value=''>Chọn Quận/Huyện</option>";
        wardSelect.innerHTML = "<option value=''>Chọn Phường/Xã</option>";

        try {
            
            const cities = await fetch("includes/getProvinces.php")
                .then(r => r.json());

            cities.forEach(c => {
                const option = new Option(c.provinceName, c.provinceID);
                citySelect.add(option);
            });

           
            const selectedCity = cities.find(c => c.provinceName === userCity);
            if (!selectedCity) return;
            citySelect.value = selectedCity.provinceID;

           
            const wards = await fetch(`includes/getWard.php?provinceID=${selectedCity.provinceID}`)
                .then(r => r.json());

            wardSelect.innerHTML = "<option value=''>Chọn Phường/Xã</option>";
            wards.forEach(w => {
                const option = new Option(w.wardName, w.wardID);
                wardSelect.add(option);
            });

            const selectedWard = wards.find(w => w.wardName === userWard);
            if (selectedWard) wardSelect.value = selectedWard.wardID;

            
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

    
    autoFillRadio.addEventListener("change", function () {
        if (this.checked) autoFillAddress();
    });

    
otherRadio.addEventListener("change", async function () {
    if (!this.checked) return;

    
    citySelect.disabled = false;
    wardSelect.disabled = false;
    streetInput.readOnly = false;
    citySelect.classList.remove('select-disabled');
    wardSelect.classList.remove('select-disabled');
    streetInput.classList.remove('readonly-input');

    receiverName.readOnly = false;
    receiverPhone.readOnly = false;

    
    receiverName.value = '';
    receiverPhone.value = '';
    streetInput.value = '';
    citySelect.innerHTML = "<option value=''>Chọn Thành phố</option>";
    wardSelect.innerHTML = "<option value=''>Chọn Phường / Xã</option>";

    try {
        const provinces = await fetch("includes/getProvinces.php").then(res => res.json());
        provinces.forEach(p => citySelect.add(new Option(p.provinceName, p.provinceID)));
    } catch (err) {
        console.error("Lỗi tải tỉnh:", err);
        alert("Không thể tải danh sách tỉnh/thành phố.");
        return;
    }

    receiverName.focus();
});


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
    
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
const momoFields = document.getElementById('Momo-fields');
const vnpayFields = document.getElementById('VNPay-fields');
const myOrder = document.querySelector('.my-order');
const subtotal = document.querySelector('.subtotal');
const notification = document.querySelector('.notification');
const payBtn = document.querySelector('.pay');


function updatePaymentView() {
    const selectedRadio = document.querySelector('input[name="payment_method"]:checked');
    if (!selectedRadio) return;

    const selected = selectedRadio.value;

    // reset
    momoFields.style.display = 'none';
    vnpayFields.style.display = 'none';

    subtotal.style.display = 'block';
    notification.style.display = 'block';
    payBtn.style.display = 'block';

    if (selected === 'Momo') {
    
        momoFields.style.display = 'block';

    
        subtotal.style.display = 'none';
        notification.style.display = 'none';
        payBtn.style.display = 'none';
    } 
    else if (selected === 'VNPay') {
        vnpayFields.style.display = 'block';
    } 
    else if (selected === 'COD') {
    
    }
}




paymentRadios.forEach(radio => {
    radio.addEventListener('change', updatePaymentView);
});


    
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); 
    var yyyy = today.getFullYear();

    today = yyyy + '-' + mm + '-' + dd;

    
    const deliveryDateInput = document.getElementById("delivery_date");
    deliveryDateInput.setAttribute("min", today);

    
    const maxDate = new Date(today);
    maxDate.setMonth(maxDate.getMonth() + 1);
    const maxDd = String(maxDate.getDate()).padStart(2, '0');
    const maxMm = String(maxDate.getMonth() + 1).padStart(2, '0');
    const maxYyyy = maxDate.getFullYear();
    deliveryDateInput.setAttribute("max", `${maxYyyy}-${maxMm}-${maxDd}`);
    
    

    
    document.getElementById('payment-form').addEventListener('submit', function (e) {

        const name = receiverName.value.trim();
        const phone = receiverPhone.value.trim();
        const nameRegex = /^[\p{L} ]+$/u;
        const phoneRegex = /^(03|05|07|08|09)\d{8}$/;

        if (!phone) {
            alert("Vui lòng nhập số điện thoại");
            receiverPhone.focus();
            e.preventDefault();
            return false;
        }

        if (otherRadio.checked) {
            if (!name) {
                alert("Vui lòng nhập họ và tên");
                receiverName.focus();
                e.preventDefault();
                return false;
            }

            if (!nameRegex.test(name)) {
                alert("Họ và tên không được chứa ký tự đặc biệt");
                receiverName.focus();
                e.preventDefault();
                return false;
            }

            if (!phoneRegex.test(phone)) {
                alert("Số điện thoại không hợp lệ. Vui lòng nhập đúng 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09.");
                receiverPhone.focus();
                e.preventDefault();
                return false;
            }
        }
        
        

        const dateInput = deliveryDateInput.value;
        const timeInput = document.getElementById('delivery_time');
        const selectedTime = timeInput.value;
        const selectedDate = new Date(dateInput + 'T00:00:00');
        const minDate = new Date(deliveryDateInput.getAttribute('min') + 'T00:00:00');
        const maxDate = new Date(deliveryDateInput.getAttribute('max') + 'T00:00:00');

        if (!dateInput) {
            alert('Vui lòng chọn ngày giao hàng');
            deliveryDateInput.focus();
            e.preventDefault();
            return false;
        }

        if (selectedDate < minDate) {
            alert('Ngày giao hàng không được ở quá khứ');
            deliveryDateInput.focus();
            e.preventDefault();
            return false;
        }

        if (selectedDate > maxDate) {
            alert('Ngày giao hàng không được quá 1 tháng kể từ hôm nay');
            deliveryDateInput.focus();
            e.preventDefault();
            return false;
        }

        if (!selectedTime) {
            alert('Vui lòng chọn thời gian giao hàng');
            timeInput.focus();
            e.preventDefault();
            return false;
        }

        const [hours, minutes] = selectedTime.split(':').map(Number);
        const totalMinutes = hours * 60 + minutes;

        if (totalMinutes < 480 || totalMinutes > 1200) {
            e.preventDefault();
            alert('Thời gian giao hàng phải nằm trong khoảng từ 08:00 đến 20:00.');
            timeInput.focus();
            return false;
        }

        const now = new Date();
        const todayDate = new Date(today + 'T00:00:00');
        if (selectedDate.getTime() === todayDate.getTime()) {
            const currentMinutes = now.getHours() * 60 + now.getMinutes();
            if (totalMinutes <= currentMinutes) {
                e.preventDefault();
                alert('Thời gian giao hàng hôm nay không được ở quá khứ. Vui lòng chọn giờ sau hiện tại.');
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
                e.preventDefault(); 
                return false;
            }
        }

        e.preventDefault();

        updateAddressNames(); 
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
        data = JSON.parse(text); 
    } catch (e) {
        alert("Server trả về lỗi (không phải JSON)");
        return;
    }

    
    if (data.success) {
    document.getElementById('confirmation-overlay').style.display = 'block';
    document.getElementById('confirmation').classList.add('show');

    document.getElementById('order-id-number').textContent = `#${data.order_id}`;
    document.getElementById('view-invoice-link').href = `receipt?order_id=${data.order_id}`;

  
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
                `Total: <span>${totalCost.toLocaleString()} VNĐ</span>`;
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


