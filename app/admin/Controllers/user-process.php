<?php
session_name("admin");
session_start();

include '../../config/data_connect.php';

// Lấy username của admin hiện tại đang đăng nhập
$currentUser = $_SESSION['admin']['username'] ?? '';

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0): ?>
    <table id="userTable">
        <thead>
            <tr>
                <th>Tên đăng nhập</th>
                <th class="hide2">Họ và tên</th>
                <th class="hide3">Email</th>
                <th class="hide4">Số điện thoại</th>
                <th class="hide5">Địa chỉ</th>
                <th>Vai trò</th>
                <th>Chức năng</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $result->fetch_assoc()):

            $username = $row['username'];
            $fullname = $row['fullname'];
            // Trạng thái hiển thị
            $statusText = ($row['status'] === 'locked') ? 'Đã khóa' : 'Hoạt động';
            // Hành động nút
            $toggleAction = ($row['status'] === 'locked') ? 'Mở khóa' : 'Khóa';
            // Icon
            $icon = ($row['status'] === 'locked') ? 'lock-closed-outline' : 'lock-open-outline';
            // Kiểm tra admin tự khóa mình
            $isSelf = ($username === $currentUser);
            $iconColor = ($row['status'] === 'locked') ? 'red' : 'black';

        ?>
            <tr data-username="<?php echo htmlspecialchars($username); ?>">
                <td><?php echo htmlspecialchars($username); ?></td>
                <td class="hide2">
                    <?php echo htmlspecialchars($fullname); ?>
                </td>
                <td class="hide3">
                    <?php echo htmlspecialchars($row['email']); ?>
                </td>
                <td class="hide4">
                    <?php echo htmlspecialchars($row['phone']); ?>
                </td>
                <td class="hide5" 
    style="cursor: pointer; color: #007bff;" 
    onclick="alert('Địa chỉ đầy đủ: <?php echo htmlspecialchars($row['street'] . ', ' . $row['ward'] . ', ' . $row['city']); ?>')">
    <?php 
    $fullAddress = $row['street'] . ', ' . $row['ward'] . ', ' . $row['city'];
    echo htmlspecialchars(mb_substr($fullAddress, 0, 25)) . '... (Xem thêm)';
    ?>
</td>
                
                <td>
                    <?php echo htmlspecialchars($row['role']); ?>
                </td>
                <td>
                    <!-- Nút khóa / mở khóa -->
                    <button 
                        class="button lock <?php echo $isSelf ? 'disabled' : ''; ?>"
                        <?php echo $isSelf ? 'disabled' : 
                        'onclick="toggleLockUser(\'' . $username . '\', \'' . $row['status'] . '\', \'' . $row['role'] . '\')"'; ?>

                        title="<?php 
                        echo $isSelf 
                        ? 'Bạn không thể tự khóa tài khoản của mình' 
                        : $toggleAction . ' người dùng này'; 
                        ?>">
                        
                        <ion-icon 
                        name="<?php echo $icon; ?>" 
                        style="color:<?php echo $iconColor; ?>;">
                        </ion-icon>

                    </button>

                    <!-- Nút chỉnh sửa -->
                    <button class="button edit" onclick="editUser('<?php echo $row['username']; ?>')">
                            <ion-icon name="create-outline" style="color: black;"></ion-icon>
                        </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Không tìm thấy người dùng nào.</p>
<?php endif; ?>