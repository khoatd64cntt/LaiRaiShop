<?php
session_start();


// --- [MỚI] 1. CHẶN CACHE TRÌNH DUYỆT (Để sửa lỗi nút Back) ---
// Giúp trình duyệt không lưu trang này vào bộ nhớ đệm. 
// Khi logout xong bấm Back, trình duyệt buộc phải tải lại trang -> Session đã mất -> Hiện giao diện khách.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Kết nối Config & DB (Giữ nguyên)
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';

// --- [MỚI] 2. CHẶN ADMIN (Cách 1: Strict Mode) ---
// Nếu là Admin -> Đá ngay về Dashboard, không cho xem Homepage
if (isset($_SESSION['aid']) && $_SESSION['role'] === 'admin') {
    header("Location: " . BASE_URL . "/page/AdminPage/dashboard.php");
    exit();
}

// --- LOGIC XỬ LÝ LINK KÊNH NGƯỜI BÁN (Giữ nguyên) ---
$sellerLink = BASE_URL . "/page/HomePage/LoginPage/login.php";

if (isset($_SESSION['aid'])) {
    if ($_SESSION['role'] === 'seller') {
        $sellerLink = BASE_URL . "/page/SellerPage/dashboard.php";
    } elseif ($_SESSION['role'] === 'user') {
        $sellerLink = BASE_URL . "/page/SellerPage/CreateSellerPage/create_shop.php";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaiRaishop | Mua Sắm Trực Tuyến</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/page/HomePage/style/homepage.css?v=4">
    <link rel="icon" href="<?php echo BASE_URL; ?>/images/icon.png" />
</head>

<body>

    <div class="sticky-header-wrapper">
        <div class="top-bar">
            <div class="container top-bar-content">
                <div class="top-bar-left">
                    <a href="<?php echo $sellerLink; ?>">Kênh Người Bán</a>

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
                    <?php if (isset($_SESSION['aid'])): ?>
                        <span class="auth-link" style="color: white;">
                            Xin chào, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong>
                        </span>
                        <span style="color: white; margin: 0 5px;">|</span>
                        <a href="<?php echo BASE_URL; ?>/page/HomePage/LoginPage/logout.php" class="auth-link">Đăng Xuất</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/page/HomePage/SignupPage/signup.php" class="auth-link">Đăng Ký</a>
                        <a href="<?php echo BASE_URL; ?>/page/HomePage/LoginPage/login.php" class="auth-link">Đăng Nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <header class="lairai-header">
            <div class="container header-content">
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>/page/HomePage/homepage.php">
                        <img src="<?php echo BASE_URL; ?>/images/logo.png" alt="LaiRaiShop Logo">
                    </a>
                </div>

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

    <div class="main-container">

        <div class="hero-banner-section">
            <div class="hero-banner-main">
                <div id="lairaiCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
                    <ol class="carousel-indicators lairai-indicators">
                        <li data-target="#lairaiCarousel" data-slide-to="0" class="active"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="1"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="2"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="3"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="4"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="5"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="6"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="7"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="8"></li>
                        <li data-target="#lairaiCarousel" data-slide-to="9"></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item active"><img src="https://down-vn.img.susercontent.com/file/vn-11134258-820l4-mf82135tct8s16@resize_w1594_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821dk-mh8uv7fap5vt17@resize_w1594_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821e9-mh9ynhcbucy282@resize_w797_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821d5-mh8pve8i1b0qce@resize_w797_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821gh-mh8uv9mopzimd0@resize_w797_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821fk-mh8uvbf493497a@resize_w797_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821dl-mh9ynk3youtlc5@resize_w1594_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821dq-mh9ynpdx9bm0f5@resize_w797_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821ey-mh9ynnduj66mcf@resize_w1594_nl.webp"></div>
                        <div class="carousel-item"><img src="https://down-vn.img.susercontent.com/file/vn-11134258-820l4-mh8pziequo7jdd@resize_w797_nl.webp"></div>
                    </div>
                    <a class="carousel-control-prev lairai-control" href="#lairaiCarousel" role="button" data-slide="prev"><span class="carousel-control-prev-icon"></span></a>
                    <a class="carousel-control-next lairai-control" href="#lairaiCarousel" role="button" data-slide="next"><span class="carousel-control-next-icon"></span></a>
                </div>
            </div>
            <div class="hero-banner-side">
                <a href="BannerPage/ad_banner1.php"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821gh-mh91gxeue8ei70@resize_w796_nl.webp"></a>
                <a href="BannerPage/ad_banner2.php"><img src="https://down-vn.img.susercontent.com/file/sg-11134258-821fr-mh91h4o61xxm9d@resize_w398_nl.webp"></a>
            </div>
        </div>

        <div class="category-section">
            <div class="category-header">DANH MỤC</div>
            <div class="category-wrapper">
                <button class="cat-prev-btn" id="catPrevBtn"><i class="fas fa-chevron-left"></i></button>
                <div class="category-list" id="categoryList">
                    <?php
                    $categories = [
                        ["name" => "Thời Trang Nam", "img" => "https://down-vn.img.susercontent.com/file/687f3967b7c2fe6a134a2c11894eea4b_tn"],
                        ["name" => "Thời Trang Nữ", "img" => "https://down-vn.img.susercontent.com/file/75ea42f9eca124e9cb3cde744c060e4d_tn"],
                        ["name" => "Điện Thoại & Phụ Kiện", "img" => "https://down-vn.img.susercontent.com/file/31234a27876fb89cd522d7e3db1ba5ca_tn"],
                        ["name" => "Thiết Bị Điện Tử", "img" => "https://down-vn.img.susercontent.com/file/978b9e4cb61c611aaaf58664fae133c5_tn"],
                        ["name" => "Nhà Cửa & Đời Sống", "img" => "https://down-vn.img.susercontent.com/file/24b194a695ea59d384768b7b471d563f_tn"],
                        ["name" => "Máy Tính & Laptop", "img" => "https://down-vn.img.susercontent.com/file/c3f3edfaa9f6dafc4825b77d8449999d_tn"],
                        ["name" => "Máy Ảnh & Quay Phim", "img" => "https://down-vn.img.susercontent.com/file/ec14dd4fc238e676e43be2a911414d4d_tn"],
                        ["name" => "Đồng Hồ", "img" => "https://down-vn.img.susercontent.com/file/86c294aae72ca1db5f541790f7796260@resize_w640_nl.webp"],
                        ["name" => "Giày Dép Nữ", "img" => "https://down-vn.img.susercontent.com/file/48630b7c76a7b62bc070c9e227097847@resize_w320_nl.webp"],
                        ["name" => "Giày Dép Nam", "img" => "https://down-vn.img.susercontent.com/file/74ca517e1fa74dc4d974e5d03c3139de_tn"],
                        ["name" => "Túi Ví Nữ", "img" => "https://down-vn.img.susercontent.com/file/fa6ada2555e8e51f369718bbc92ccc52@resize_w320_nl.webp"],
                        ["name" => "Thiết Bị Điện Gia Dụng", "img" => "https://down-vn.img.susercontent.com/file/7abfbfee3c4844652b4a8245e473d857@resize_w320_nl.webp"],
                        ["name" => "Phụ Kiện & Trang Sức", "img" => "https://down-vn.img.susercontent.com/file/8e71245b9659ea72c1b4e737be5cf42e_tn"],
                        ["name" => "Nhà Sách Online", "img" => "https://down-vn.img.susercontent.com/file/36013311815c55d303b0e6c62d6a8139@resize_w320_nl.webp"],
                        ["name" => "Balo & Túi Ví Nam", "img" => "https://down-vn.img.susercontent.com/file/18fd9d878ad946db2f1bf4e33760c86f@resize_w640_nl.webp"],
                        ["name" => "Thời Trang Trẻ Em", "img" => "https://down-vn.img.susercontent.com/file/4540f87aa3cbe99db739f9e8dd2cdaf0@resize_w640_nl.webp"],
                        ["name" => "Dụng Cụ Tiện Ích", "img" => "https://down-vn.img.susercontent.com/file/e4fbccba5e1189d1141b9d6188af79c0@resize_w320_nl.webp"],
                    ];
                    foreach ($categories as $cat) {
                    ?>
                        <a href="#" class="category-item">
                            <div class="cat-img-wrapper"><img src="<?php echo $cat['img']; ?>" alt="<?php echo $cat['name']; ?>"></div>
                            <div class="cat-name"><?php echo $cat['name']; ?></div>
                        </a>
                    <?php } ?>
                </div>
                <button class="cat-next-btn" id="catNextBtn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="suggestion-section">
            <div class="suggestion-header-wrapper">
                <div class="suggestion-header-wrapper">
                    <div class="suggestion-header">
                        <div class="suggestion-title">
                            SẢN PHẨM GỢI Ý
                        </div>
                    </div>
                </div>
            </div>

            <div class="product-grid">
                <?php
                // Chỉ lấy những sản phẩm có status là 'approved'
                $sql = "SELECT * FROM products WHERE status = 'approved' ORDER BY pid DESC LIMIT 12";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $imgSrc = $row['main_image'];
                        if (strpos($imgSrc, 'http') === false) {
                            $cleanPath = ltrim($imgSrc, '.');
                            $imgSrc = BASE_URL . $cleanPath;
                        }
                ?>
                        <a href="detail.php?id=<?php echo $row['pid']; ?>" class="product-card has-border">
                            <div class="product-img">
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo $row['name']; ?>" onerror="this.onerror=null; this.src='https://placehold.co/300x300?text=No+Image';">

                            </div>

                            <div class="product-info">
                                <div class="product-name">
                                    <?php echo $row['name']; ?>
                                </div>

                                <div class="product-meta">
                                    <div class="product-price">
                                        <?php echo number_format($row['price'], 0, ',', '.'); ?><span class="currency">đ</span>
                                    </div>
                                </div>
                            </div>

                            <div class="find-similar-btn">Tìm sản phẩm tương tự</div>
                        </a>
                <?php
                    }
                } else {
                    echo "<div class='empty-state'>Chưa có sản phẩm nào để hiển thị.</div>";
                }
                ?>
            </div>

            <div class="load-more">
                <a href="<?php echo BASE_URL; ?>/page/HomePage/LoginPage/login.php" style="text-decoration: none;">
                    <button>Đăng Nhập Để Xem Thêm</button>
                </a>
            </div>
        </div>
    </div>

    <footer class="lairai-footer">
        <div class="container">
            <div class="footer-content">

                <div class="footer-column">
                    <h3>CHĂM SÓC KHÁCH HÀNG</h3>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/page/HomePage/HelpPage/help_center.php">Trung Tâm Trợ Giúp</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/page/HomePage/ContentPage/tutorial1.php">Hướng Dẫn Mua Hàng/Đặt Hàng</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/page/HomePage/ContentPage/tutorial2.php">Hướng Dẫn Bán Hàng</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>LAIRAISHOP VIỆT NAM</h3>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/page/HomePage/ContentPage/about.php">Về LaiRaiShop</a></li>
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
                        <li><a href="https://www.facebook.com/ShopeeVN" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="https://www.instagram.com/Shopee_VN" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="https://www.linkedin.com/company/shopee" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>TẢI ỨNG DỤNG LAIRAI</h3>
                    <div class="download-app">
                        <div class="qr-code">
                            <img src="https://down-vn.img.susercontent.com/file/a5e589e8e118e937dc660f224b9a1472" alt="QR Code">
                        </div>
                        <div class="app-stores">
                            <a href="https://shopee.vn/web" target="_blank" rel="noopener noreferrer"><img src="https://down-vn.img.susercontent.com/file/ad01628e90ddf248076685f73497c163" alt="App Store"></a>
                            <a href="https://shopee.vn/web" target="_blank" rel="noopener noreferrer"><img src="https://down-vn.img.susercontent.com/file/ae7dced05f7243d0f3171f786e123def" alt="Google Play"></a>
                            <a href="https://shopee.vn/web" target="_blank" rel="noopener noreferrer"><img src="https://down-vn.img.susercontent.com/file/35352374f39bdd03b25e7b83542b2cb0" alt="App Gallery"></a>
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
                    <a>Việt Nam</a>
                    | <a>Lào</a>
                    | <a>Singapore</a>
                    | <a>Thái Lan</a>
                    | <a>Philippines</a>
                    | <a>Đông Timor</a>
                    | <a>Indonesia</a>
                    | <a>Malaysia</a>
                    | <a>Brunei</a>
                    | <a>Đài Loan</a>
                </div>
            </div>
        </div>

        <div class="footer-policy">
            <div class="container">
                <div class="policy-row">
                    <a>CHÍNH SÁCH BẢO MẬT</a>
                    <a>QUY CHẾ HOẠT ĐỘNG</a>
                    <a>CHÍNH SÁCH VẬN CHUYỂN</a>
                    <a>CHÍNH SÁCH TRẢ HÀNG VÀ HOÀN TIỀN</a>
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
    <script src="<?php echo BASE_URL; ?>/js/homepage.js?v=4"></script>
</body>

</html>