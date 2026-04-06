<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../app/config/config.php';
include __DIR__ . '/../app/config/data_connect.php';

//ktra đăng nhập
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Lấy user từ session
$user_id = $_SESSION['user']['user_id'] ?? null;

$sql = "SELECT * FROM users WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User does not exist");
}

// ================= UPDATE =================
if (isset($_POST['update'])) {

    $errors = [];

    $email = trim($_POST['email'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $ward = trim($_POST['ward'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $password = $_POST['password'] ?? '';

    // fullname
    if (!$fullname) {
        $errors[] = "Vui lòng nhập họ tên";
    } elseif (!preg_match('/^[\p{L} ]+$/u', $fullname)) {
        $errors[] = "Họ tên không được chứa ký tự đặc biệt";
    }

    // email
    if (!$email) {
        $errors[] = "Vui lòng nhập email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $errors[] = "Email phải có dạng @gmail.com";
    }

    // phone
    if (!$phone) {
        $errors[] = "Vui lòng nhập số điện thoại";
    } elseif (!preg_match('/^(03|05|07|08|09)\d{8}$/', $phone)) {
        $errors[] = "Số điện thoại phải có 10 chữ số và bắt đầu bằng 03, 05, 07, 08 hoặc 09";
    }

    // street
    if (!$street) {
        $errors[] = "Vui lòng nhập địa chỉ";
    }

    // tỉnh / phường
    if (!$city) {
        $errors[] = "Vui lòng chọn tỉnh/thành phố";
    }
    if (!$ward) {
        $errors[] = "Vui lòng chọn phường/xã";
    }

    // password
    if ($password !== '') {
        if (strlen($password) < 8) {
            $errors[] = "Mật khẩu phải >= 8 ký tự";
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Mật khẩu phải có chữ hoa, chữ thường và số";
        }
    }

    if (!empty($errors)) {
        $message = implode(" \\n", $errors);
        echo "<script>alert('" . addslashes($message) . "'); window.location.href='profile';</script>";
        exit();
    }

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users 
        SET email=?, fullname=?, phone=?, street=?, ward=?, city=?, password=? 
        WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $email, $fullname, $phone, $street, $ward, $city, $password, $user_id);
    } else {
        $sql = "UPDATE users 
                SET email=?, fullname=?, phone=?, street=?, ward=?, city=? 
                WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $email, $fullname, $phone, $street, $ward, $city, $user_id);
    }

    if ($stmt->execute()) {

        // 🔄 Lấy lại dữ liệu mới từ DB
        $sql = "SELECT * FROM users WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $_SESSION['user'] = $result->fetch_assoc();

        echo "<script>alert('Cập nhật thành công'); window.location.href='profile';</script>";
        exit();
    } else {
        echo "Lỗi update: " . $conn->error;
    }
}


// Dữ liệu hiển thị
$username = htmlspecialchars($user['username'] ?? 'Guest');
$email = htmlspecialchars($user['email'] ?? '');
$fullname = htmlspecialchars($user['fullname'] ?? '');
$phone = htmlspecialchars($user['phone'] ?? '');
$street = htmlspecialchars($user['street'] ?? '');
$ward = htmlspecialchars($user['ward'] ?? '');
$city = htmlspecialchars($user['city'] ?? '');

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin tài khoản</title>
    <link rel="stylesheet" href="public/assets/css/profile.css">
</head>
<body>

<div class="profile-container">
    <h1>Thông tin tài khoản</h1>

    <!-- HIỂN THỊ -->
    <div class="profile-field">
        <label>Tên:</label>
        <span><?php echo $username; ?></span>
    </div>

    <div class="profile-field">
        <label>Họ và tên:</label>
        <span><?php echo $fullname; ?></span>
    </div>

    <div class="profile-field">
        <label>Email:</label>
        <span><?php echo $email; ?></span>
    </div>

    <div class="profile-field">
        <label>Số điện thoại:</label>
        <span><?php echo $phone; ?></span>
    </div>

    <div class="profile-field">
        <label>Đường:</label>
        <span><?php echo $street; ?></span>
    </div>

    <div class="profile-field">
        <label>Phường/Xã:</label>
        <span><?php echo $ward; ?></span>
    </div>

    <div class="profile-field">
        <label>Thành phố:</label>
        <span><?php echo $city; ?></span>
    </div>

    <!-- NÚT SỬA -->
    <button onclick="toggleEdit()">Sửa thông tin</button>

    <!-- FORM SỬA -->
    <form method="POST" id="editForm" style="display:none; margin-top:20px;">

        <div>
            <label>Họ và tên:</label>
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>">
        </div>

        <div>
            <label>Email:</label>
            <input type="text" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
        </div>

        <div>
            <label>Số điện thoại:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>

        <div>
            <label>Địa chỉ:</label>
            <input type="text" name="street" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>">
        </div>

        <div>

        <label>Thành phố:</label>
        <select id="editCity"></select>
        </div>

        <div>
        <label>Phường/Xã:</label>
       <select id="editWard"></select>
     </div>

       <!-- hidden gửi tên -->
    <input type="hidden" name="city" id="editCityName">
    <input type="hidden" name="ward" id="editWardName">

        <div>
            <label>Mật khẩu mới:</label>
            <input type="password" name="password">
        </div>

        <button type="submit" name="update">Lưu</button>
    </form>

</div>
<script>
const currentCity = "<?php echo addslashes($city); ?>";
const currentWard = "<?php echo addslashes($ward); ?>";
</script>
<script>
function toggleEdit() {
    var form = document.getElementById("editForm");
    form.style.display = (form.style.display === "none") ? "block" : "none";
}
</script>

</body>
</html>