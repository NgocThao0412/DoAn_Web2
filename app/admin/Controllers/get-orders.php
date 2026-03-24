<?php
include '../../config/data_connect.php';

$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM orders WHERE 1=1";
if ($from != '') $sql .= " AND created_at >= '$from 00:00:00'";
if ($to != '')   $sql .= " AND created_at <= '$to 23:59:59'";
if ($status != '') $sql .= " AND order_status = '$status'";

// YÊU CẦU: Sắp xếp theo phường (shipping_ward)
$sql .= " ORDER BY shipping_ward ASC, created_at DESC";
$result = $conn->query($sql);
?>

<div class="order-table-container">
    <div class="order-grid-header">
        <div>MÃ ĐƠN</div>
        <div>NGÀY ĐẶT</div>
        <div>KHU VỰC</div>
        <div>TỔNG TIỀN</div>
        <div>TRẠNG THÁI</div>
        <div>CHI TIẾT</div>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="order-row">
                <div>#<?= $row['order_id'] ?></div>
                <div><?= date('d/m/Y', strtotime($row['created_at'])) ?></div>
                <div style="font-size: 13px;">
                    <strong><?= htmlspecialchars($row['shipping_ward']) ?></strong> /<br>
                    <small><?= htmlspecialchars($row['shipping_district']) ?></small>
                </div>
                <div style="color:#d32f2f; font-weight:bold;"><?= number_format($row['total_amount']) ?>đ</div>
                <div>
                    <select class="order-status-select" onchange="updateStatus(<?= $row['order_id'] ?>, this.value)">
                        <option value="PENDING" <?= ($row['order_status'] == 'PENDING' ? 'selected' : '') ?>>Chờ xử lý</option>
                        <option value="PROCESSING" <?= ($row['order_status'] == 'PROCESSING' ? 'selected' : '') ?>>Đang xử lý</option>
                        <option value="COMPLETED" <?= ($row['order_status'] == 'COMPLETED' ? 'selected' : '') ?>>Hoàn thành</option>
                    </select>
                </div>
                <div>
                    <a href="order-detail?id=<?= $row['order_id'] ?>" class="view-btn" style="text-decoration: none; color: #4e499e; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 5px;">
                        <ion-icon name="eye-outline"></ion-icon> Xem chi tiết
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 20px;">Không tìm thấy đơn hàng nào.</div>
    <?php endif; ?>
</div>