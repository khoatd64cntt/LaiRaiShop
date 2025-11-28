<?php
session_start();
// Kết nối Database (Lùi 2 cấp thư mục)
require_once '../../db/db.php';

// 1. LẤY ID TỪ URL
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 11;

// 2. TRUY VẤN SẢN PHẨM & SHOP
$sql_product = "SELECT p.*, s.shop_name, s.aid as shop_owner_id
                FROM products p
                LEFT JOIN shops s ON p.sid = s.sid
                WHERE p.pid = $pid";
$result_product = $conn->query($sql_product);

if ($result_product->num_rows == 0) {
    die("Sản phẩm không tồn tại hoặc đã bị xóa.");
}
$product = $result_product->fetch_assoc();

// 3. TRUY VẤN ẢNH
$image_list = [];
if (!empty($product['main_image'])) {
    $image_list[] = $product['main_image'];
}
$sql_images = "SELECT img_url FROM product_images WHERE pid = $pid";
$result_images = $conn->query($sql_images);
while($row = $result_images->fetch_assoc()) {
    $image_list[] = $row['img_url'];
}
if (empty($image_list)) {
    $image_list[] = 'placeholder.png'; 
}

// 4. TRUY VẤN ĐÁNH GIÁ
$sql_reviews = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE pid = $pid";
$result_reviews = $conn->query($sql_reviews);
$review_data = $result_reviews->fetch_assoc();
$avg_rating = round($review_data['avg_rating'], 1); 
$total_reviews = $review_data['total_reviews'];

// 5. [MỚI] TÍNH TỔNG SỐ LƯỢNG ĐÃ BÁN (Từ bảng order_items)
$sql_sold = "SELECT SUM(quantity) as total_sold FROM order_items WHERE pid = $pid";
$result_sold = $conn->query($sql_sold);
$sold_data = $result_sold->fetch_assoc();
$total_sold = $sold_data['total_sold'] ? $sold_data['total_sold'] : 0;

// HÀM HỖ TRỢ
function formatMoney($number) {
    return number_format($number, 0, ',', '.') . '₫';
}

