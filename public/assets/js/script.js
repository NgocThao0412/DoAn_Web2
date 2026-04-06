function toggleMenu(hamburger) {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('active');
    
    
    document.querySelectorAll('.hamburger').forEach(icon => {
        icon.classList.toggle('active');
    });
}

const logo = document.querySelector('.logo');
logo.addEventListener('click', function(e) {

    e.preventDefault();
    window.location.href = 'home'; 
});

function myFunction() {
    const input = document.getElementById('search');
    
}


function displayWelcomeMessage() {
    
    if (localStorage.getItem("welcomeShown") === "true") {
        return;
    }

    fetch('includes/session.php', {
        method: 'GET',
        credentials: 'include'  
    })
    .then(response => response.json())
    .then(data => {
        if (data.loggedIn && data.username) {
            const notificate = document.getElementById("notificate");
            const message = document.getElementById("message");

            
            message.innerHTML = `Xin chào, ${data.username}`;
            notificate.classList.add("show");

            
            setTimeout(() => {
                notificate.classList.remove("show");
                notificate.classList.add("hide");

                
                setTimeout(() => {
                    notificate.style.display = "none";
                }, 1000); 
            }, 2000);

            
            localStorage.setItem("welcomeShown", "true");
        }
    })
    .catch(error => console.error("Error fetching session data:", error));
}



