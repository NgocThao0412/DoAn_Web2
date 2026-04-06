<?php
include '../../config/data_connect.php';

$from   = trim($_GET['from'] ?? '');
$to     = trim($_GET['to'] ?? '');
$status = trim($_GET['status'] ?? '');
$ward   = trim($_GET['ward'] ?? '');

if ($from != '' && $to != '') {
    if (strtotime($from) > strtotime($to)) {
        $temp = $from;
        $from = $to;
        $to = $temp;
    }
}

$sql = "SELECT * FROM orders WHERE 1=1";
if ($from != '') $sql .= " AND created_at >= '$from 00:00:00'";
if ($to != '')   $sql .= " AND created_at <= '$to 23:59:59'";

if ($status != '') {
    if ($status === 'CANCELED') {
        $sql .= " AND order_status = 'COMPLETED' AND total_amount = 0";
    } elseif ($status === 'COMPLETED') {
        $sql .= " AND order_status = 'COMPLETED' AND total_amount > 0";
    } else {
        $sql .= " AND order_status = '$status'";
    }
}

if ($ward != '') {
    $escapedWard = mysqli_real_escape_string($conn, $ward);
    $sql .= " AND shipping_ward COLLATE utf8_unicode_ci LIKE '%$escapedWard%' COLLATE utf8_unicode_ci";
}

$sql .= " ORDER BY created_at DESC";
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
                    <strong><?= htmlspecialchars($row['shipping_ward']) ?></strong><br>
                </div>
                <div style="color:#d32f2f; font-weight:bold;"><?= number_format($row['total_amount']) ?> VND</div>
                
                <div>
                    <?php 
                        $isActuallyCancelled = ($row['order_status'] == 'COMPLETED' && $row['total_amount'] == 0);
                        $isCompleted = ($row['order_status'] == 'COMPLETED' && $row['total_amount'] > 0);
                    ?>
                    <select class="order-status-select" 
        onchange="updateStatus(<?= $row['order_id'] ?>, this.value, '<?= $row['order_status'] ?>')"
        <?= ($isActuallyCancelled || $isCompleted) ? 'disabled' : '' ?>
        style="<?= $isActuallyCancelled ? 'color: rgba(255, 0, 0, 0.6) !important; font-weight: bold; -webkit-text-fill-color: rgba(255, 0, 0, 0.6); cursor: not-allowed;' : ($isCompleted ? 'color: rgba(0, 128, 0, 0.6) !important; font-weight: bold; -webkit-text-fill-color: rgba(0, 128, 0, 0.6); cursor: not-allowed;' : '') ?>">
    
    <option value="PENDING" <?= ($row['order_status'] == 'PENDING' ? 'selected' : '') ?>>Chờ xử lý</option>
    <option value="PROCESSING" <?= ($row['order_status'] == 'PROCESSING' ? 'selected' : '') ?>>Đang xử lý</option>
    <option value="COMPLETED" <?= ($row['order_status'] == 'COMPLETED' && $row['total_amount'] > 0 ? 'selected' : '') ?>>Hoàn thành</option>
    
    <option value="CANCEL_ORDER" <?= $isActuallyCancelled ? 'selected' : '' ?> style="color: red; font-weight: bold;">
        Đã hủy
    </option>
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