<?php
session_name("user");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn)) {
    include_once __DIR__ . "/../app/config/data_connect.php";
}

if (!$conn) {
    echo '<div class="empty-cart">Database connection error.</div>';
    return;
}

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
    echo '<div class="empty-cart">Please login to view cart.</div>';
    return;
}

$user_id = $_SESSION['user']['user_id'];

$sql = "SELECT p.product_id, p.name, p.selling_price, p.image, cd.quantity
        FROM cart_detail cd
        JOIN cart c ON cd.cart_id = c.cart_id
        JOIN products p ON cd.product_id = p.product_id
        WHERE c.user_id = ? AND c.status = 'active'";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Database error: " . $conn->error;
    return;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

if (empty($products)) {
    echo '<div class="empty-cart">There are no products in the cart.</div>';
    return;
}
?>

<?php foreach ($products as $row): ?>
<?php
    $price = $row["selling_price"] ?? 0;  
    $quantity = $row["quantity"] ?? 1;
    $total_price += $price * $quantity;
?>
<div class="cart-item">

    <img src="<?= $row["image"] ?? '' ?>" 
         height="74" width="60" class="cart-img" alt="">

    <span class="infor">
        <button class="close-mini" onclick="removeFromCart(<?= $row["product_id"] ?>)">
            <ion-icon name="close-outline"></ion-icon>
        </button>

        <span class="head-text">
            <?= $row["name"] ?? 'Không có tên' ?>
        </span>

        <p class="bottom-text">SL

            <button class="click" onclick="updateQuantity(<?= $row["product_id"] ?>, -1)">
                <ion-icon name="caret-back-outline"></ion-icon>
            </button>

            <input type="number"
                class="quantity-input"
                id="quantity_<?= $row["product_id"] ?>"
                value="<?= $quantity ?>"
                min="1"
                onchange="updateQuantityDirectly(<?= $row["product_id"] ?>, this.value)">

            <button class="click" onclick="updateQuantity(<?= $row["product_id"] ?>, 1)">
                <ion-icon name="caret-forward-outline"></ion-icon>
            </button>

            <span class="price">
                <?= number_format($price, 0, ",", ".") ?> VND
            </span>

        </p>
    </span>

</div>
<?php endforeach; ?>
<div class="cart-footer">
    <p class="cart-title">Hóa đơn</p>
    <span class="total-amount"><?= number_format($total_price, 0, ",", ".") ?> VND</span>
</div>