function getImgUrl($path) {
    if (strpos($path, 'http') === 0) return $path;
    // Xử lý đường dẫn ảnh local: lùi 2 cấp về root rồi vào images
    $clean_path = ltrim($path, '/');
    return "../../" . $clean_path;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="style/homepage.css?v=4">
    <link rel="stylesheet" href="style/detail.css?v=1"> 
    
    <link rel="icon" href="../../images/icon.png" />
</head>

<body>

    <div class="sticky-header-wrapper">
        <div class="top-bar">
            <div class="container top-bar-content">
                <div class="top-bar-left">
                    <a href="../SellerPage/dashboard.php">Kênh Người Bán</a>
                    <a href="#">Trở thành Người bán</a>
                    <div class="top-bar-connect">
                        <p>Kết nối</p>
                        <a href="https://www.facebook.com/ShopeeVN" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://www.instagram.com/Shopee_VN" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <div class="top-bar-right">
                    <a href="SignupPage/signup.php" class="auth-link">Đăng Ký</a>
                    <a href="LoginPage/login.php" class="auth-link">Đăng Nhập</a>
                </div>
            </div>
        </div>
        <header class="lairai-header">
            <div class="container header-content">
                <div class="logo"><a href="#"><img src="../../images/logo.png" alt="LaiRaiShop Logo"></a></div>

                <div class="search-box">
                    <form action="search.php" method="GET">
                        <input type="text" name="keyword" placeholder="Bao ship 0Đ - Đăng ký ngay để nhận ưu đãi hấp dẫn!">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    <div class="search-keywords">
                        <?php
                        $keywords = ["Sục Crocs", "Áo Khoác Nam", "iPhone 15 Pro Max", "Dép Lào", "Quần Bò Ống Rộng", "Túi Xách Nữ", "MacBook Air M3", "Nồi Cơm Điện", "Cây Cảnh"];
                        foreach ($keywords as $kw) {
                            echo '<a href="search.php?keyword=' . urlencode($kw) . '">' . $kw . '</a>';
                        }
                        ?>
                    </div>
                </div>

                <div class="cart-icon has-dropdown">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="lairai-dropdown-menu cart-dropdown">
                        <div class="cart-empty-icon-wrapper"><i class="fas fa-shopping-bag"></i></div>
                        <p>Chưa Có Sản Phẩm</p>
                    </div>
                </div>
            </div>
        </header>
    </div>

    <div class="container">
        
        <div class="product-wrapper">
            
            <div class="product-gallery">
                <div class="main-image">
                    <img id="current-img" src="<?= getImgUrl($image_list[0]) ?>" alt="Ảnh sản phẩm">
                </div>
                
                <?php if (count($image_list) > 1): ?>
                <div class="thumbnail-list">
                    <?php foreach($image_list as $index => $img): ?>
                        <div class="thumb-item <?= $index === 0 ? 'active' : '' ?>" onclick="changeImage(this, '<?= getImgUrl($img) ?>')">
                            <img src="<?= getImgUrl($img) ?>" alt="thumb">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="shop-info">
                    <div class="shop-avatar">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <div class="shop-name"><?= htmlspecialchars($product['shop_name']) ?></div>
                        <div class="shop-status">Online vài phút trước</div>
                    </div>
                    <a href="#" class="shop-btn"><i class="fas fa-store"></i> Xem Shop</a>
                </div>
            </div>

            <div class="product-info">
                <h1 class="product-title">
                    <span class="badge-mall">Mall</span>
                    <?= htmlspecialchars($product['name']) ?>
                </h1>

                <div class="meta-data">
                    <div class="rating">
                        <span class="score"><?= $avg_rating ?: '5.0' ?></span>
                        <div class="stars">
                            <?php 
                                $stars = $avg_rating ?: 5; 
                                for($i=1; $i<=5; $i++) echo ($i <= $stars) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            ?>
                        </div>
                    </div>
                    <div class="separator"></div>
                    
                    <div class="reviews">
                        <span class="num"><?= $total_reviews ?></span> Đánh Giá
                    </div>
                    <div class="separator"></div>
                    
                    <div class="sold">
                        <span class="num"><?= $total_sold ?></span> Đã Bán
                    </div>
                </div>

                <div class="price-section">
                    <span class="current-price"><?= formatMoney($product['price']) ?></span>
                </div>

                <div class="quantity-group">
                    <span class="label">Số Lượng</span>
                    <div class="quantity-control">
                        <button type="button" onclick="updateQty(-1)">-</button>
                        <input type="text" id="qty" value="1" readonly>
                        <button type="button" onclick="updateQty(1)">+</button>
                    </div>
                    <span class="stock-info"><?= $product['stock'] ?> sản phẩm có sẵn</span>
                </div>

                <div class="action-buttons">
                    <button class="btn-product btn-add-cart">
                        <i class="fas fa-cart-plus"></i> Thêm Vào Giỏ Hàng
                    </button>
                    <button class="btn-product btn-buy-now">Mua Ngay</button>
                </div>
            </div>
        </div>

        <div class="product-description-container">
            <div class="desc-header">MÔ TẢ SẢN PHẨM</div>
            <div class="desc-content">
                <?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : "Chưa có mô tả cho sản phẩm này." ?>
            </div>
        </div>

    </div>

    <footer class="lairai-footer">
        <div class="container">
            <div class="footer-content">

                <div class="footer-column">
                    <h3>CHĂM SÓC KHÁCH HÀNG</h3>
                    <ul>
                        <li><a href="#">Trung Tâm Trợ Giúp</a></li>
                        <li><a href="#">LaiRai Blog</a></li>
                        <li><a href="#">LaiRai Mall</a></li>
                        <li><a href="#">Hướng Dẫn Mua Hàng/Đặt Hàng</a></li>
                        <li><a href="#">Hướng Dẫn Bán Hàng</a></li>
                        <li><a href="#">Ví Điện Tử</a></li>
                        <li><a href="#">Đơn Hàng</a></li>
                        <li><a href="#">Trả Hàng/Hoàn Tiền</a></li>
                        <li><a href="#">Liên Hệ LaiRaiShop</a></li>
                        <li><a href="#">Chính Sách Bảo Hành</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>LAIRAISHOP VIỆT NAM</h3>
                    <ul>
                        <li><a href="#">Về LaiRaiShop</a></li>
                        <li><a href="#">Tuyển Dụng</a></li>
                        <li><a href="#">Điều Khoản LaiRaiShop</a></li>
                        <li><a href="#">Chính Sách Bảo Mật</a></li>
                        <li><a href="#">Kênh Người Bán</a></li>
                        <li><a href="#">Flash Sale</a></li>
                        <li><a href="#">Tiếp Thị Liên Kết</a></li>
                        <li><a href="#">Liên Hệ Truyền Thông</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>THANH TOÁN</h3>
                    <div class="payment-icons">
                        <img src="https://down-vn.img.susercontent.com/file/d4bbea4570b93bfd5fc652ca82a262a8" alt="Visa">
                        <img src="https://down-vn.img.susercontent.com/file/a0a9062ebe19b45c1ae0506f16af5c16" alt="MasterCard">
                        <img src="https://down-vn.img.susercontent.com/file/38fd98e55806c3b2e4535c4e4a6c4c08" alt="JCB">
                        <img src="https://down-vn.img.susercontent.com/file/bc2a874caeee705449c164be385b796c" alt="American Express">
                        <img src="https://down-vn.img.susercontent.com/file/2c46b83d84111ddc32cfd3b5995d9281" alt="COD">
                        <img src="https://down-vn.img.susercontent.com/file/5e3f0bee86058637ff23cfdf2e14ca09" alt="Tra gop">
                        <img src="https://down-vn.img.susercontent.com/file/9263fa8c83628f5deff55e2a90758b06" alt="ShopeePay">
                        <img src="https://down-vn.img.susercontent.com/file/0217f1d345587aa0a300e69e2195c492" alt="ShopeePay Later">
                    </div>
                    <h3 style="margin-top: 30px;">ĐƠN VỊ VẬN CHUYỂN</h3>
                    <div class="shipping-icons">
                        <img src="https://down-vn.img.susercontent.com/file/vn-11134258-7ras8-m20rc1wk8926cf" alt="SPX">
                        <img src="https://down-vn.img.susercontent.com/file/vn-50009109-64f0b242486a67a3d29fd4bcf024a8c6" alt="Giao Hàng Nhanh">
                        <img src="https://down-vn.img.susercontent.com/file/59270fb2f3fbb7cbc92fca3877edde3f" alt="Viettel Post">
                        <img src="https://down-vn.img.susercontent.com/file/957f4eec32b963115f952835c779cd2c" alt="Vietnam Post">
                        <img src="https://down-vn.img.susercontent.com/file/0d349e22ca8d4337d11c9b134cf9fe63" alt="J&T Express">
                        <img src="https://down-vn.img.susercontent.com/file/3900aefbf52b1c180ba66e5ec91190e5" alt="Grab Express">
                        <img src="https://down-vn.img.susercontent.com/file/6e3be504f08f88a15a28a9a447d94d3d" alt="Ninja Van">
                        <img src="https://down-vn.img.susercontent.com/file/0b3014da32de48c03340a4e4154328f6" alt="Be">
                        <img src="https://down-vn.img.susercontent.com/file/vn-50009109-ec3ae587db6309b791b78eb8af6793fd" alt="Ahamove">
                    </div>
                </div>

                <div class="footer-column">
                    <h3>THEO DÕI CHÚNG TÔI TRÊN</h3>
                    <ul class="social-links">
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>TẢI ỨNG DỤNG LAIRAI</h3>
                    <div class="download-app">
                        <div class="qr-code">
                            <img src="https://down-vn.img.susercontent.com/file/a5e589e8e118e937dc660f224b9a1472" alt="QR Code">
                        </div>
                        <div class="app-stores">
                            <a href="#"><img src="https://down-vn.img.susercontent.com/file/ad01628e90ddf248076685f73497c163" alt="App Store"></a>
                            <a href="#"><img src="https://down-vn.img.susercontent.com/file/ae7dced05f7243d0f3171f786e123def" alt="Google Play"></a>
                            <a href="#"><img src="https://down-vn.img.susercontent.com/file/35352374f39bdd03b25e7b83542b2cb0" alt="App Gallery"></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="copyright">
                    © 2025 LaiRaiShop. Tất cả các quyền được bảo lưu.
                </div>
                <div class="country-list">
                    Quốc gia & Khu vực:
                    <a href="#">Việt Nam</a>
                    | <a href="#">Lào</a>
                    | <a href="#">Singapore</a>
                    | <a href="#">Thái Lan</a>
                    | <a href="#">Philippines</a>
                    | <a href="#">Đông Timor</a>
                    | <a href="#">Indonesia</a>z
                    | <a href="#">Malaysia</a>
                    | <a href="#">Brunei</a>
                    | <a href="#">Đài Loan</a>
                </div>
            </div>
        </div>

        <div class="footer-policy">
            <div class="container">
                <div class="policy-row">
                    <a href="#">CHÍNH SÁCH BẢO MẬT</a>
                    <a href="#">QUY CHẾ HOẠT ĐỘNG</a>
                    <a href="#">CHÍNH SÁCH VẬN CHUYỂN</a>
                    <a href="#">CHÍNH SÁCH TRẢ HÀNG VÀ HOÀN TIỀN</a>
                </div>
                <div class="company-info">
                    <p>Địa chỉ: 2 Đ. Nguyễn Đình Chiểu, Phường Vĩnh Thọ, Thành phố Nha Trang, Tỉnh Khánh Hòa, Việt Nam</p>
                    <p>Chăm sóc khách hàng: Gọi tổng đài LaiRaiShop (miễn phí) hoặc trò chuyện với LaiRaiShop ngay trên trung tâm trợ giúp</p>
                    <p>Chịu Trách Nhiệm Quản Lý Nội Dung: Trần Đăng Khoa</p>
                    <p>© 2025 - Bản quyền thuộc về Công ty TNHH LaiRai</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/homepage.js?v=4"></script>
    
    <script>
        // Đổi ảnh khi click thumbnail
        function changeImage(element, src) {
            document.getElementById('current-img').src = src;
            const thumbs = document.querySelectorAll('.thumb-item');
            if(thumbs.length > 0) {
                thumbs.forEach(i => i.classList.remove('active'));
                element.classList.add('active');
            }
        }

        // Tăng giảm số lượng (Có check tồn kho từ PHP)
        function updateQty(change) {
            const input = document.getElementById('qty');
            let newVal = parseInt(input.value) + change;
            let maxStock = <?= (int)$product['stock'] ?>;

            if (newVal >= 1 && newVal <= maxStock) {
                input.value = newVal;
            } else if (newVal > maxStock) {
                alert("Số lượng mua vượt quá hàng có sẵn!");
            }
        }
    </script>
</body>

</html>