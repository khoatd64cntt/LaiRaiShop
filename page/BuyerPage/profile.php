<?php
// FILE: page/HomePage/profile.php
session_start();
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['aid'])) {
    header("Location: LoginPage/login.php");
    exit();
}
$aid = $_SESSION['aid'];
$msg = "";

// 2. Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $afname = $_POST['afname'];
    $alname = $_POST['alname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Update DB
    $sql_update = "UPDATE acc SET afname=?, alname=?, email=?, phone=? WHERE aid=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssssi", $afname, $alname, $email, $phone, $aid);
    
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Cập nhật hồ sơ thành công!</div>";
        // Cập nhật lại Session
        $_SESSION['fullname'] = $afname . " " . $alname;
    } else {
        $msg = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// 3. Lấy thông tin User
$sql = "SELECT * FROM acc WHERE aid = $aid";
$user = $conn->query($sql)->fetch_assoc();

// Hàm hiển thị ảnh đại diện
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&background=random&size=128";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ Sơ Của Tôi | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/homepage.css?v=4">
    <link rel="icon" href="../../images/icon.png" />
    
    <style>
        body { background-color: #f5f5f5; font-size: 14px; }
        
        /* Sidebar bên trái */
        .profile-sidebar { width: 100%; padding: 10px 0; }
        .user-brief { display: flex; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #efefef; margin-bottom: 15px; }
        .user-brief img { width: 50px; height: 50px; border-radius: 50%; border: 1px solid #e1e1e1; margin-right: 15px; }
        .user-brief div { font-weight: 600; color: #333; overflow: hidden; text-overflow: ellipsis; }
        .user-brief a { font-weight: 400; color: #888; font-size: 12px; text-decoration: none; }
        
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu li { margin-bottom: 10px; }
        .sidebar-menu a { text-decoration: none; color: #333; display: block; padding: 5px 0; transition: color 0.2s; }
        .sidebar-menu a:hover { color: #ee4d2d; }
        .sidebar-menu li.active > a { color: #ee4d2d; font-weight: 600; }
        .sidebar-menu i { width: 25px; text-align: center; color: #555; margin-right: 10px; }

        /* Card nội dung chính */
        .profile-main-card { background: #fff; box-shadow: 0 1px 2px 0 rgba(0,0,0,.13); border-radius: 2px; padding: 30px; min-height: 500px; }
        .profile-header { border-bottom: 1px solid #efefef; padding-bottom: 18px; margin-bottom: 25px; }
        .profile-header h3 { font-size: 18px; margin: 0; color: #333; font-weight: 500; }
        .profile-header p { margin: 5px 0 0; font-size: 13px; color: #555; }

        /* Form styling */
        .form-group label { color: #555555cc; font-weight: 500; }
        .form-control:focus { border-color: #888; box-shadow: none; }
        .form-control-plaintext { color: #333; font-weight: 500; }
        .btn-save { background-color: #ee4d2d; color: #fff; border: none; padding: 8px 25px; border-radius: 2px; box-shadow: 0 1px 1px 0 rgba(0,0,0,.09); }
        .btn-save:hover { background-color: #d73211; color: #fff; }

        /* Avatar Upload */
        .avatar-upload-section { border-left: 1px solid #efefef; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding-left: 20px; }
        .avatar-preview-lg { width: 100px; height: 100px; border-radius: 50%; background-color: #f5f5f5; overflow: hidden; margin-bottom: 20px; border: 1px solid #e1e1e1; }
        .avatar-preview-lg img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>

    <div class="sticky-header-wrapper">
        <header class="lairai-header">
            <div class="container header-content">
                <div class="logo"><a href="homepage.php"><img src="../../images/logo.png" alt="Logo"></a></div>
                <div class="top-bar-right" style="margin-left: auto;">
                     <a href="homepage.php" class="text-white">Trang Chủ</a> 
                     <span class="mx-2 text-white">|</span> 
                     <a href="LoginPage/logout.php" class="text-white">Đăng Xuất</a>
                </div>
            </div>
        </header>
    </div>

    <div class="container mt-4 mb-5">
        <div class="row">
            
            <div class="col-md-3">
                <div class="profile-sidebar">
                    <div class="user-brief">
                        <img src="<?= $avatarUrl ?>" alt="Avatar">
                        <div>
                            <div><?= htmlspecialchars($user['username']) ?></div>
                            <a href="profile.php"><i class="fas fa-pen"></i> Sửa hồ sơ</a>
                        </div>
                    </div>

                    <ul class="sidebar-menu">
                        <li class="active">
                            <a href="profile.php"><i class="fas fa-user text-primary"></i> Tài Khoản Của Tôi</a>
                            <ul style="list-style: none; padding-left: 35px; margin-top: 5px;">
                                <li><a href="profile.php" style="color: #ee4d2d;">Hồ Sơ</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="purchase.php"><i class="fas fa-file-alt text-primary"></i> Đơn Mua</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-bell text-danger"></i> Thông Báo</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-9">
                <div class="profile-main-card">
                    <div class="profile-header">
                        <h3>Hồ Sơ Của Tôi</h3>
                        <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
                    </div>

                    <?= $msg ?>
                    
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label text-right">Tên đăng nhập</label>
                                    <div class="col-sm-9">
                                        <p class="form-control-plaintext"><?= htmlspecialchars($user['username']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label text-right">Họ</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="afname" class="form-control" value="<?= htmlspecialchars($user['afname']) ?>">
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label text-right">Tên</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="alname" class="form-control" value="<?= htmlspecialchars($user['alname']) ?>">
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label text-right">Email</label>
                                    <div class="col-sm-9">
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label text-right">Số điện thoại</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                                    </div>
                                </div>

                                <div class="form-group row mt-4">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" class="btn btn-save">Lưu</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="avatar-upload-section">
                                    <div class="avatar-preview-lg">
                                        <img src="<?= $avatarUrl ?>" alt="Avatar Large">
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm mb-2">Chọn Ảnh</button>
                                    <div class="text-muted text-center" style="font-size: 12px;">
                                        Dụng lượng file tối đa 1 MB<br>
                                        Định dạng:.JPEG, .PNG
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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