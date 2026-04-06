<?php
include '../../config/data_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($order_id > 0) {
        
        $check_stmt = $conn->prepare("SELECT order_status, total_amount FROM orders WHERE order_id = ?");
        $check_stmt->bind_param("i", $order_id);
        $check_stmt->execute();
        $res = $check_stmt->get_result()->fetch_assoc();

       
        if ($res && $res['order_status'] == 'COMPLETED' && $res['total_amount'] == 0) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng này đã bị hủy và không thể thay đổi trạng thái nữa!']);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        // Định nghĩa thứ tự trạng thái (chỉ được tiến lên, không lùi)
        $status_order = ['PENDING' => 1, 'PROCESSING' => 2, 'COMPLETED' => 3];
        $current_status = $res['order_status'];
        $is_cancelled = ($current_status == 'COMPLETED' && $res['total_amount'] == 0);

        // Nếu đã hoàn thành (không phải hủy), khóa luôn
        if ($current_status == 'COMPLETED' && !$is_cancelled) {
            echo json_encode(['success' => false, 'message' => 'Đơn hàng đã hoàn thành và không thể thay đổi trạng thái nữa!']);
            exit;
        }

        // Kiểm tra logic một chiều
        if ($status !== 'CANCEL_ORDER') {
            if (!isset($status_order[$current_status]) || !isset($status_order[$status])) {
                echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ!']);
                exit;
            }
            if ($status_order[$status] <= $status_order[$current_status]) {
                echo json_encode(['success' => false, 'message' => 'Không thể chuyển trạng thái về mức thấp hơn hoặc giữ nguyên!']);
                exit;
            }
        }

        // Nếu đang PROCESSING, không cho phép về PENDING
        if ($current_status == 'PROCESSING' && $status == 'PENDING') {
            echo json_encode(['success' => false, 'message' => 'Không thể chuyển từ "Đang xử lý" về "Chờ xử lý"!']);
            exit;
        }

        if ($status === 'CANCEL_ORDER') {
           
            $conn->begin_transaction();
            try {
                $detail_stmt = $conn->prepare("SELECT product_id, quantity FROM order_detail WHERE order_id = ?");
                $detail_stmt->bind_param("i", $order_id);
                $detail_stmt->execute();
                $result = $detail_stmt->get_result();

                $stock_restore_stmt = $conn->prepare("UPDATE products SET current_stock = current_stock + ? WHERE product_id = ?");
                while ($row = $result->fetch_assoc()) {
                    $stock_restore_stmt->bind_param("ii", $row['quantity'], $row['product_id']);
                    if (!$stock_restore_stmt->execute()) {
                        throw new Exception('Lỗi phục hồi tồn kho: ' . $stock_restore_stmt->error);
                    }
                }
                $detail_stmt->close();
                $stock_restore_stmt->close();

                $sql = "UPDATE orders SET order_status = 'COMPLETED', total_amount = 0 WHERE order_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $order_id);
                if (!$stmt->execute()) {
                    throw new Exception('Lỗi cập nhật trạng thái đơn hàng: ' . $stmt->error);
                }
                $stmt->close();
                $conn->commit();

                echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công!']);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }

       
        $sql = "UPDATE orders SET 
                    order_status = ?, 
                    payment_status = CASE WHEN ? = 'COMPLETED' THEN 'PAID' ELSE payment_status END,
                    total_amount = (SELECT SUM(price * quantity) FROM order_detail WHERE order_id = ?) 
                WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $status, $status, $order_id, $order_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi thực thi: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ!']);
    }
}
$conn->close();
?>