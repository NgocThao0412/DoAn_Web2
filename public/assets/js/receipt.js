document.addEventListener("DOMContentLoaded", function () {
    const viewMoreBtns = document.querySelectorAll(".choose");
    const moreInforContent = document.querySelector('.more-infor-content');
    const blurOverlay = document.querySelector('.blur-overlay');

    viewMoreBtns.forEach(item => {
        item.addEventListener('click', function () {

            const orderId = this.getAttribute('data-order-id');
            console.log("CLICK ORDER:", orderId);

            fetch(`pages/receipt-detail.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(data => {

                    console.log("SERVER RETURN:", data); 
                    moreInforContent.innerHTML = '';
                    moreInforContent.insertAdjacentHTML('beforeend', data);

                    const moreInfor = moreInforContent.querySelector('.more-infor');

                    if (!moreInfor) {
                        console.error("Không tìm thấy .more-infor → PHP lỗi hoặc không trả HTML đúng");
                        return;
                    }

                    blurOverlay.classList.add('active');

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            moreInfor.classList.add('active');
                        });
                    });

                    const iconClose = moreInfor.querySelector('.icon-close');

                    if (iconClose) {
                        iconClose.addEventListener('click', function () {
                            moreInfor.classList.remove('active');

                            moreInfor.addEventListener('transitionend', () => {
                                blurOverlay.classList.remove('active');
                                moreInforContent.innerHTML = "";
                            }, { once: true });
                        });
                    }

                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
        });
    });
});