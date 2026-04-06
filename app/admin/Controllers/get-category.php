<?php
include '../../config/data_connect.php';

$sql = "SELECT category_id, name FROM category ORDER BY category_id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo '<table style="width:100%; border-collapse: collapse; background: #fff; font-size: 14px;">';
    echo '  <thead style="background: #f8f9fa; border-bottom: 2px solid #4e499e;">
                <tr>
                    <th style="padding: 10px; text-align: left;">ID</th>
                    <th style="padding: 10px; text-align: left;">Tên loại sản phẩm</th>
                </tr>
            </thead>
            <tbody>';

    while ($row = $result->fetch_assoc()) {
        echo "  <tr style='border-bottom: 1px solid #eee;'>
                    <td style='padding: 12px;'>#{$row['category_id']}</td>
                    <td style='padding: 12px; font-weight: bold; color: #4e499e;'>{$row['name']}</td>
                </tr>";
    }
    echo '    </tbody>
          </table>';
} else {
    echo "<p style='text-align:center; padding:10px;'>Chưa có loại sản phẩm nào.</p>";
}
?>