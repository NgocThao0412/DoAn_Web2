<?php
include '../../config/data_connect.php';

if (isset($_GET['product_id'])) {

    $product_id = $_GET['product_id'];

    // Lấy trạng thái hiện tại
    $stmt = $conn->prepare("SELECT status FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    // Nếu đang HIDDEN -> hiện lại
    if (strcasecmp($status, 'HIDDEN') === 0) {

        $update = $conn->prepare("UPDATE products SET status = 'AVAILABLE' WHERE product_id = ?");
        $update->bind_param("i", $product_id);

        if ($update->execute()) {
            echo "<script>
                alert('Sản phẩm đã được hiển thị.');
                window.history.back();
            </script>";
        }

    } else {

        // Ẩn sản phẩm
        $update = $conn->prepare("UPDATE products SET status = 'HIDDEN' WHERE product_id = ?");
        $update->bind_param("i", $product_id);

        if ($update->execute()) {
            echo "<script>
                alert('Sản phẩm đã được ẩn.');
                window.history.back();
            </script>";
        }
    }

    $update->close();
}
?>