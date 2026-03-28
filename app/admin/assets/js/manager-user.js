// Function: Check login status
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
            alert("Tài khoản của bạn đã bị khóa. Bạn sẽ được chuyển hướng đến trang đăng nhập.");
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

function normalizeString(str) {
    if (!str) return "";

    return str
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")      // bỏ dấu
        .replace(/tp[\.\- ]*/i, "")          // xử lý "TP", "TP.", "TP-", "TP "
        .replace(/tinh[\.\- ]*/i, "")        // xử lý "Tỉnh", "Tỉnh."
        .replace(/thanh pho[\.\- ]*/i, "")   // xử lý "Thành phố"
        .toLowerCase()
        .trim();
}

function findBestMatchByName(list, name) {
    if (!name || !list) return null;
    const normName = normalizeString(name);
    return (
        list.find(item => {
            const normItemName = normalizeString(item.name);
            return (
                normItemName === normName ||
                normItemName.includes(normName) ||
                normName.includes(normItemName)
            );
        }) || null
    );
}

// Hiển thị form chỉnh sửa người dùng với dữ liệu đã có
async function showEditUserForm(users) {
    const user = users[0]; // Lấy người dùng đầu tiên từ mảng
    document.getElementById('modalTitle').innerText = "Chỉnh sửa người dùng";

    console.log("Dữ liệu người dùng:", users);

    document.getElementById('username').value = user.username || "";
    document.getElementById('fullname').value = user.fullname || "";
    document.getElementById('password').value = user.password || "";
    document.getElementById('confirm_password').value = user.password || "";
    document.getElementById('email').value = user.email || "";
    document.getElementById('phone').value = user.phone || "";
    document.getElementById('role').value = user.role || "";
    document.getElementById('street').value = user.street || "";

    document.getElementById('username').readOnly = true;
    document.getElementById('email').readOnly = true;
    document.getElementById('password').readOnly = true;

    document.getElementById('confirm_password_group').style.display = 'none';

    // Load danh sách thành phố
    const cities = await loadCities();

    console.log("Danh sách thành phố:", cities);
    console.log("Thành phố của user:", user.city);

    const cityMatch = findBestMatchByName(cities, user.city);

    if (cityMatch) {

        console.log("City match:", cityMatch);

        document.getElementById('city').value = cityMatch.code;

        // Load toàn bộ phường/xã thuộc thành phố
        await loadWardsFromCity(cityMatch.code);

        const wardSelect = document.getElementById('ward');

        const wardMatch = findBestMatchByName(
            Array.from(wardSelect.options).map(o => ({ name: o.value })),
            user.ward
        );

        if (wardMatch) {
            wardSelect.value = wardMatch.name;
        } else {
            console.log("Không tìm thấy phường:", user.ward);
        }

    } else {

        console.log("Không tìm thấy thành phố:", users.city);
        console.log("City normalized:", normalizeString(users.city));
        console.log(
            "Cities normalized:",
            cities.map(c => normalizeString(c.name))
        );

        alert("Không tìm thấy thành phố trong danh sách. Vui lòng chọn lại.");
    }

    const userModal = document.getElementById('userModal');

    if (userModal) {
        userModal.style.display = 'flex';
    } else {
        console.error("Không tìm thấy modal!");
    }
}

// Lấy thông tin người dùng và hiển thị form chỉnh sửa
async function editUser(username) {
    try {
        const response = await fetch(`Api_php/get-user.php?username=${username}`);
        let data = await response.json();
        
        if(!data) {
            alert("Không tìm thấy người dùng!");
            return;
        }

        if(!Array.isArray(data)) {
            data = [data]; // Đảm bảo data là mảng
        }

        showEditUserForm(data);
    } catch (error) {
        console.error("Lỗi khi lấy thông tin người dùng:", error);
        alert("Đã xảy ra lỗi khi lấy thông tin người dùng. Vui lòng thử lại.");
    }
}

// 1. Load Tỉnh/Thành từ Database nội bộ
async function loadCities(selectedCity = '') {
    const citySelect = document.getElementById("city");
    citySelect.innerHTML = '<option value="">Chọn thành phố</option>';

    try {
        const res = await fetch("Api_php/get-location.php?action=get_provinces");
        const cities = await res.json();

        cities.forEach(city => {
            const opt = document.createElement("option");
            opt.value = city.code; // provinceID (01, 79,...)
            opt.textContent = city.name;
            citySelect.appendChild(opt);
        });

        if (selectedCity) citySelect.value = selectedCity;
        return cities;
    } catch (error) {
        console.error("Lỗi load tỉnh thành:", error);
    }
}

// 2. Load Phường/Xã dựa trên provinceID từ Database nội bộ
async function loadWardsFromCity(provinceID, selectedWard = '') {
    const wardSelect = document.getElementById("ward");
    wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';

    if (!provinceID) return;

    try {
        // Gọi API nội bộ lấy xã theo tỉnh
        const res = await fetch(`Api_php/get-location.php?action=get_wards&provinceID=${provinceID}`);
        const wards = await res.json();

        wards.forEach(ward => {
            const opt = document.createElement("option");
            opt.value = ward.name;
            opt.textContent = ward.name;
            wardSelect.appendChild(opt);
        });

        if (selectedWard) wardSelect.value = selectedWard;
    } catch (error) {
        console.error("Lỗi load phường xã:", error);
    }
}

