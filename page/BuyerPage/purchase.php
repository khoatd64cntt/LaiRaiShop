<?php 
require_once '../../db/db.php'; 

// --- MOCK DATA (DỮ LIỆU GIẢ LẬP) ---
// Bạn sẽ thay thế phần này bằng câu truy vấn SQL lấy từ bảng orders và order_details
$orders = [
    [
        "id" => "ORDER123456",
        "shop_name" => "Compliment Viet Nam",
        "status" => "HOÀN THÀNH",
        "status_code" => "completed", // completed, shipping, to_pay, etc.
        "items" => [
            [
                "name" => "Sữa Rửa Mặt Trắng Da Dưỡng Ẩm Giảm Mụn Kiềm Dầu Loại Sạch Bụi Bẩn Compliment",
                "variant" => "Phân loại hàng: GRM Salicylic 200ml",
                "qty" => 1,
                "old_price" => 254000,
                "price" => 112000,
                "img" => "https://down-vn.img.susercontent.com/file/sg-11134258-821dk-mh8uv7fap5vt17@resize_w1594_nl.webp" // Ảnh mẫu
            ]
        ],
        "total" => 110000
    ],
    [
        "id" => "ORDER789012",
        "shop_name" => "Mỹ Phẩm Mã Phượng - Chính Hãng",
        "status" => "HOÀN THÀNH",
        "status_code" => "completed",
        "items" => [
            [
                "name" => "Derma Forte Giảm Thâm Mụn, Dưỡng trắng mịn da bản thường - Mỹ Phẩm Mã Phượng",
                "variant" => "Phân loại hàng: BẢN THƯỜNG",
                "qty" => 1,
                "old_price" => 120000,
                "price" => 116000,
                "img" => "https://down-vn.img.susercontent.com/file/ef1f336ecc6f97b790d5aae9916dcb72_tn"
            ]
        ],
        "total" => 101000
    ],
    [
        "id" => "ORDER345678",
        "shop_name" => "Harumi - thế giới bông",
        "status" => "HOÀN THÀNH",
        "status_code" => "completed",
        "items" => [
            [
                "name" => "(Combo 2 Gói) Bông Tẩy Trang Lamer 201 Miếng, Bông Tẩy Trang Miniso 180 Miếng",
                "variant" => "Phân loại hàng: Lamer 201 miếng, 2 gói",
                "qty" => 1,
                "old_price" => 80000,
                "price" => 68000,
                "img" => "https://down-vn.img.susercontent.com/file/31234a27876fb89cd522d7e3db1ba5ca_tn"
            ]
        ],
        "total" => 68000
    ]
];

// Lấy tab hiện tại từ URL, mặc định là 'all'
$current_tab = isset($_GET['type']) ? $_GET['type'] : 'all';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LaiRaishop | Mua Sắm Trực Tuyến</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/purchase.css?v=4">
    <link rel="icon" href="../../images/icon.png" />
</head>

<body>
<div class="sticky-header-wrapper">
        <div class="top-bar">
            <div class="container top-bar-content">
                <div class="top-bar-left">
                    <a href="#">Kênh Người Bán</a><span>|</span><a href="#">Trở thành Người bán</a><span>|</span>
                    <div class="top-bar-connect">
                        <p>Kết nối</p> <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <div class="top-bar-right">
                    <div class="top-bar-item has-dropdown">
                        <a href="#"><i class="fas fa-bell"></i> Thông Báo</a>
                        <div class="lairai-dropdown-menu notification-dropdown">
                            <div class="notify-icon-wrapper"><i class="fas fa-user-alt"></i></div>
                            <p>Đăng nhập để xem Thông báo</p>
                            <div class="dropdown-footer">
                                <a href="../../page/HomePage/SignupPage/signup.php" class="btn-register">Đăng ký</a>
                                <a href="../../page/HomePage/LoginPage/login.php" class="btn-login">Đăng nhập</a>
                            </div>
                        </div>
                    </div>

                    <a href="#"><i class="fas fa-question-circle"></i> Hỗ Trợ</a>

                    <div class="top-bar-item has-dropdown">
                        <a href="#"><i class="fas fa-globe"></i> Tiếng Việt <i class="fas fa-chevron-down icon-chevron"></i></a>
                        <div class="lairai-dropdown-menu language-dropdown">
                            <ul>
                                <li><a href="#">Tiếng Việt</a></li>
                                <li><a href="#">English</a></li>
                            </ul>
                        </div>
                    </div>

                    <a href="../../page/HomePage/SignupPage/signup.php" class="auth-link">Đăng Ký</a><span>|</span><a href="../../page/HomePage/LoginPage/login.php" class="auth-link">Đăng Nhập</a>
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

    <div class="container mt-4 mb-5">
        <div class="row">
            
            <div class="col-md-2 d-none d-md-block">
                 <div class="profile-card">
                    <div class="profile-avatar">
                        <img src="https://placehold.co/50x50" alt="Avatar">
                    </div>
                     <div class="profile-info">
                         <div class="profile-name"></div>
                        <a href="#" class="profile-edit"><i class="fas fa-pen"></i> Thông tin cá nhân</a>
                    </div> 
                    <li>
                        <a href="#"><i class="fas fa-bell text-danger mr-2"></i> Thông Báo</a>
                    </li>
                    
                </div>

                <ul class="sidebar-menu">
                    <li>
                        <a href="#"><i class="fas fa-user text-primary mr-2"></i> Tài Khoản Của Tôi</a>
                        <ul class="sidebar-submenu pl-4">
                            <li><a href="#">Hồ Sơ</a></li>
                            <li><a href="#">Ngân Hàng</a></li>
                            <li><a href="#">Địa Chỉ</a></li>
                            <li><a href="#">Đổi Mật Khẩu</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fas fa-file-alt text-primary mr-2"></i> Đơn Mua</a>
                    </li>
                    
                    <li>
                        <a href="#"><i class="fas fa-ticket-alt text-danger mr-2"></i> Kho Voucher</a>
                    </li>
                </ul>
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
                    | <a href="#">Indonesia</a>
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
    <script src="../../js/homepage.js?v=4"></script>
</body>
</html>