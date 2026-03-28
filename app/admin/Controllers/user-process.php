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
            $toggleAction = ($row['status'] === 'locked') ? 'Mở khóa' : 'Khóa';
            $icon = ($row['status'] === 'locked') ? 'lock-closed-outline' : 'lock-open-outline';
            
            // Kiểm tra admin tự khóa mình
            $isSelf = ($username === $currentUser);
            
            // Chuẩn bị địa chỉ đầy đủ để hiển thị khi hover (dùng title attribute)
            $fullAddress = htmlspecialchars($row['street'] . ', ' . $row['ward'] . ', ' . $row['city']);
        ?>
            <tr data-username="<?php echo htmlspecialchars($username); ?>">
                <td><?php echo htmlspecialchars($username); ?></td>
                <td class="hide2"><?php echo htmlspecialchars($fullname); ?></td>
                <td class="hide3" title="<?php echo $row['email']; ?>">
                    <?php echo htmlspecialchars($row['email']); ?>
                </td>
                <td class="hide4" title="<?php echo $row['phone']; ?>">
                    <?php echo htmlspecialchars($row['phone']); ?>
                </td>
                <td class="hide5" title="<?php echo $fullAddress; ?>">
                    <?php echo $fullAddress; ?>
                </td>
                
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                
                <td class="action-buttons">
                    <button 
                        class="button lock <?php echo ($row['status'] === 'locked') ? 'is-locked' : ''; ?> <?php echo $isSelf ? 'disabled' : ''; ?>"
                        <?php echo $isSelf ? 'disabled' : 'onclick="toggleLockUser(\'' . $username . '\', \'' . $row['status'] . '\', \'' . $row['role'] . '\')"'; ?>
                        title="<?php echo $isSelf ? 'Bạn không thể tự khóa mình' : $toggleAction; ?>">
    
                        <ion-icon name="<?php echo $icon; ?>"></ion-icon>
                    </button>
                    <button class="button reset" 
                            onclick="confirmResetPassword('<?php echo $username; ?>')" 
                            title="Khởi tạo lại mật khẩu">
                        <ion-icon name="key-outline"></ion-icon>
                    </button>

                    <button class="button edit" 
                            onclick="editUser('<?php echo $username; ?>')" 
                            title="Chỉnh sửa thông tin">
                        <ion-icon name="create-outline"></ion-icon>
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="no-data">
        <p>Không tìm thấy người dùng nào trong hệ thống.</p>
    </div>
<?php endif; ?>