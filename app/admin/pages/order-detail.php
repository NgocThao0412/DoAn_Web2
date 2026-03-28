<?php 
include '../Api_php/check-session-admin.php';
include '../../config/data_connect.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$order_query = $conn->query("SELECT * FROM orders WHERE order_id = $order_id");
$order = $order_query->fetch_assoc();

if (!$order) {
    echo "<script>alert('Đơn hàng không tồn tại!'); window.location.href='order-manager.php';</script>";
    exit;
}

$details = $conn->query("SELECT od.*, p.name, p.image 
                         FROM order_detail od 
                         JOIN products p ON od.product_id = p.product_id 
                         WHERE od.order_id = $order_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../public/assets/Img/golden_crumb.png" type="image/x-icon" />
    <title>Chi tiết đơn hàng #<?= $order_id ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/order-manager.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>
    <div class="grid-full">
        <div class="left-screen">
            <?php include ("../includes/header.php"); ?>
            
            <div class="Home">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding: 0 20px;">
                    <div class="text-big">Chi tiết đơn hàng #<?= $order_id ?></div>
                    <a href="order-manager" style="background:#4e499e; color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none; display:flex; align-items:center; gap:8px; font-weight:bold;">
                        <ion-icon name="arrow-back-outline"></ion-icon> Quay lại danh sách
                    </a>
                </div>

                <div class="order-table-container" style="background:#f9f9f9; padding:20px; border-radius:12px; margin-bottom:20px; display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div>
                        <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['recipient_name']) ?></p>
                        <p><strong>Điện thoại:</strong> <?= htmlspecialchars($order['recipient_phone']) ?></p>
                        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_street']) ?>, <?= htmlspecialchars($order['shipping_ward']) ?>, <?= htmlspecialchars($order['shipping_city']) ?></p>
                        <p><strong>Ghi chú:</strong> <span style="color: #666; font-style: italic;"><?= htmlspecialchars($order['notes']) ?: 'Không có ghi chú' ?></span></p>
                    </div>
                    <div style="text-align: right;">
                        <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                        <p><strong>Trạng thái:</strong> 
                            <?php 
                                $status_list = [
                                    'PENDING' => 'Chờ xử lý', 
                                    'PROCESSING' => 'Đang xử lý', 
                                    'COMPLETED' => 'Hoàn thành'
                                ];
                                $status_text = $status_list[$order['order_status']] ?? $order['order_status'];
                                $status_color = ($order['order_status'] == 'COMPLETED') ? 'green' : (($order['order_status'] == 'PROCESSING') ? 'orange' : 'red');
                            ?>
                            <span style="font-weight:bold; color:<?= $status_color ?>;"><?= $status_text ?></span>
                        </p>
                    </div>
                </div>

                <div class="order-table-container">
                    <div class="order-grid-header" style="grid-template-columns: 1fr 2.5fr 1fr 1fr 1fr 1.5fr; background: #EF99B4; color: #455265; padding: 12px; border-radius: 12px 12px 0 0; text-align: center; font-weight: bold;">
                        <div>ẢNH</div>
                        <div style="text-align: left; padding-left: 10px;">SẢN PHẨM</div>
                        <div>ĐƠN VỊ TÍNH</div> 
                        <div>GIÁ</div>
                        <div>SỐ LƯỢNG</div>
                        <div>THÀNH TIỀN</div>
                    </div>

                    <?php if ($details && $details->num_rows > 0): ?>
                        <?php while ($row = $details->fetch_assoc()): ?>
                            <div class="order-row" style="grid-template-columns: 1fr 2.5fr 1fr 1fr 1fr 1.5fr; display: grid; align-items: center; text-align: center; border-bottom: 1px solid #eee; padding: 10px 0;">
                                <div>
                                    <img src="../../<?= $row['image'] ?>" width="55" height="55" style="border-radius:10px; object-fit: cover; border: 1px solid #ddd;">
                                </div>
                                <div style="text-align:left; font-weight: bold; padding-left: 10px;">
                                    <?= htmlspecialchars($row['name']) ?>
                                </div>
                                <div style="color: #666; font-size: 0.9em;"><?= htmlspecialchars($row['unit']) ?></div> 
                                <div><?= number_format($row['price']) ?> VND</div>
                                <div style="font-weight: bold;"><?= $row['quantity'] ?></div>
                                <div style="font-weight:bold; color:#d32f2f; font-size: 1.1em;"><?= number_format($row['price'] * $row['quantity']) ?> VND</div>
                            </div>
                        <?php endwhile; ?> 
                    <?php else: ?>
                        <div style="text-align: center; padding: 30px; color: #666;">Không có dữ liệu sản phẩm.</div>
                    <?php endif; ?>
                    
                    <div style="text-align:right; padding:25px; font-size:1.6em; font-weight:bold;">
                        TỔNG CỘNG: <span style="color:#d32f2f;"><?= number_format($order['total_amount']) ?> VND</span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include ("../includes/nav.php"); ?>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
