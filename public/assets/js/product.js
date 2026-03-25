document.addEventListener("DOMContentLoaded", function () {

    const minusBtn = document.querySelector(".minus-btn");
    const plusBtn = document.querySelector(".plus-btn");
    const quantityInput = document.querySelector(".quantity-button input");
    const inputElement = document.querySelector(".quantity-button input");

    const shoppingCart = document.querySelector(".shopping-cart");
    const addToCartBtn = document.querySelector(".add-to-cart-detail");
    const blurOverlay = document.querySelector(".blur-overlay");

    const payButton = document.querySelector(".pay-btn-link");
    const payLink = document.querySelector(".pay-link");

    // 🔼 Tăng số lượng
    if (plusBtn && quantityInput) {
        plusBtn.addEventListener("click", function () {
            let currentValue = parseInt(quantityInput.value) || 1;

            if (currentValue >= 100) {
                alert("Chỉ được mua tối đa 100 sản phẩm.");
                quantityInput.value = 100;
                return;
            }

            quantityInput.value = currentValue + 1;
        });
    }

    // 🔽 Giảm số lượng
    if (minusBtn && quantityInput) {
        minusBtn.addEventListener("click", function () {
            let currentValue = parseInt(quantityInput.value) || 1;

            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
    }

    // ✏️ Nhập tay (chỉ cho nhập số)
    if (inputElement) {
        inputElement.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, '');

            if (this.value !== "" && parseInt(this.value) > 100) {
                alert("Số lượng tối đa là 100.");
                this.value = 100;
            }
        });

        inputElement.addEventListener("blur", function () {
            if (this.value === "" || parseInt(this.value) < 1) {
                this.value = 1;
            }
        });
    }

    // 🛒 Thêm vào giỏ hàng
    if (addToCartBtn && quantityInput) {
        addToCartBtn.addEventListener("click", function () {
            const productId = this.dataset.id;
            const quantity = parseInt(quantityInput.value) || 1;

            fetch("includes/cart_action_detail.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    action: "add",
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                alert(data.message || "Đã thêm vào giỏ hàng");

                if (data.success) {
                    fetchCart();
                    updateCartCount();
                }
            })
            .catch(err => console.error("Lỗi:", err));
        });
    }

    // 📦 Lấy dữ liệu giỏ hàng
    function fetchCart() {
        fetch("includes/cart.php", {
            method: "GET",
            credentials: "include"
        })
        .then(res => res.text())
        .then(data => {
            const cartContent = document.querySelector(".cart-scroll");

            if (cartContent) {
                if (data.includes("empty-cart")) {
                    cartContent.innerHTML = `
                        <div class="emptyCart">
                            <div class="close-icon">
                                <ion-icon name="alert-circle-outline"></ion-icon>
                            </div>
                            <p class="empty-cart">Giỏ hàng đang trống</p>
                        </div>
                    `;
                } else {
                    cartContent.innerHTML = data;
                }
            }

            if (shoppingCart) shoppingCart.classList.add("active");
            if (blurOverlay) blurOverlay.classList.add("active");
        })
        .catch(err => console.error("Lỗi giỏ hàng:", err));
    }

    // 🔄 Cập nhật số lượng giỏ hàng
    function updateCartCount() {
        fetch("includes/cart_action.php?cart_count=1", {
            method: "GET",
            credentials: "include"
        })
        .then(res => res.json())
        .then(data => {
            document.querySelectorAll(".cart-count").forEach(el => {
                el.textContent = data.count || 0;
            });

            if (payButton && payLink) {
                if (data.count > 0) {
                    payButton.removeAttribute("disabled");
                    payButton.classList.remove("disabled");
                    payLink.classList.remove("disabled-link");
                } else {
                    payButton.setAttribute("disabled", "true");
                    payButton.classList.add("disabled");
                    payLink.classList.add("disabled-link");
                }
            }
        })
        .catch(err => console.error("Lỗi cập nhật số lượng:", err));
    }

    updateCartCount();
});