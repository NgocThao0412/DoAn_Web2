<?php
include '../../config/data_connect.php';

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // 1. Kiểm tra sự tồn tại trong Chi tiết phiếu nhập 
    $check_import = $conn->query("SELECT COUNT(*) as total FROM import_receipt_detail WHERE product_id = $product_id");
    $has_import = ($check_import->fetch_assoc()['total'] > 0);

    // 2. Kiểm tra sự tồn tại trong Chi tiết đơn hàng
    $check_order = $conn->query("SELECT COUNT(*) as total FROM order_detail WHERE product_id = $product_id");
    $has_order = ($check_order->fetch_assoc()['total'] > 0);

    // --- LOGIC XỬ LÝ ---
    
    
    if (!$has_import && !$has_order) {
        $delete = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $delete->bind_param("i", $product_id);
        
        if ($delete->execute()) {
            echo "<script>alert('Sản phẩm mới, chưa có dữ liệu giao dịch nên đã được xóa vĩnh viễn.'); window.location.href = '../list-product';</script>";
        }
    } 
   
    else {
        $stmt = $conn->prepare("SELECT status FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();

        
        $newStatus = (strcasecmp($status, 'HIDDEN') === 0) ? 'AVAILABLE' : 'HIDDEN';
        
        $msg = ($newStatus === 'AVAILABLE') 
            ? 'Sản phẩm đã được hiển thị lại trên website.' 
            : 'Sản phẩm đã có lịch sử nhập/xuất nên hệ thống đã chuyển sang trạng thái ẨN để bảo toàn dữ liệu.';

        $update = $conn->prepare("UPDATE products SET status = ? WHERE product_id = ?");
        $update->bind_param("si", $newStatus, $product_id);

        if ($update->execute()) {
            echo "<script>alert('$msg'); window.location.href = '../list-product';</script>";
        }
    }
}
?>