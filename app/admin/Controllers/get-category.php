<?php
include '../../config/data_connect.php';

$sql = "SELECT * FROM category ORDER BY category_id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo '<table style="width:100%; border-collapse: collapse; background: #fff; font-size: 14px;">';
    echo '<thead style="background: #f8f9fa; border-bottom: 2px solid #4e499e;">
            <tr>
                <th style="padding: 10px; text-align: left;">ID</th>
                <th style="padding: 10px; text-align: left;">Tên loại sản phẩm</th>
                <th style="padding: 10px; text-align: center;">Trạng thái</th>
                <th style="padding: 10px; text-align: center;">Hành động</th>
            </tr>
          </thead><tbody>';

    while($row = $result->fetch_assoc()) {
        $currStatus = $row['status'];
        $statusLabel = ($currStatus == 1) ? 
            '<span style="color: green;">✔ Hiện</span>' : 
            '<span style="color: red;">✖ Ẩn</span>';
        
        // Nút bấm đổi trạng thái
        $btnText = ($currStatus == 1) ? "Ẩn đi" : "Hiển thị";
        $btnClass = ($currStatus == 1) ? "btn-hide" : "btn-show";

        echo "<tr style='border-bottom: 1px solid #eee;'>
                <td style='padding: 8px;'>{$row['category_id']}</td>
                <td style='padding: 8px;'>{$row['name']}</td>
                <td style='padding: 8px; text-align: center;'>$statusLabel</td>
                <td style='padding: 8px; text-align: center;'>
                    <button class='update-status-btn $btnClass' 
                            data-id='{$row['category_id']}' 
                            data-status='$currStatus'
                            style='cursor:pointer; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd;'>
                        $btnText
                    </button>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo "Chưa có loại sản phẩm nào.";
}
?>