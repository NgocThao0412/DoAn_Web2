<?php
include "app/config/data_connect.php";

$loggedIn = isset($_SESSION['user']) && isset($_SESSION['user']['username']);

if ($loggedIn) {
    $username = $_SESSION['user']['username'];

    $sql = "SELECT 
                o.order_id, 
                DATE_FORMAT(o.order_date, '%Y-%m-%d %H:%i') AS order_date,
                o.total_amount, 
                o.order_status, 
                (SELECT SUM(od.quantity) 
                 FROM order_detail od 
                 WHERE od.order_id = o.order_id) AS quantity 
            FROM orders o 
            WHERE o.username = ? 
            ORDER BY o.order_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); // ✅ FIX QUAN TRỌNG
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<div class="receipt">
    <div class="big-text">
        <h1>Hóa đơn của bạn</h1>
    </div>

    <div class="text-infor">
        <div class="text-top"><p>Mã#</p></div>
        <div class="text-top"><p>Ngày</p></div>
        <div class="text-top"><p>Số lượng</p></div>
        <div class="text-top"><p>Tổng cộng</p></div>
        <div class="text-top"><p>Trạng thái</p></div>
        <div class="text-top"><p>Hành động</p></div>
    </div>

    <?php if ($loggedIn): ?>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="custumer">
                    <div class="text"><p><?= htmlspecialchars($row['order_id']) ?></p></div>
                    <div class="text"><p><?= htmlspecialchars($row['order_date']) ?></p></div>
                    <div class="text"><p><?= htmlspecialchars($row['quantity']) ?></p></div>
                    <div class="text"><p><?= number_format($row['total_amount'], 0, ',', '.') ?> VND</p></div>
                    <div class="text"><p><?= htmlspecialchars($row['order_status']) ?></p></div>
                    <div class="text">
                        <button class="choose" data-order-id="<?= $row['order_id'] ?>">
                            Xem thêm
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    <?php else: ?>
        <p style="text-align: center; color: red;  margin-bottom: 20px; margin-top: 30px;">
            Bạn chưa đăng nhập. Vui lòng đăng nhập để xem hóa đơn.
        </p>
    <?php endif; ?>
</div>

<!-- Thêm khung chứa chi tiết để load nội dung -->
<div class="more-infor-content"></div>
<div class="blur-overlay"></div>
