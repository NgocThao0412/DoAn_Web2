<?php include '../Api_php/check-session-admin.php'; ?>

<?php 
// Trang hiển thị thông tin admin (profile/profit)
include '../../config/data_connect.php';

$username = $_SESSION['admin']['username'] ?? '';
$fullname = '';
$email    = '';
$role     = '';

if ($username) {
    $stmt = $conn->prepare("
        SELECT fullname, email, phone, city, ward, street, role, status
        FROM users 
        WHERE username = ?
    ");
    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $fullname = $user['fullname'];
            $email    = $user['email'];
            $phone    = $user['phone'];
            $city     = $user['city'];
            $ward     = $user['ward'];
            $street   = $user['street'];
            $role     = $user['role'];
            $status   = $user['status'];
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="icon" href="../../public/assets/Img/golden_crumb.png" type="image/x-icon" class="icon-page" />

    <title>Thông tin Admin</title>
</head>

<body>
    <div class="notificate" id="notificate">
        <p id="message"></p>
    </div>

    <div class="grid-full">
        <div class="left-screen">
            <?php include ("../includes/header.php"); ?>

            <div class="profile-container">
                <h2>Thông tin tài khoản</h2>

                <div class="profile-item">
                    <strong>Tên đăng nhập:</strong>
                    <span id="adminName"><?php echo htmlspecialchars($username); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Họ và tên:</strong>
                    <span id="adminFullname"><?php echo htmlspecialchars($fullname); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Email:</strong>
                    <span id="adminEmail"><?php echo htmlspecialchars($email); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Số điện thoại:</strong>
                    <span id="adminPhone"><?php echo htmlspecialchars($phone); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Tỉnh/Thành phố:</strong>
                    <span id="adminCity"><?php echo htmlspecialchars($city); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Phường:</strong>
                    <span id="adminWard"><?php echo htmlspecialchars($ward); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Đường:</strong>
                    <span id="adminStreet"><?php echo htmlspecialchars($street); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Vai trò:</strong>
                    <span id="adminRole" style="color: blue;"><?php echo htmlspecialchars($role); ?></span>
                </div>

                <div class="profile-item">
                    <strong>Trạng thái:</strong>
                    <span id="adminStatus" style="color: green;"><?php echo htmlspecialchars($status); ?></span>
                </div>
            </div>
        </div>

        <?php include ("../includes/nav.php"); ?>
    </div>

    <script>
        // khi trang như profile admin, có thể gọi loadAdminInfo để cập nhật phần tử nếu cần
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof loadAdminInfo === 'function') {
                loadAdminInfo();
            }
        });
    </script>

    <script src="assets/js/script.js"></script>
</body>
</html>