// Lưu thông tin người dùng (thêm mới hoặc cập nhật)
function saveUser() {
    const formData = new FormData();
    let errors = [];

    const isUpdate = document.getElementById('username').readOnly; // nếu readonly thì là đang sửa
    formData.append('is_update', isUpdate ? '1' : '0');


    const fields = ['username', 'fullname', 'email', 'phone', 'password', 'role', 'street', 'city', 'ward'];

    // Lấy dữ liệu từ input và kiểm tra nếu trống
    fields.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            formData.append(id, element.value.trim());
            if (!element.value.trim()) {
                errors.push(`Vui lòng nhập ${id.replace('_', ' ')}.`);
            }
        } else {
            console.warn(`⚠️ Element with ID ${id} not found`);
        }
    });

    const emailElement = document.getElementById('email');
    if (emailElement && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(emailElement.value.trim())) {
        errors.push("Email không hợp lệ.");
    }

    const phoneElement = document.getElementById('phone');
    const phonePattern = /^(03[2-9]|05[2,6,8,9]|07[0-9]|08[1-9]|09[0-9])\d{7}$/;
    if (phoneElement && !phonePattern.test(phoneElement.value.trim())) {
        errors.push("Số điện thoại không hợp lệ.");
    }

    // Kiểm tra mật khẩu hợp lệ
    const passwordElement = document.getElementById('password');
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (passwordElement && !passwordElement.readOnly) {
        if (!passwordPattern.test(passwordElement.value.trim())) {
            errors.push("Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ cái viết hoa, số và ký tự đặc biệt.");
        }
    }

    const confirm_password = document.getElementById('confirm_password').value;
    if (passwordElement && passwordElement.value !== confirm_password) {
        errors.push("Mật khẩu không khớp");
    }

    // Nếu có lỗi, hiển thị thông báo lỗi bằng alert
    if (errors.length > 0) {
        alert(errors.join("\n"));
        return;
    }

    // Lấy tên thành phố, và phường từ các option đã chọn
    const citySelect = document.getElementById('city');
    const wardSelect = document.getElementById('ward');

    const cityName = citySelect.options[citySelect.selectedIndex]?.text || '';
    const wardName = wardSelect.value;

    // Thêm vào formData các giá trị tên
    formData.append('city_name', cityName);
    formData.append('ward_name', wardName);
    
    // Gửi dữ liệu nếu hợp lệ
    fetch('Api_php/save-user.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            alert(data);
            closeModal();
            loadUserTable();
        })
        .catch(console.error);
}


let currentPage = 1;
const rowsPerPage = 8;

function paginateTable() {
    const rows = Array.from(document.querySelectorAll('#userTableContainer table tbody tr'))
        .filter(row => row.style.display !== 'none'); // chỉ lấy các hàng đang hiển thị (sau tìm kiếm)
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    let currentPage = 1;

    function showPage(page) {
        currentPage = page;
        rows.forEach((row, index) => {
            row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? '' : 'none';
        });

        renderPagination();
    }

    function renderPagination() {
        const paginationContainer = document.getElementById('paginationContainer');
        paginationContainer.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.innerText = i;
            if (i === currentPage) button.classList.add('active');
            button.addEventListener('click', () => showPage(i));
            paginationContainer.appendChild(button);
        }
    }

    // Gọi lần đầu
    if (rows.length > 0) {
        showPage(1);
    } else {
        document.getElementById('paginationContainer').innerHTML = '';
    }
}


function loadUserTable() {
    fetch('Controllers/user-process.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('userTableContainer').innerHTML = html;
            paginateTable();  // Gọi phân trang sau khi load bảng
        })
        .catch(console.error);
}