document.addEventListener("DOMContentLoaded", function () {
    const url = new URL(window.location);
    if (url.searchParams.has('term')) {
      url.searchParams.delete('term');
      window.history.replaceState(null, '', url.toString());
    }

    const blurOverlay = document.querySelector(".blur-overlay");
    const btnCart = document.querySelectorAll(".add-to-cart"); 
    const shoppingCart = document.querySelector(".shopping-cart"); 
    const cartBtn = document.getElementById("cart-btn"); 
    const cartBtns = document.querySelectorAll(".sp-cart");
    const closeBtns = document.querySelectorAll(".close");
    const loginBtn = document.getElementById("login-btn");
    const logoutBtn = document.getElementById("logout-btn");
    const cartCounts = document.querySelectorAll(".cart-count");    
    const payButton = document.querySelector(".pay-btn-link");
    const payLink = document.querySelector(".pay-link");

    if (btnCart.length > 0 && !window.location.search.includes('page=product')) {
        btnCart.forEach(button => {
            button.addEventListener("click", function () {
                const productId = this.dataset.id;
                addToCart(productId, this);
            });
        });
    }

    // Kiểm tra trạng thái đăng nhập từ session
    function checkLoginStatus(callback) {
        fetch("./includes/session.php", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            console.log("Session Data:", data);
            if (data.loggedIn) {
                document.body.classList.add("logged-in");
            } else {
                document.body.classList.remove("logged-in");
            }
            updateUI();
            if (callback) callback(data.loggedIn);
        })
        .catch(error => console.error("Lỗi kiểm tra session:", error));
    }

    // Cập nhật giao diện đăng nhập
    function updateUI() {
        const isLoggedIn = document.body.classList.contains("logged-in");
        if (loginBtn) loginBtn.style.display = isLoggedIn ? "none" : "inline-block";
        if (logoutBtn) logoutBtn.style.display = isLoggedIn ? "inline-block" : "none";
    }


    if (cartBtns.length > 0) {
        cartBtns.forEach(cartBtn => {
            cartBtn.addEventListener("click", () => {
                checkLoginStatus((isLoggedIn) => {
                    if (isLoggedIn) {
                        fetchCart(); 
                        shoppingCart.classList.add("active");
                        if (blurOverlay) blurOverlay.classList.add("active");
                    } else {
                        alert("Bạn cần đăng nhập để xem giỏ hàng");
                        window.location.href = "login";
                    }
                });
            });
        });
    }

    displayWelcomeMessage();

    checkLoginStatus((isLoggedIn)=>{
        if (!isLoggedIn) {
            console.log("Không đăng nhập, xóa flag welcomeShown");
            localStorage.removeItem("welcomeShown");
            console.log("welcomeShown flag removed:", localStorage.getItem("welcomeShown"));
        }
    });
    
function closeCart() {
    if (shoppingCart) shoppingCart.classList.remove("active");
    if (blurOverlay) blurOverlay.classList.remove("active");
}

if (blurOverlay) {
    blurOverlay.addEventListener("click", closeCart);
}

if (shoppingCart) {
    shoppingCart.addEventListener("click", function (e) {
        e.stopPropagation();
    });
}

    
    closeBtns.forEach(button => {
        button.addEventListener("click", function (event) {
            event.stopPropagation();
            shoppingCart.classList.remove("active");
            if (blurOverlay) blurOverlay.classList.remove("active");
            console.log("Đóng giỏ hàng");
        });
    });
  
    
    function addToCart(productId, button) {
        
        let quantity = 1;
        if (button) {
            const quantityInput = button.closest('.content')?.querySelector('.quantity');
            if (quantityInput) {
                quantity = Math.max(1, parseInt(quantityInput.value) || 1);
            }
        }
        
        console.log("Đang gửi request thêm sản phẩm:", productId, "với số lượng:", quantity);
        fetch("./includes/cart_action.php", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "add", product_id: parseInt(productId), quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Response từ server:", data);
            if (data.success) {
                alert("Đã thêm vào giỏ hàng");
                fetchCart();
                updateCartCount();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Lỗi khi thêm vào giỏ hàng:", error));
    }

    // Hàm xóa sản phẩm khỏi giỏ hàng
    function removeFromCart(productId) {
        fetch("./includes/cart_action.php", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "remove", product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCart();
                updateCartCount();
            } else {
                alert("Lỗi xóa sản phẩm: " + data.message);
            }
        })
        .catch(error => console.error("Lỗi khi xóa sản phẩm:", error));
    }

    
    function updateQuantity(productId, change) {
        let inputField = document.getElementById(`quantity_${productId}`);
        let newQuantity = parseInt(inputField.value) + change;
        let maxStock = parseInt(inputField.dataset.stock) || 100;
    
        if (newQuantity < 1) newQuantity = 1;
        if (newQuantity > maxStock) {
            alert("Sản phẩm không đủ!");
            inputField.value = maxStock;
            inputField.disabled = true;
            return;
        }
        
        inputField.disabled = false;
        inputField.value = newQuantity;
        sendUpdateRequest(productId, newQuantity);
        updateTotalPrice(); 
    }
    

    
    function updateQuantityDirectly(productId, value) {
        let newQuantity = parseInt(value);
        let inputField = document.getElementById(`quantity_${productId}`);
        let maxStock = parseInt(inputField.dataset.stock) || 100;
    
        if (isNaN(newQuantity) || newQuantity < 1) {
            newQuantity = 1;
        }
        
        if (newQuantity > maxStock) {
            alert("Sản phẩm không đủ!");
            newQuantity = maxStock;
            inputField.disabled = true;
            document.getElementById(`quantity_${productId}`).value = newQuantity;
            return;
        }
        
        inputField.disabled = false;
        document.getElementById(`quantity_${productId}`).value = newQuantity;
        sendUpdateRequest(productId, newQuantity);
        updateTotalPrice(); 
    }
    

    
    function sendUpdateRequest(productId, quantity) {
        fetch("./includes/cart_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify({
                action: "update",
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCart(); 
                updateCartCount(); 
                updateTotalPrice(); 
            } else {
                alert("Xảy ra lỗi trong quá trình cập nhật giỏ hàng");
            }
        })
        .catch(error => console.error("Lỗi:", error));
    }
    
   function updateTotalPrice() {
    const items = document.querySelectorAll(".cart-item, .Cake-infor");

    if (!items.length) return;

    let total = 0;

    items.forEach(item => {

        
        const priceEl =
            item.querySelector(".price") ||
            item.querySelector(".price-10") ||
            item.querySelector(".price-product");

        
        const quantityEl =
            item.querySelector(".quantity") ||
            
            item.querySelector("input");
        if (!priceEl || !quantityEl) return;

        const priceText = priceEl.textContent;
        if (!priceText) return;

        const price = parseFloat(priceText.replace(/[^\d]/g, "")) || 0;
        const quantity = parseInt(quantityEl.value) || 1;

        total += price * quantity;
    });

    const totalEl = document.querySelector(".total-price");

    if (totalEl) {
        totalEl.textContent = total.toLocaleString() + "đ";
    }
}
    

    
    function fetchCart() {
        fetch("./includes/cart.php", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.text())
        .then(data => {
            console.log("Dữ liệu giỏ hàng nhận được:", data);
            const cartContent = document.querySelector(".cart-scroll");
            if (cartContent) {
                if (data.includes("empty-cart")) {
                    cartContent.innerHTML = `
                        <div class="emptyCart">
                            <div class="close-icon"> <ion-icon name="alert-circle-outline"></ion-icon> </div>
                            <p class="empty-cart">Không có sản phẩm trong giỏ </p>
                        </div>
                    `;
                }
                else {
                    
                    cartContent.innerHTML = data;
                }
            } else {
                console.error("Không tìm thấy phần tử .cart-content trên trang!");
            }
    
            
            shoppingCart.classList.add("active");
            if (blurOverlay) blurOverlay.classList.add("active");
        })
        .catch(error => console.error("Lỗi khi fetch giỏ hàng:", error));
    }
    

    
    function updateCartCount() {
        fetch("./includes/cart_action.php?cart_count=1", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            console.log("Số lượng giỏ hàng:", data.count);
    
            
            document.querySelectorAll(".cart-count").forEach(cartCount => {
                cartCount.textContent = data.count || 0;
            });
    
            
            if (data.count > 0) {
                payButton.removeAttribute("disabled");
                payButton.classList.remove("disabled");
                payLink.classList.remove("disabled-link"); 
            } else {
                payButton.setAttribute("disabled", "true");
                payButton.classList.add("disabled");
                payLink.classList.add("disabled-link"); 
            }
        })
        .catch(error => console.error("Lỗi khi lấy số lượng giỏ hàng:", error));
    }
    
    // Ngăn điều hướng nếu giỏ hàng rỗng
    payLink.addEventListener("click", function(event) {
        if (payButton.hasAttribute("disabled")) {
            event.preventDefault(); 
            alert("Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi tiến hành thanh toán!");
        }
    });
    

    // Khi trang tải xong, kiểm tra trạng thái đăng nhập và cập nhật số lượng giỏ hàng
    checkLoginStatus();
    updateCartCount();
    window.updateQuantity = updateQuantity;
    window.removeFromCart = removeFromCart;
    window.updateQuantityDirectly = updateQuantityDirectly;
    

    

        
    let itemsPerPage = 8; 
    let currentPage = 1;
    let selectedCategory = "all"; 


    document.querySelectorAll(".nav-item").forEach(item => {
        item.addEventListener("click", () => {
            const category = item.textContent.trim().toLowerCase();
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('category', category);
            urlParams.set('page', '1'); 
            window.location.search = urlParams.toString();
        });
    });
    

    
    document.querySelectorAll(".nav-item").forEach(label => {
        label.addEventListener("click", function () {
            document.querySelectorAll(".nav-item").forEach(l => l.classList.remove("active"));
            this.classList.add("active");
        });
    });


      

    

    
    const searchTerm = '';
    const urll = `pages/getAllProduct.php?term=${encodeURIComponent(searchTerm)}`;
    console.log('Fetching:', urll); 

    fetch(urll)
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));

    
    
