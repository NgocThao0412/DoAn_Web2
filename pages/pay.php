<?php

include __DIR__ . "/../app/config/data_connect.php"; // Kết nối database với đường dẫn tuyệt đối

// header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Kiểm tra đăng nhập
if (
    !isset($_SESSION['user']) || 
    !isset($_SESSION['user']['username']) || 
    !isset($_SESSION['user']['role'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Please log in before performing this action."
    ]);
    exit;
}


$user_id = $_SESSION['user']['user_id'];
$role = $_SESSION['user']['role'];

$user_query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($user_query);

// Lấy thông tin cart (chỉnh sửa cho đúng cột size_id trong product)
$cart_query = mysqli_query($conn, "
    SELECT p.product_id, p.name, p.selling_price, p.image, cd.quantity
    FROM cart_detail cd
    JOIN cart c ON cd.cart_id = c.cart_id
    JOIN products p ON cd.product_id = p.product_id
    WHERE c.user_id = $user_id AND c.status = 'active'
");
if (!$cart_query) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}
$total_cost = 0;

?>

<div class="Pay_big">
    <div class="pay-infor">
        <div class="input-information">
            <h1>Thông tin khách hàng</h1>

            <div class="choose">   
                <div class="fill">
                    <input type="radio" name="auto-fill" id="autoFill" value="Auto fill" checked>
                    <label for="autoFill">Sử dụng thông tin đã lưu</label>
                </div>

                <div class="clear">
                    <input type="radio" id="sendOther" name="auto-fill"  value="Clear fill">
                    <label for="sendOther">Nhập địa chỉ mới</label>
                </div>
            </div>

            <form id="payment-form" >
                <div class="name">
                    <label for="full_name">Họ và tên <span style="color: red;">(*)</span></label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['username'] ?? '') ?>">

                </div>
                <div class="phone">
                    <label for="phone">Số điện thoại <span style="color: red;">(*)</span></label>
                    <input type="number" id="phone" name="phone" value="<?= $user['phone'] ?>">
                </div>

                <input type="hidden" name="shipping_city_name" id="shipping_city_name">
                <input type="hidden" name="shipping_ward_name" id="shipping_ward_name">

                
                <div class="address">
                    <label for="registerStreet">Địa chỉ <span style="color: red;">(*)</span></label>
                    <input type="text" id="registerStreet" name="shipping_street" value="<?= htmlspecialchars($user['street']) ?>">
                </div>

                <div class="address">
                    <label for="registerCity">Thành phố <span style="color: red;">(*)</span></label>
                    <select id="registerCity" name="shipping_city" required>
                        <option value="">Select City</option>
                    </select>
                    <!-- <label>City</label> -->
                </div>

                <!-- ẨN district -->
                <div class="address" style="display:none;">
                     <select id="registerDistrict" name="shipping_district"></select>
                </div>

                <div class="address">
                    <label for="registerWard">Phường/Xã <span style="color: red;">(*)</span></label>
                    <select id="registerWard" name="shipping_ward" required>
                        <option value="">Select Ward</option>
                    </select>
                    <!-- <label>Ward</label> -->
                </div>

                <div class="delivery-date">
                    <label for="delivery_date">Ngày giao hàng <span style="color: red;">(*)</span></label>
                    <input type="date" id="delivery_date" name="delivery_date" required>
                </div>


                <div class="delivery-time">
                    <label for="delivery_time">Thời gian giao hàng <span style="color: red;">(*)</span></label>
                    <!-- <select id="delivery_time" name="delivery_time" required>
                        <option value="">Select Time</option>
                        <option value="8:00 - 10:00">8:00 - 10:00</option>
                        <option value="10:00 - 12:00">10:00 - 12:00</option>
                        <option value="13:00 - 15:00">13:00 - 15:00</option>
                        <option value="15:00 - 17:00">15:00 - 17:00</option>
                        <option value="17:00 - 19:00">17:00 - 20:00</option>
                    </select> -->
                    <input type="time" id="delivery_time" name="delivery_time" min="08:00" max="20:00" required>
                </div>

                <div class="note">
                    <label for="note">Lời nhắn</label>
                    <textarea id="note" name="note" rows="2" cols="90" style="overflow:auto;"></textarea>
                </div>


                <h1>Phương thức thanh toán</h1>
                <div class="payment-method">
                    <label><input type="radio" name="payment_method" value="COD" checked> COD</label>
                    <label><input type="radio" name="payment_method" value="Momo"> Momo</label>
                    <label><input type="radio" name="payment_method" value="VNPay"> VNPay</label>
                </div>

                <div id="Momo-fields" class="credit-details active">
                    <p style="color: red; text-align: center;">Tính năng đang được phát triển</p>
                </div>

                <div id="VNPay-fields" class="credit-details">
                    <p style="color: red; text-align: center;">Tính năng đang được phát triển</p>
                </div>

                <div class="my-order">
                    <div class="Text-head">
                        <h1>Your Orders</h1>
                    </div>

                    <div class="product-list">
                        <?php while ($row = mysqli_fetch_assoc($cart_query)) { 
                         $price = $row['selling_price'] ?? 0;
                         $quantity = $row['quantity'] ?? 1;
                         $subtotal = $price * $quantity;
                         $total_cost += $subtotal;
                        ?>
                    <div class="product">
                    <div class="item">
                          <img width="55" height="69" 
                           src="<?= $row['image'] ?? '' ?>" 
                           alt="<?= $row['name'] ?? '' ?>">

                    <div class="details">
                    <div><?= $row['name'] ?? 'Không có tên' ?></div>
                    <div class="btn-quantity">
                         Số lượng: <?= $quantity ?>
                    </div>
                 </div>

            <div class="price">
                <?= number_format($subtotal, 0, ",", ".") ?> VND
            </div>
        </div>

        <div class="note">
            <label for="note_<?= $row['product_id'] ?>">
                Note for this product
            </label>

            <input type="text"
                name="product_note[<?= $row['product_id'] ?>]"
                id="note_<?= $row['product_id'] ?>"
                value="<?= htmlspecialchars($row['note'] ?? '') ?>"
                placeholder="Enter message for this product">
        </div>
    </div>
<?php } ?>
</div>


                    <div class="subtotal">
                        <div class="total">
                            <!-- <div class="provisional">
                                <div>Provisional</div>
                                <div class="price"><?= number_format($total_cost, 0, '.', '.') ?> VND</div>
                            </div> -->
                            <div class="total-sum">
                                <div>Total</div>
                                <div class="price"><?= number_format($total_cost, 0, '.', '.') ?> VND</div>
                            </div>
                        </div>
                    </div>

                    <div class="notification">
                        <p>Thông báo: Vui lòng xem lại đơn hàng trước khi tiến hành thanh toán.</p>
                    </div>

                    <div class="pay">
                        <button type="submit" class="pay-button">Pay</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="confirmation" id="confirmation">
    <div class="icon-wrapper">
        <ion-icon name="checkmark-circle-outline"></ion-icon>
    </div>
    <h1>THÀNH CÔNG</h1>
    <p class="order-id"><span style="font-weight: bold;">Mã đơn hàng của bạn </span><strong class="order-id-number" id="order-id-number">#...</strong></p>
    <div id="receive-address-display" class="receive-address-display"></div>
    <div id="order-items" class="order-items"></div>
    <p style="font-size: 14px;">Cảm ơn bạn đã lựa chọn dịch vụ của chúng tôi!</p>
    <p style="font-size: 14px;">Đơn hàng của bạn đang được giao với tình yêu.</p>
    <!-- <a href="./user-receipt.html" class="back-home">Click here to view the invoice</a> -->
    <a id="view-invoice-link" href="receipt" class="view-invoice-btn">Xem hóa đơn</a>
    <p>Wishing you the sweetest day!</p>
</div>

<div class="blur-overlay" id="confirmation-overlay"></div>

<script>
    const userAddressInfo = {
        full_name: "<?= htmlspecialchars($user['fullname'] ?? '') ?>",
        phone: "<?= htmlspecialchars($user['phone'] ?? '') ?>",
        city: "<?= htmlspecialchars($user['city'] ?? '') ?>",
        district: "<?= htmlspecialchars($user['district'] ?? '') ?>",  
        ward: "<?= htmlspecialchars($user['ward'] ?? '') ?>",
        street: "<?= htmlspecialchars($user['street'] ?? '') ?>"
    };
</script>
