
<?php
include __DIR__ . '/../app/config/data_connect.php'; // Kết nối database

// Kiểm tra xem ID có được truyền lên không
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT * FROM products WHERE product_id = $id";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product does not exist!");
    }
} else {
    die("No product selected!");
}

// Truy vấn sản phẩm ngẫu nhiên
$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
$result = $conn->query($sql);
$product_rand = [];
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $product_rand[] = $row; 
    }
}else{
    die("Product does not exist");
}
?>

<?php
if (!empty($product)) {
    echo '
    <div class="Cake-infor">
        <div class="image-cake">
            <img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">
        </div>
        <div class="content">
            <h1>' . $product['name'] . '</h1>
            <div class="describe">
                <p>' . $product['description'] . '</p>
            </div>

            <div class="date-use"> Số lượng: <span class="date-span"> ' .$product['current_stock']. '</span> </div>
            <div class="buy-cake">

                <div class="size-cake">
                    <p class="title-size">Đơn vị:</p>
                    <p class="size">' . $product['unit'] . '</p>
                    
                </div>
                <div class="border"></div>
                <div class="size-descibe">
                    <p class="price">' . number_format($product['selling_price'], 0, ',', '.') . ' VNĐ</p>
                </div>
                <div class="border"></div>
                <div class="quantity-cake">
                    <p class="title-quantity">Số lượng: </p>
                    <div class="quantity-button">
                        <button class="minus-btn">
                            <p>-</p>
                        </button>
                      <input type="text" class="quantity" value="1">
                        <button class="plus-btn">
                            <p>+</p>
                        </button>
                    </div>
                </div>
            </div>
            <div class="add-shopping-cart">';
                
                // Kiểm tra trạng thái sản phẩm để hiển thị nút Add to cart hoặc nút vô hiệu
                if (isset($product['status']) && ($product['status'] === 'Out of Stock' || $product['status'] === 'Discontinued')) {
                    echo '<button class="sp-cart add-to-cart butn title" data-id="' . htmlspecialchars($product['product_id']) . '" disabled>';
                    echo '<p>' . htmlspecialchars($product['status']) . '</p>';
                    echo '</button>';
                } else {
                    echo '<button class="add-to-cart" data-id="' . htmlspecialchars($product['product_id']) . '">';
                    echo '<p class="add-to-cart-btn">Thêm vào giỏ</p>';
                    echo '</button>';
                }
                
    echo '  </div>
        </div>
    </div>
    ';
    
    echo '
    <div class="showing-product">
        <div class="div-title-product">
            <div class="border"></div>
            <p class="title-product">Sản phẩm liên quan</p>
        </div>
    ';
    
echo '<div class="list-product">';

foreach ($product_rand as $items) {
    echo '<div class="product-1">';

    echo '<a href="index.php?page=product&id=' . $items["product_id"] . '">
            <img width="300" height="300"
                 class="poster-img"
                 src="' . htmlspecialchars($items['image']) . '"
                 alt="' . htmlspecialchars($items['name']) . '">
          </a>';

    echo '<p class="name-product">' . htmlspecialchars($items['name']) . '</p>';

    // ✅ KIỂM TRA TRẠNG THÁI PHẢI NẰM Ở ĐÂY
    if (isset($items['status']) && ($items['status'] === 'Out of Stock' || $items['status'] === 'Discontinued')) {
        echo '<p class="price-product sp-cart disabled" style="cursor:not-allowed;">'
                . htmlspecialchars($items['status']) .
             '</p>';
    } else {
        echo '<p class="price-product sp-cart add-to-cart" data-id="' . htmlspecialchars($items['product_id']) . '">
                Price: ' . number_format($items['selling_price'], 0, ',', '.') . ' VNĐ
              </p>';
    }

    echo '</div>';
}

echo '</div>';
  
    }

//    echo '<div class="shopping-cart">
//         <button class="close">
//             &times;
//         </button>
//         <div class="cart-scroll">
//             '. include 'includes/cart.php'; '
//         </div>
        
//         <div class="Pay_button">
//             <a href="pay" class="pay-link">
//                 <button class="pay-btn-link">Pay</button>
//             </a>
//         </div>
//     </div>
//     <div class="blur-overlay"></div>';

?>
