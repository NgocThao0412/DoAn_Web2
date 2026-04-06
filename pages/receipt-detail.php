<?php
include "../app/config/data_connect.php";

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    echo "<p style='color:red;'>Invalid order ID!</p>";
    exit;
}
// ================== LẤY THÔNG TIN ORDER ==================
$sql_order = "SELECT o.order_id, 
                    DATE_FORMAT(o.order_date, '%Y-%m-%d %H:%i') AS order_date, 
                    o.delivery_date,
                    o.delivery_time,
                    o.total_amount, 
                    o.order_status, 
                    o.payment_status,
                    o.notes,
                    o.recipient_name,
                    o.recipient_phone,
                    CONCAT(o.shipping_street, ', ', o.shipping_ward, ', ', o.shipping_city) AS full_address
            FROM orders o
            WHERE o.order_id = ?";

$stmt = $conn->prepare($sql_order);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<p style='color:red;'>Order not found!</p>";
    exit;
}

// ================== LẤY CHI TIẾT ==================
$sql_details = "SELECT 
                    od.quantity, 
                    od.price, 
                    p.name, 
                    p.image
                FROM order_detail od
                LEFT JOIN products p ON od.product_id = p.product_id
                WHERE od.order_id = ?";

$stmt = $conn->prepare($sql_details);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$details = $stmt->get_result();

// ================== MÀU STATUS ==================
$status = strtoupper(trim($order['order_status']));

$statusColor = match($status) {
    'COMPLETED' => 'green',
    'PROCESSING' => 'deepskyblue',
    'PENDING' => 'orange',
    default => 'black'
};
$status_list = [
    'PENDING' => 'Chờ xử lý', 
    'PROCESSING' => 'Đang xử lý', 
    'COMPLETED' => 'Hoàn thành',
];

$payment_list = [
    'UNPAID' => 'Chưa thanh toán',
    'PAID' => 'Đã thanh toán',
    'FAILED' => 'Thất bại'
];

?>

<div class="more-infor">
    <span class="icon-close">
        <ion-icon name="close-outline"></ion-icon>
    </span>

    <div class="big-text more"><p>Order #<?= $order_id ?></p></div>

    <div class="scroll-see">
        <div class="customer-infor">
            <p><strong>Tên:</strong> <?= htmlspecialchars($order['recipient_name']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['full_address']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['recipient_phone']) ?></p>
            <p><strong>Ngày đặt hàng:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
            <p><strong>Ngày giao hàng:</strong> <?= htmlspecialchars($order['delivery_date']) ?></p>
            <p><strong>Thời gian giao hàng:</strong> <?= htmlspecialchars($order['delivery_time']) ?></p>
            <p><strong>Trạng thái thanh toán:</strong> 
                <?php
                    $os = $order['order_status'];
                    $amount = $order['total_amount'];

                    if ($os == 'COMPLETED' && $amount == 0) {
                        echo 'Chưa thanh toán';
                    } elseif ($os == 'COMPLETED') {
                        echo 'Đã thanh toán';
                    } else {
                        echo htmlspecialchars($payment_list[$order['payment_status']] ?? $order['payment_status']);
                    }
                ?>
            </p>
            <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['notes']) ?></p>
            <?php 
               $os = $order['order_status'];
                $amount = $order['total_amount'];

             if ($os == 'COMPLETED' && $amount == 0): ?>
            <p><strong>Trạng thái:</strong>
                 <span style="color:#d32f2f; font-weight:bold;">Đã hủy</span>
            </p>
            <?php else: ?>
            <p><strong>Trạng thái:</strong>
                <span style="color: <?= $statusColor ?>;">
                    <?= htmlspecialchars($status_list[$os] ?? $os) ?>
                 </span>
            </p>
            <?php endif; ?>
        </div>

        <div class="img-infor-container">

        <?php 
        $total = 0;
        while($row = $details->fetch_assoc()): 
            $subtotal = $row['price'] * $row['quantity'];
            $total += $subtotal;
        ?>
        <div class="img-infor">
            <img src="<?= htmlspecialchars($row['image'] ?? '') ?>" 
                 alt="<?= htmlspecialchars($row['name']) ?>">

            <div class="name-type">
                <div class="name">
                    <p class="pd_name"><?= htmlspecialchars($row['name']) ?></p>
                    <div class="pd_quantity">
                        <span>Số lượng: <?= $row['quantity'] ?></span>
                        <span>Giá: <?= number_format($row['price'], 0, ',', '.') ?> VNĐ</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

    </div> 

    <div class="total-price">
        <p style="color: red;">
            <strong>Tổng cộng:</strong> <?= number_format($total, 0, ',', '.') ?> VNĐ
        </p>
    </div>
</div>