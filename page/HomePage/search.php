<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';

// 1. NHẬN PARAM
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$cat_input = isset($_GET['category']) ? $_GET['category'] : '';
$price_min = isset($_GET['price_min']) ? intval($_GET['price_min']) : '';
$price_max = isset($_GET['price_max']) ? intval($_GET['price_max']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// 2. URL RESET CHO SIDEBAR (Giữ lại từ khóa, chỉ xóa lọc)
$resetUrl = "search.php";
if (!empty($keyword)) {
    $resetUrl .= "?keyword=" . urlencode($keyword);
}

// 3. QUERY - CHỈ NHỮNG SẢN PHẨM ĐƯỢC DUYỆT MỚI HIỂN THỊ
$sql = "SELECT p.*, c.name as cat_name FROM products p 
        LEFT JOIN categories c ON p.cid = c.cid 
        WHERE p.status = 'approved'";
$params = [];
$types = "";

if (!empty($keyword)) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$keyword%";
    $types .= "s";
}
if (!empty($cat_input)) {
    // Tách chuỗi ID danh mục thành mảng
    $id_array = explode(',', $cat_input);
    // Ép kiểu từng phần tử thành số nguyên để tránh SQL Injection
    $id_array = array_map('intval', $id_array);
    // Loại bỏ các giá trị không hợp lệ (nếu có)
    $id_array = array_filter($id_array);

    if (!empty($id_array)) {
        // Nối lại thành chuỗi để dùng trong câu lệnh SQL
        $ids_string = implode(',', $id_array);
        // Dùng IN để lọc nhiều danh mục
        $sql .= " AND p.cid IN ($ids_string) ";
    }
}
if (!empty($price_min)) {
    $sql .= " AND p.price >= ?";
    $params[] = $price_min;
    $types .= "i";
}
if (!empty($price_max)) {
    $sql .= " AND p.price <= ?";
    $params[] = $price_max;
    $types .= "i";
}

switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'sales':
        $sql .= " ORDER BY p.stock ASC";
        break;
    default:
        $sql .= " ORDER BY p.pid DESC";
        break;
}

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. DANH MỤC
$allCategories = $conn->query("SELECT * FROM categories WHERE parent_id IS NOT NULL")->fetch_all(MYSQLI_ASSOC);

// Link header
$sellerLink = BASE_URL . "/page/HomePage/LoginPage/login.php";
if (isset($_SESSION['aid'])) {
    if ($_SESSION['role'] === 'seller') $sellerLink = BASE_URL . "/page/SellerPage/dashboard.php";
    elseif ($_SESSION['role'] === 'user') $sellerLink = BASE_URL . "/page/SellerPage/CreateSellerPage/create_shop.php";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm: <?php echo htmlspecialchars($keyword); ?> | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/page/HomePage/style/homepage.css?v=4">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/page/HomePage/style/search.css?v=9">
    <link rel="icon" href="<?php echo BASE_URL; ?>/images/icon.png" />
</head>

<body>
    <div class="sticky-header-wrapper">
        <div class="top-bar">
            <div class="container top-bar-content">
                <div class="top-bar-left">
                    <a href="<?php echo $sellerLink; ?>">Kênh Người Bán</a>
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
                            Xin chào, <strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></strong>
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

    <div class="container search-page-container">

        <aside class="search-sidebar">
            <div class="filter-group">
                <div class="filter-header-main"><i class="fas fa-filter"></i> BỘ LỌC TÌM KIẾM</div>

                <div class="filter-section">
                    <h4>Theo Danh Mục</h4>
                    <div class="search-category-list">
                        <?php foreach ($allCategories as $cat): ?>
                            <a href="search.php?keyword=<?php echo urlencode($keyword); ?>&category=<?php echo $cat['cid']; ?>"
                                class="search-cat-item <?php echo ($cat_id == $cat['cid']) ? 'active' : ''; ?>">
                                <?php echo $cat['name']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-section">
                    <h4>Khoảng Giá</h4>
                    <form id="priceFilterForm" method="GET" action="search.php">
                        <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                        <?php if (!empty($cat_input)): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($cat_input); ?>">
                        <?php endif; ?>

                        <div class="price-inputs">
                            <input type="number" name="price_min" placeholder="₫ TỪ" value="<?php echo $price_min ?: ''; ?>">
                            <div class="dash">-</div>
                            <input type="number" name="price_max" placeholder="₫ ĐẾN" value="<?php echo $price_max ?: ''; ?>">
                        </div>
                        <button type="submit" class="btn-apply">ÁP DỤNG</button>
                    </form>
                </div>

                <div class="filter-footer">
                    <a href="<?php echo $resetUrl; ?>" class="btn-reset-all">Xóa tất cả</a>
                </div>
            </div>
        </aside>

        <main class="search-results">
            <div class="search-result-info">
                <?php if ($keyword): ?>
                    <i class="far fa-lightbulb"></i> Kết quả tìm kiếm cho từ khóa '<strong><?php echo htmlspecialchars($keyword); ?></strong>'
                <?php else: ?>
                    Tất cả sản phẩm
                <?php endif; ?>
            </div>

            <div class="sort-bar">
                <span class="sort-label">Sắp xếp theo</span>
                <a href="?keyword=<?php echo $keyword; ?>&sort=newest" class="sort-btn <?php echo ($sort == 'newest') ? 'active' : ''; ?>">Mới nhất</a>
                <a href="?keyword=<?php echo $keyword; ?>&sort=sales" class="sort-btn <?php echo ($sort == 'sales') ? 'active' : ''; ?>">Bán chạy</a>

                <div class="sort-price-dropdown">
                    <span class="<?php echo ($sort == 'price_asc' || $sort == 'price_desc') ? 'active-text' : ''; ?>">
                        <?php
                        if ($sort == 'price_asc') echo 'Giá: Thấp đến Cao';
                        elseif ($sort == 'price_desc') echo 'Giá: Cao đến Thấp';
                        else echo 'Giá';
                        ?>
                    </span>
                    <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
                    <div class="dropdown-content">
                        <a href="?keyword=<?php echo $keyword; ?>&sort=price_asc">Giá: Thấp đến Cao</a>
                        <a href="?keyword=<?php echo $keyword; ?>&sort=price_desc">Giá: Cao đến Thấp</a>
                    </div>
                </div>
            </div>

            <div class="product-grid search-grid">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $row):
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
                                <div class="product-name"><?php echo $row['name']; ?></div>
                                <div class="product-meta">
                                    <div class="product-price"><?php echo number_format($row['price'], 0, ',', '.'); ?><span class="currency">đ</span></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-result">
                        <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/search/a60759ad1dabe909c46a.png" alt="No result">
                        <p class="msg">Hix. Không có sản phẩm nào. Bạn thử tắt điều kiện lọc và tìm lại nhé?</p>
                        <p class="or">hoặc</p>
                        <a href="search.php" class="btn-clear-main">Xóa bộ lọc</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/search.js?v=2"></script>
</body>

</html>