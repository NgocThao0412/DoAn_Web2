<?php
include "../app/config/data_connect.php";

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo "<p style='color:red;'>Invalid order ID</p>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_id = intval($_POST['cancel_order_id']);

    // Chỉ cho hủy nếu đang PENDING
    $check_sql = "SELECT order_status FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $cancel_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && $result['order_status'] === 'PENDING') {

        $update_sql = "UPDATE orders SET order_status = 'COMPLETED' WHERE order_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $cancel_id);

        if ($stmt->execute()) {
            echo "<script>alert('Hủy đơn thành công');
                 window.location.href = '../receipt';
                 </script>";
            exit;
        } else {
            echo "<script>alert('Lỗi khi hủy đơn');</script>";
        }

    } else {
        echo "<script>alert('Không thể hủy đơn này');</script>";
    }
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
                <?= htmlspecialchars($payment_list[$order['payment_status']] ?? $order['payment_status']) ?>
            </p>
            <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['notes']) ?></p>
            <p><strong>Trạng thái:</strong>
                <span style="color: <?= $statusColor ?>;">
                    <?= htmlspecialchars($status_list[$order['order_status']] ?? $order['order_status']) ?>
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
    <?php if ($order['order_status'] === 'PENDING'): ?>
     <form method="POST" action="pages/receipt-detail.php?order_id=<?= $order_id ?>" 
      onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
      
     <input type="hidden" name="cancel_order_id" value="<?= $order_id ?>">
    
     <button type="submit" class="cancel-button">Hủy đơn hàng</button>
     </form>
    <?php endif; ?>
</div>