function removeVietnameseTones(str) {
    return str.normalize("NFD")
              .replace(/[\u0300-\u036f]/g, "")
              .replace(/đ/g, "d")
              .replace(/Đ/g, "D");
}


function showHints(inputField) {
    const rawInput = inputField.value.trim();
    const searchTerm = removeVietnameseTones(rawInput.toLowerCase());
    const hintContainer = inputField.closest(".search-container").querySelector(".hint-container");

    if (!searchTerm) {
        hintContainer.innerHTML = "";
        hintContainer.style.display = "none";
        return;
    }

    fetch(`pages/getAllProduct.php?term=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(products => {
            hintContainer.innerHTML = "";

            if (!products || products.length === 0) {
                const notFoundItem = document.createElement("div");
                notFoundItem.className = "hint-item";
                notFoundItem.textContent = "Không tìm thấy sản phẩm";
                notFoundItem.style.textAlign = "center";
                notFoundItem.style.padding = "8px";
                notFoundItem.style.color = "#999";
                hintContainer.appendChild(notFoundItem);
                hintContainer.style.display = "block";
                return;
            }
            console.log(products);
            console.log(typeof products);
            products.forEach(item => {
                const hintItem = document.createElement("div");
                hintItem.className = "hint-item";
                hintItem.dataset.productId = item.product_id;
                hintItem.innerHTML = `
                    <img src="${item.image}" alt="${item.product_name}" 
                         style="width:30px; height:30px; margin-right:10px; vertical-align:middle;">
                    ${item.product_name}
                `;

                hintItem.addEventListener("mousedown", e => e.preventDefault()); // ngăn mất focus
                hintItem.addEventListener("click", () => {
                    window.location.href = `home?pages=product&id=${item.product_id}`;
                });

                hintContainer.appendChild(hintItem);
            });

            hintContainer.style.display = "block";
        })
        .catch(error => console.error("Lỗi khi lấy gợi ý:", error));
}


    const searchInputs = document.querySelectorAll(".search-input");
    const searchButtons = document.querySelectorAll(".searchBtn");


    function removeVietnameseTones(str) {
        return str.normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/đ/g, "d")
            .replace(/Đ/g, "D");
    }

    let inputTimeout = null;
    let isSelectingHint = false;
    let lastInputValue = "";
    
    searchInputs.forEach(input => {
        
        input.addEventListener("compositionend", function () {
            lastInputValue = this.value.trim();
        });
    
        input.addEventListener('input', function () {
            const container = this.closest(".search-container");
            if (!container) return;

            const hintContainer = container.querySelector(".hint-container");
            if (!hintContainer) return;
    
            const value = this.value.trim();
            lastInputValue = value;
    
            
            if (inputTimeout) clearTimeout(inputTimeout);
    
            if (value === '') {
                hintContainer.innerHTML = '';
                hintContainer.style.display = 'none';
    
                
                const url = new URL(window.location);
                url.searchParams.delete('term');
                url.searchParams.set('page', 1);
                window.location.href = url.toString();
                return;
            }
    
            
            inputTimeout = setTimeout(() => {
                showHints(input);
            }, 500);
        });
    
        input.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
    
                const raw = lastInputValue || this.value.trim();
                if (raw) {
                    const term = encodeURIComponent(raw);
                    window.location.href = `?term=${term}&page=1`;
                }
            }
        });
    
        input.addEventListener("blur", function () {
            setTimeout(() => {
                if (!isSelectingHint) {
                    const container = this.closest(".search-container");
                    if (!container) return;

                    const hintContainer = container.querySelector(".hint-container");
                    if (hintContainer) {
                    hintContainer.style.display = "none";
}
                }
                isSelectingHint = false;
            }, 200);
        });
    });
    
    
    document.addEventListener("mousedown", e => {
        if (e.target.closest(".hint-item")) {
            isSelectingHint = true;
        }
    });
    
    
    
    document.addEventListener("mousedown", e => {
        if (e.target.closest(".hint-item")) {
            isSelectingHint = true;
        }
    });
    
    
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".search-container") && !event.target.closest(".hint-item")) {
            document.querySelectorAll(".hint-container").forEach(hint => {
                hint.innerHTML = "";
                hint.style.display = "none";
            });
        }
    });
    
    
    searchButtons.forEach(button => {
        button.addEventListener("click", function () {
            const searchInput = button.closest(".input-wrapper").querySelector(".search-input");
            if (searchInput) {
                const raw = searchInput.value.trim();
                if (raw) {
                    const term = encodeURIComponent(raw);
                    window.location.href = `?term=${term}&page=1`;
                }
            }
        });
    });
    


    
});

/*scroll*/
let lastScrollTop = 0;
const header = document.querySelector('.header');
const mediaQuery = window.matchMedia('(max-width: 1390px)');

function handleScroll() {
    if (mediaQuery.matches) {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop) {
            
            header.classList.add('hide');
        } else {
            
            header.classList.remove('hide');
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    } else {
        
        header.classList.remove('hide');
    }
}

window.addEventListener('scroll', handleScroll);

window.addEventListener('resize', handleScroll);

handleScroll();

window.onscroll = function () {
    toggleBackToTopButton();
};

function toggleBackToTopButton() {
    const backToTopButton = document.getElementById("backToTop");
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        backToTopButton.style.display = "block";
    } else {
        backToTopButton.style.display = "none";
    }
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}


$(document).ready(function(){
    $('.carousel_wrapper').slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        prevArrow: $('.custom-prev'),
        nextArrow: $('.custom-next'),
        dotsClass: 'carousel-dots',
        responsive: [
            {
                breakpoint: 1197,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    dots: true
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    dots: true,
                    arrows: false,
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    dots: true,
                    arrows: false
                }
            }
        ]
    });
});

document.querySelector("form").addEventListener("submit", function(e) {
    const minPrice = parseFloat(document.querySelector('input[name="minPrice"]').value) || 0;
    const maxPrice = parseFloat(document.querySelector('input[name="maxPrice"]').value) || 0;

    if (minPrice > maxPrice && maxPrice !== 0) {
        e.preventDefault();
        alert("Giá từ không được lớn hơn giá đến!");
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        const minInput = form.querySelector('input[name="minPrice"]');
        const maxInput = form.querySelector('input[name="maxPrice"]');
        const errorDiv = form.querySelector('#price-error');

        if (!minInput || !maxInput) return; 

        form.addEventListener('submit', function (e) {
    
            const minPrice = parseFloat(minInput.value) || 0;
            const maxPrice = parseFloat(maxInput.value) || 0;

            if (maxPrice !== 0 && minPrice > maxPrice) {
                e.preventDefault();
                if (errorDiv) {
                    errorDiv.textContent = "Giá từ phải nhỏ hơn hoặc bằng giá đến!";
                    errorDiv.style.display = "block";
                } else {
                    alert("Giá từ phải nhỏ hơn hoặc bằng giá đến!");
                }
            } else if (errorDiv) {
                errorDiv.style.display = "none";
                errorDiv.textContent = "";
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function () {
    var mainForm = document.getElementById('main-search-form');
    var filtersForm = document.getElementById('filters-form');

    if (!mainForm || !filtersForm) return;

    mainForm.addEventListener('submit', function(e) {
        e.preventDefault(); 

        
        mainForm.querySelectorAll('[data-filter="true"]').forEach(el => el.remove());

        ['searchCategory', 'minPrice', 'maxPrice'].forEach(function(name) {
            var field = filtersForm.querySelector('[name="' + name + '"]');
            if (field && field.value && field.value !== 'all') {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = field.value;
                input.setAttribute('data-filter', 'true');
                mainForm.appendChild(input);
            }
        });

        mainForm.submit(); 
    });
});