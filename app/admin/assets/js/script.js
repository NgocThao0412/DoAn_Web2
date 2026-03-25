// Toggle Menu Mobile
function toggleMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu) {
        mobileMenu.classList.toggle('active');
    }

    // Toggle tất cả hamburger menu (header & mobile)
    document.querySelectorAll('.hamburger').forEach(icon => {
        icon.classList.toggle('active');
    });
}

// Logo → chuyển hướng về home.php

document.querySelectorAll('.logo').forEach(logo => {
    logo.addEventListener('click', function (e) {
        e.preventDefault();
        window.location.href = 'home';
    });
});

// Toggle nhóm chức năng (Grade)
// Hoạt động cho cả PC + mobile

function toggleGrade(contentId, chevronId) {
    const chevrons = document.querySelectorAll('#' + chevronId);
    const contents = document.querySelectorAll('#' + contentId);

    chevrons.forEach(chevron => {
        chevron.classList.toggle('up');
        chevron.classList.toggle('down');
    });

    contents.forEach(content => {
        const isHidden = content.style.display === "none" || content.style.display === "";
        content.style.display = isHidden ? "block" : "none";
        console.log(isHidden ? "Hiện danh sách" : "Ẩn danh sách");
    });
}

// Auto mở menu theo trang hiện tại

document.addEventListener("DOMContentLoaded", function () {
    const currentPage = window.location.pathname.split("/").pop().replace(".php", "");

    const menuMap = {
    "list-product": "gradeProduct",
    "add-product": "gradeProduct",
    "add-category": "gradeProduct",

    "manager-user": "gradeUser",
    "add-user": "gradeUser",

    "import-add": "gradeImport",
    "import-list": "gradeImport",

    "price-manager": "gradePrice",

    "order-manager": "gradeOrder",

    "stock-report": "gradeStock",
    "stock-warning": "gradeStock"
    };

    if (menuMap[currentPage]) {
        const gradeID = menuMap[currentPage];
        const chevronID = "chevron" + gradeID.replace("grade", "");
        toggleGrade(menuMap[currentPage], chevronID);
    }
});

// HIỆN LỜI CHÀO (Chỉ 1 lần sau login)

function displayWelcomeMessage() {
    if (localStorage.getItem("welcomeShownAdmin") === "true") {
        return;
    }

    fetch('Api_php/session-admin.php', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.loggedIn && data.username) {
            const notificate = document.getElementById("notificate");
            const message = document.getElementById("message");

            if (notificate && message) {
                message.innerHTML = `Chào mừng trở lại, ${data.username}!<br>Chúc bạn một ngày tốt lành!`;

                notificate.style.display = "block";
                notificate.classList.add("show");

                setTimeout(() => {
                    notificate.classList.remove("show");
                    notificate.classList.add("hide");

                    setTimeout(() => {
                        notificate.style.display = "none";
                    }, 1000);
                }, 2000);

                localStorage.setItem("welcomeShownAdmin", "true");
            }
        }
    })
    .catch(error => console.error("Lỗi lấy session:", error));
}

// KIỂM TRA LOGIN SESSION

function checkLoginStatus(callback) {
    console.log("Đang kiểm tra session...");

    fetch("Api_php/session-admin.php", {
        method: "GET",
        credentials: "include"
    })
    .then(response => response.json())
    .then(data => {
        console.log("Session trả về:", data);

        // Chưa đăng nhập → quay về login
        if (!data.loggedIn) {
            console.warn("Chưa đăng nhập → chuyển về login");
            window.location.href = "login";
            return;
        }

        // Nếu tài khoản bị khóa
        if (data.status && data.status.toLowerCase() === "locked") {
            alert("Tài khoản của bạn đã bị khóa. Hệ thống sẽ đưa bạn về trang đăng nhập.");
            window.location.href = "login";
            return;
        }

        document.body.classList.toggle("logged-in", data.loggedIn);

        if (callback) callback(data.loggedIn);
    })
    .catch(error => console.error("Lỗi session:", error));
}

// LẤY THÔNG TIN ADMIN & GẮN VÀO DROPDOWN

function loadAdminInfo() {
    fetch("Api_php/session-admin.php", {
        method: "GET",
        credentials: "include"
    })
    .then(response => response.json())
    .then(data => {
        if (data.loggedIn) {

            const nameEl = document.getElementById("adminName");
            const emailEl = document.getElementById("adminEmail");

            if (nameEl) nameEl.innerText = data.username;
            if (emailEl) {} emailEl.innerText = data.email; 
        }
    })
    .catch(error => console.error("Lỗi load admin:", error));
} 
document.addEventListener("DOMContentLoaded", function () {

    checkLoginStatus((isLoggedIn) => {
        if (!isLoggedIn) {
            localStorage.removeItem("welcomeShownAdmin");
        }
    });

    displayWelcomeMessage();

    // người dùng nhấn tên tài khoản thì sang trang thông tin
    const userBtn = document.getElementById('user-btn');
    if (userBtn) {
        userBtn.addEventListener('click', function(e) {
            window.location.href = ADMIN_URL + 'profit';
        });
    }
    const mobileUserBtn = document.getElementById('mobile-user-btn');
    if (mobileUserBtn) {
        mobileUserBtn.addEventListener('click', function(e) {
            window.location.href = ADMIN_URL + 'profit';
        });
    }

});

document.addEventListener("DOMContentLoaded", function () {
    // 1. Lấy tên file hiện tại từ URL (ví dụ: list-product)
    const currentPath = window.location.pathname.split("/").pop().replace(".php", "");

    // 2. Tìm tất cả các link trong menu
    const menuLinks = document.querySelectorAll(".colorForLink");
    const gradeButtons = document.querySelectorAll(".grade-button");

    // Xử lý cho các mục con (subject-item)
    menuLinks.forEach(link => {
        const href = link.getAttribute("href").replace(".php", "");
        
        if (currentPath === href) {
            const subjectItem = link.closest(".subject-item");
            if (subjectItem) {
                subjectItem.classList.add("active-nav"); // Thêm class in đậm

                // Tự động mở menu cha (subject-list)
                const parentList = subjectItem.closest(".subject-list");
                if (parentList) {
                    parentList.style.display = "block";
                    
                    // Xoay mũi tên chevron của menu cha
                    const gradeId = parentList.id;
                    const chevronId = "chevron" + gradeId.replace("grade", "");
                    const chevron = document.getElementById(chevronId);
                    if (chevron) {
                        chevron.classList.remove("down");
                        chevron.classList.add("up");
                    }
                }
            }
        }
    });

    // Xử lý cho các mục đơn (Quản lý người dùng, Đơn hàng, v.v.)
    gradeButtons.forEach(btn => {
        const onClickAttr = btn.getAttribute("onclick") || "";
        if (onClickAttr.includes(currentPath) && !btn.querySelector(".chevron")) {
            btn.classList.add("active-nav");
        }
    });
});