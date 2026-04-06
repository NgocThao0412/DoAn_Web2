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

    // Đóng giỏ hàng
    const closeBtn = document.querySelector(".close");
    if (closeBtn) {
        closeBtn.addEventListener("click", function() {
            if (shoppingCart) shoppingCart.classList.remove("active");
            if (blurOverlay) blurOverlay.classList.remove("active");
        });
    }
    if (blurOverlay) {
        blurOverlay.addEventListener("click", function() {
            if (shoppingCart) shoppingCart.classList.remove("active");
            if (blurOverlay) blurOverlay.classList.remove("active");
        });
    }

    
    if (plusBtn && quantityInput) {
        plusBtn.addEventListener("click", function () {
            let currentValue = parseInt(quantityInput.value) || 1;
            let maxStock = parseInt(quantityInput.dataset.max) || 100;

            if (currentValue >= maxStock) {
                alert("Sản phẩm không đủ!");
                quantityInput.value = maxStock;
                quantityInput.disabled = true;
                plusBtn.disabled = true;
                return;
            }

            quantityInput.value = currentValue + 1;
        });
    }

    
    if (minusBtn && quantityInput) {
        minusBtn.addEventListener("click", function () {
            let currentValue = parseInt(quantityInput.value) || 1;

            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
    }

    
    if (inputElement) {
        let maxStock = parseInt(inputElement.dataset.max) || 100;
        
        inputElement.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, '');
            let val = parseInt(this.value) || 0;

            if (val > maxStock) {
                alert("Sản phẩm không đủ!");
                this.value = maxStock;
            }
        });

        inputElement.addEventListener("blur", function () {
            if (this.value === "" || parseInt(this.value) < 1) {
                this.value = 1;
                this.disabled = false;
                if (plusBtn) plusBtn.disabled = false;
            }
        });
    }

    updateCartCount();
});


function fetchCart() {
    const shoppingCart = document.querySelector(".shopping-cart");
    const blurOverlay = document.querySelector(".blur-overlay");
    
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

        const payButton = document.querySelector(".pay-btn-link");
        const payLink = document.querySelector(".pay-link");
        
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


document.querySelectorAll('.add-to-cart, .add-to-cart-detail').forEach(btn => {
    btn.addEventListener('click', function () {
        const productId = this.dataset.id;
        
        
        const quantityInput = this.closest('.content')?.querySelector('.quantity');
        let quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;

        
        if (quantity > 100) {
            alert("Số lượng tối đa là 100 sản phẩm.");
            return;
        }

        if (quantity < 1) {
            alert("Số lượng phải >= 1.");
            return;
        }

        
        fetch('includes/cart_action_detail.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=add&product_id=${productId}&quantity=${quantity}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                
                alert(" Đã thêm vào giỏ hàng!");
                if (typeof fetchCart !== 'undefined') fetchCart();
                if (typeof updateCartCount !== 'undefined') updateCartCount();
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error("Lỗi:", err));
    });
});