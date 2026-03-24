/*Home data*/
function myFunction() {
    const input = document.getElementById('search');
    // Add your search functionality here
}

document.addEventListener('DOMContentLoaded', function() {

    
    // Các biến và sự kiện khác của trang...

     const searchInputs = document.querySelectorAll(".search-input");
     const searchButtons = document.querySelectorAll(".searchBtn");

    // Hàm tìm kiếm sản phẩm (giữ nguyên nếu cần)
     function searchItems(searchTerm) {
         let allProducts = document.querySelectorAll("#product-container .movie-item");
         if (searchTerm.trim() === "") {
             allProducts.forEach(product => product.style.display = "block");
             const noResultMessage = document.getElementById("no-result-message");
             if (noResultMessage) {
                 noResultMessage.style.display = "none";
             }
            return;
         }
         let found = false;
         allProducts.forEach(product => {
             let productName = product.querySelector(".title").innerText.toLowerCase();
         if (productName.includes(searchTerm.toLowerCase())) {
                 product.style.display = "block";
             found = true;
             } else {
                product.style.display = "none";
          }
        });
        const noResultMessage = document.getElementById("no-result-message");
        if (!found) {
            if (noResultMessage) {
                noResultMessage.style.display = "block";
                noResultMessage.textContent = "Không tìm thấy sản phẩm nào!";
            }
        } else {
            if (noResultMessage) {
                noResultMessage.style.display = "none";
            }
        }
    }

       let isSelectingHint = false;
    // Hàm hiển thị gợi ý tìm kiếm
    function showHints(inputField) {
        const searchTerm = inputField.value.trim();
        const container = inputField.closest(".search-container") || inputField.closest(".products-search-container");
        const hintContainer = container ? container.querySelector(".hint-container") : null;

        if (!searchTerm || !hintContainer) {
            hintContainer.innerHTML = "";
            hintContainer.style.display = "none";
            return;
        }

        fetch(`pages/getAllProduct.php?term=${encodeURIComponent(searchTerm)}`)
           .then(response => response.json())
           .then(products => {
               hintContainer.innerHTML = "";
               if (!products || products.length === 0) {
                   hintContainer.style.display = "none";
                   return;
               }

                products.forEach(item => {
                    const hintItem = document.createElement("div");
                    hintItem.className = "hint-item";
                    // Sử dụng dataset để lưu product_id
                    hintItem.dataset.productId = item.product_id;
                    hintItem.innerHTML = `
                        <img src="${item.image}" alt="${item.product_name}" style="width:30px; height:30px; margin-right:10px;">
            ${item.product_name}
                  `;

               // Dùng mousedown để tránh mất focus trước khi xử lý
                 hintItem.addEventListener("mousedown", function (event) {
                        event.preventDefault(); // Ngăn trình duyệt hiểu là nhấp ra ngoài input
                         isSelectingHint = true;
                     });

                     hintItem.addEventListener("click", function () {
                         isSelectingHint = false; // Reset biến
                       console.log(item.product_id);
                        window.location.href = `index.php?page=product&id=${item.product_id}`;
                   });                    

                  hintContainer.appendChild(hintItem);
                 });

              hintContainer.style.display = "block";
           })
           .catch(error => console.error("Lỗi khi lấy gợi ý:", error));
    }

    let inputTimeout = null; // Biến lưu bộ đếm thời gian
    // Gán sự kiện cho từng ô tìm kiếm
  searchInputs.forEach(input => {
    input.addEventListener("input", function (event) {
        if (event.data === " ") return;

        clearTimeout(inputTimeout);
        const searchField = this;
        const isHeaderSearch = this.closest('.search-container');

        if (this.value.trim() === "") {
            // Nếu xóa sạch trên header, quay về home, giữ hash để focus vào ô mới
            if (isHeaderSearch) {
                window.location.href = '/webb/index.php#searchHeader';
                return;
            }

            // Nếu internal search, chỉ ẩn hint mà không điều hướng
            const container = this.closest('.products-search-container');
            if (container) {
                document.querySelectorAll('.hint-container').forEach(hint => {
                    hint.innerHTML = '';
                    hint.style.display = 'none';
                });
                return;
            }
        }

        inputTimeout = setTimeout(() => {
            showHints(searchField);
        }, 500);

        // Nếu cần load lại danh sách gốc, có thể thêm hàm loadAllProducts() ở đây.
    });

    input.addEventListener("keypress", function (event) {
        if (event.key === "Enter") {
            const isInternalSearch = this.closest('.products-search-container');
            const isHeaderSearch = this.closest('.search-container');

            if (isInternalSearch) {
                event.preventDefault();
                searchItems(this.value);
                return;
            }

            if (isHeaderSearch) {
                event.preventDefault();
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        }
    });

    input.addEventListener("blur", function () {
        setTimeout(() => {
            if (!isSelectingHint) {
                const container = this.closest(".search-container") || this.closest(".products-search-container");
                const hintContainer = container ? container.querySelector(".hint-container") : null;
                if (hintContainer) {
                    hintContainer.style.display = "none";
                }
            }
            isSelectingHint = false;
        }, 200);
    });
});

    // Xử lý click ngoài vùng input/hint-container
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".search-container") && !event.target.closest(".products-search-container") && !event.target.closest(".hint-item")) {
            document.querySelectorAll(".hint-container").forEach(hint => {
                hint.innerHTML = "";
                hint.style.display = "none";
            });
        }
    });

    // Gán sự kiện click cho nút tìm kiếm
    searchButtons.forEach(button => {
        const isInternalSearch = button.closest('.products-search-container');
        const isHeaderSearch = button.closest('.search-container');

        button.addEventListener("click", function (event) {
            if (isInternalSearch) {
                event.preventDefault();
                let searchInput = button.closest(".input-wrapper").querySelector(".search-input");
                if (searchInput) {
                    searchItems(searchInput.value);
                }
                return;
            }

            if (isHeaderSearch) {
                event.preventDefault();
                const form = button.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    });

    // Nếu có hash chuyển hướng, focus input header
    if (window.location.hash === '#searchHeader') {
        const headerInput = document.getElementById('header-search-input');
        if (headerInput) {
            headerInput.focus();
            headerInput.value = '';
        }
    }

});