function showAddUserForm() {
    document.getElementById('modalTitle').innerText = "Thêm người dùng mới";
    document.querySelectorAll('#userFormContainer input').forEach(input => input.value = '');
    loadCities();

        // reset phường/xã
    document.getElementById("ward").innerHTML = '<option value="">Chọn phường/xã</option>';

    document.getElementById('username').readOnly = false;
    document.getElementById('email').readOnly = false;
    document.getElementById('password').readOnly = false;
    document.getElementById('confirm_password_group').style.display = 'block';
    document.getElementById('role').disabled = false;
    document.getElementById('role_hidden').value = document.getElementById('role').value = '';

    document.getElementById('userModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
    document.querySelectorAll('#userFormContainer input').forEach(input => input.value = '');
}

let pendingUserName = null;
let pendingStatus = null;

function toggleLockUser(username, currentStatus, role) {
    pendingUserName = username;
    pendingStatus = currentStatus.toLowerCase() === 'locked';
    const message = pendingStatus
        ? "Bạn có chắc chắn muốn mở khóa người dùng này không?"
        : "Bạn có chắc chắn muốn khóa người dùng này không?";

    document.querySelector("#confirmLockModal p").innerText = message;
    document.getElementById('confirmLockModal').style.display = 'flex';
}

// Xử lý khi người dùng xác nhận khóa/mở khóa
document.getElementById('confirmLockBtn').addEventListener('click', () => {
    if (pendingUserName !== null) {
        const formData = new FormData();
        formData.append('username', pendingUserName);
        formData.append('action', pendingStatus ? 'unlock' : 'lock');

        fetch('Api_php/lock-user.php', { method: 'POST', body: formData, credentials: 'include' })
            .then(response => response.text())
            .then(data => {
                alert(data);
                closeConfirmModal();
                loadUserTable();
            })
            .catch(console.error);
    }
});

function closeConfirmModal() {
    document.getElementById('confirmLockModal').style.display = 'none';
    pendingUserName = null;
    pendingStatus = null;
}

window.onload = loadUserTable;

// tìm kiếm user
function searchUser() {
    const inputField = document.querySelector('.find');
    const rawFilter = inputField.value.trim();
    const filter = normalizeString(rawFilter); 

    const table = document.querySelector('#userTableContainer table');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    // Nếu input trống -> show lại tất cả
    if (filter === "") {
        rows.forEach(row => row.style.display = '');
        currentPage = 1;
        paginateTable();
        return;
    }

    let found = false;
    
    rows.forEach(row => {
        // Lấy dữ liệu từ 3 cột: Cột 1 (Tên), Cột 2 (Họ tên), Cột 6 (Vai trò)
        const rawUsername = row.querySelector('td:nth-child(1)')?.textContent || '';
        const rawFullname = row.querySelector('td:nth-child(2)')?.textContent || '';
        const rawRole = row.querySelector('td:nth-child(6)')?.textContent || ''; 

        // Chuyển về dạng không dấu chữ thường để so sánh
        const username = normalizeString(rawUsername);
        const fullname = normalizeString(rawFullname);
        const role = normalizeString(rawRole); 

        // Điều kiện: Từ khóa nằm trong Username HOẶC Fullname HOẶC Role
        if (username.includes(filter) || fullname.includes(filter) || role.includes(filter)) {
            found = true;
            row.style.display = ''; // Hiện hàng này
        } else {
            row.style.display = 'none'; // Ẩn hàng này
        }
    });

    // Xử lý khi không tìm thấy kết quả nào
    if (!found) {
        alert('Không tìm thấy người dùng!');
        inputField.value = ""; 
        rows.forEach(row => row.style.display = ''); 
    }

    currentPage = 1;
    paginateTable();
}


// Gán sự kiện vào nút tìm kiếm để chỉ tìm khi nhấn nút
document.querySelector('.search').addEventListener('click', searchUser);

// Ngăn việc tìm kiếm khi nhập chữ, chỉ tìm khi nhấn nút
document.querySelector('.find').addEventListener('input', function () {
    if (this.value.trim() === "") {
        searchUser(); // Khi ô tìm kiếm trống, gọi lại để reset bảng
    }
});

let pendingResetUsername = null;

// 1. Hiển thị Modal xác nhận Reset mật khẩu
function confirmResetPassword(username) {
    pendingResetUsername = username;
    const modal = document.getElementById('confirmResetModal');
    if (modal) {
        modal.style.display = 'flex';
    } else {
        // Backup nếu Thảo chưa kịp thêm Modal vào HTML
        if (confirm(`Bạn có chắc chắn muốn đặt lại mật khẩu cho người dùng "${username}" về mặc định (123456) không?`)) {
            executeResetPassword(username);
        }
    }
}

// 2. Đóng Modal Reset
function closeResetModal() {
    const modal = document.getElementById('confirmResetModal');
    if (modal) modal.style.display = 'none';
    pendingResetUsername = null;
}

// 3. Thực thi gọi API Reset mật khẩu
function executeResetPassword(username) {
    const formData = new FormData();
    formData.append('username', username);
    formData.append('action', 'reset_password');

    fetch('Api_php/lock-user.php', { // Thảo có thể dùng chung file lock-user hoặc tạo file mới reset-user.php
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Hiển thị thông báo "Reset thành công. Mật khẩu mới là..."
        closeResetModal();
        loadUserTable(); // Load lại bảng
    })
    .catch(error => {
        console.error("Lỗi Reset mật khẩu:", error);
        alert("Đã xảy ra lỗi hệ thống.");
    });
}

// 4. Gán sự kiện cho nút "Đồng ý" trong Modal Reset
// Lưu ý: Đảm bảo nút trong HTML có id="confirmResetBtn"
const confirmResetBtn = document.getElementById('confirmResetBtn');
if (confirmResetBtn) {
    confirmResetBtn.addEventListener('click', () => {
        if (pendingResetUsername) {
            executeResetPassword(pendingResetUsername);
        }
    });
}