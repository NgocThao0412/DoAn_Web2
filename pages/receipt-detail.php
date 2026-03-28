<?php
include "../app/config/data_connect.php";

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo "<p style='color:red;'>Invalid order ID</p>";
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
$statusColor = match($order['order_status']) {
    'COMPLETED' => 'green',
    'CANCELLED' => 'red',
    'PROCESSING' => 'deepskyblue',
    'PENDING' => 'orange',
    default => 'black'
};
?>

<div class="more-infor">
    <span class="icon-close">
        <ion-icon name="close-outline"></ion-icon>
    </span>

    <div class="big-text more"><p>Đơn hàng #<?= $order_id ?></p></div>

    <div class="scroll-see">
        <div class="customer-infor">
            <p><strong>Tên:</strong> <?= htmlspecialchars($order['recipient_name']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['full_address']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['recipient_phone']) ?></p>
            <p><strong>Ngày đặt hàng:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
            <p><strong>Ngày giao hàng:</strong> <?= htmlspecialchars($order['delivery_date']) ?></p>
            <p><strong>Thời gian giao hàng:</strong> <?= htmlspecialchars($order['delivery_time']) ?></p>
            <p><strong>Trạng thái thanh toán:</strong> 
   <span style="color: <?= trim($order['payment_status']) == 'PAID' ? 'green' : 'red' ?>">
         <?= trim($order['payment_status']) == 'PAID' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
   </span>
</p> 
            <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['notes']) ?></p>
            <p><strong>Trạng thái:</strong>
   <span style="color: <?= $statusColor ?>;">
    <?= trim($order['order_status']) == 'PENDING' ? 'Chờ xử lý' : htmlspecialchars($order['order_status']) ?>
   </span>
</p>
        </div>

        <?php 
        $total = 0;
        while($row = $details->fetch_assoc()): 
            $subtotal = $row['price'] * $row['quantity'];
            $total += $subtotal;
        ?>
        <div class="img-infor">
            <img src="<?= htmlspecialchars($row['image'] ?? '') ?>" 
                 alt="<?= htmlspecialchars($row['name']) ?>" 
                 style="width:80px; height:80px; object-fit:cover;">

            <div class="name-type">
                <div class="name">
                    <p><?= htmlspecialchars($row['name']) ?></p>
                    <p>Số lượng: <?= $row['quantity'] ?></p>
                    <p>Giá: <?= number_format($row['price'], 0, ',', '.') ?> VND</p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="total-price">
        <p style="color: red;">
            <strong>Tổng cộng:</strong> <?= number_format($total, 0, ',', '.') ?> VND
        </p>
    </div>
</div>