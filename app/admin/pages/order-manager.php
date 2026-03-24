<?php include '../Api_php/check-session-admin.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../public/assets/Img/golden_crumb.png" type="image/x-icon" class="icon-page" />
    <title>Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/order-manager.css">
    
    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="grid-full">
        <div class="left-screen">
            <?php include ("../includes/header.php"); ?>
            <div class="Home">
                <div class="text-big">Quản lý đơn đặt hàng</div>
                
                <div class="filter-box" style="display:flex; gap:15px; background:#fff; padding:20px; border-radius:12px; margin-bottom:20px; align-items:flex-end;">
                    <div style="flex:1">
                        <label>Từ ngày</label><br>
                        <input type="date" id="from_date" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <div style="flex:1">
                        <label>Đến ngày</label><br>
                        <input type="date" id="to_date" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                    </div>
                    <div style="flex:1">
                        <label>Trạng thái</label><br>
                        <select id="filter_status" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                            <option value="">Tất cả</option>
                            <option value="PENDING">Chờ xử lý</option>
                            <option value="PROCESSING">Đang xử lý</option>
                            <option value="COMPLETED">Hoàn thành</option>
                        </select>
                    </div>
                    <button id="btn-filter" style="background:#4e499e; color: white; padding:10px 25px; border:none; border-radius:6px; cursor:pointer;">
                        Lọc dữ liệu
                    </button>
                </div>

                <div id="order-list-content">
                    </div>
            </div>
        </div>
        <?php include ("../includes/nav.php"); ?>
    </div>
    <div id="orderDetailModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; width: 800px; max-height: 90vh; border-radius: 12px; padding: 25px; position: relative; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        
        <span onclick="closeOrderModal()" style="position: absolute; right: 20px; top: 15px; font-size: 30px; cursor: pointer; color: #666;">&times;</span>
        
        <h2 style="color: #4e499e; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; display: flex; align-items: center; gap: 10px;">
            <ion-icon name="cart-outline"></ion-icon> Chi tiết đơn hàng <span id="md-order-id"></span>
        </h2>

        <div id="modal-data-content">
            </div>
        
        <div style="text-align: right; margin-top: 20px;">
            <button onclick="closeOrderModal()" style="padding: 10px 20px; background: #eee; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Đóng</button>
        </div>
    </div>
</div>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/order-manager.js"></script>
</body>
</html>