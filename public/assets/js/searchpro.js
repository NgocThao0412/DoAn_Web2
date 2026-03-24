document.addEventListener("DOMContentLoaded", function () {
    const shoppingCart = document.querySelector(".shopping-cart");
    const cartCount = document.querySelector(".cart-count");
    const blurOverlay = document.querySelector(".blur-overlay");

    // 🛒 Xử lý thêm vào giỏ hàng (GÁN HÀM TOÀN CỤC)
    window.addToCartBtn = function (productId, productName, price) { 
        fetch("includes/cart_action_detail.php", { 
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "add",
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCart(); 
                updateCartCount(); 
            }
        })
        .catch(error => console.error("Lỗi:", error));
    };

    function fetchCart() {
        fetch("includes/cart.php", {
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
                            <p class="empty-cart">Your cart is empty.</p>
                        </div>
                    `;
                } else {
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
        fetch("includes/cart_action.php?cart_count=1", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            console.log("Số lượng giỏ hàng:", data.count);
            if (cartCount) {
                cartCount.textContent = data.count || 0;
            }
        })
        .catch(error => console.error("Lỗi khi lấy số lượng giỏ hàng:", error));
    }

    updateCartCount();
});
document.addEventListener("DOMContentLoaded", function () {
    const shoppingCart = document.querySelector(".shopping-cart");
    const cartCount = document.querySelector(".cart-count");
    const blurOverlay = document.querySelector(".blur-overlay");

    // 🛒 Xử lý thêm vào giỏ hàng (GÁN HÀM TOÀN CỤC)
    window.addToCartBtn = function (productId, productName, price) { 
        fetch("includes/cart_action_detail.php", { 
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "add",
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCart(); 
                updateCartCount(); 
            }
        })
        .catch(error => console.error("Lỗi:", error));
    };

    function fetchCart() {
        fetch("includes/cart.php", {
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
                            <p class="empty-cart">Your cart is empty.</p>
                        </div>
                    `;
                } else {
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
        fetch("includes/cart_action.php?cart_count=1", {
            method: "GET",
            credentials: "include"
        })
        .then(response => response.json())
        .then(data => {
            console.log("Số lượng giỏ hàng:", data.count);
            if (cartCount) {
                cartCount.textContent = data.count || 0;
            }
        })
        .catch(error => console.error("Lỗi khi lấy số lượng giỏ hàng:", error));
    }

    updateCartCount();
});
