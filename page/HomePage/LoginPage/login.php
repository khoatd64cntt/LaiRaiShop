<?php
session_start();
require_once '../../../db/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Escape username để tránh lỗi SQL
    $username_esc = $conn->real_escape_string($username);

    // Query tài khoản trong DB (bảng acc)
    $sql = "SELECT * FROM acc WHERE username = '$username_esc'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
            // Lưu session
            $_SESSION['aid'] = $user['aid'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['afname'] . " " . $user['alname'];

            // ✅ Điều hướng theo role
            if ($user['role'] === "admin") {
                header("Location: ../../AdminPage/dashboard.php");
                exit;
            }
            elseif ($user['role'] === "seller") {
                header("Location: seller.php");
                exit;
            }
            elseif ($user['role'] === "user") {
                header("Location: ../homepage.php");
                exit;
            }
            else {
                $error = "Tài khoản không có role hợp lệ!";
            }
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        // Ngoài DB không có nhưng check acc admin cứng
        if ($username === "admin" && $password === "123456") {
            $_SESSION['aid'] = 0;
            $_SESSION['role'] = "admin";
            $_SESSION['fullname'] = "Administrator";
            header("Location: admin/dashboard.php");
            exit;
        }

        $error = "Tên đăng nhập không tồn tại!";
    }
}
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập tài khoản | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../style/login_style.css?v=1">
    <link rel="icon" href="images/icon.png" />
</head>

<body>

    <div class="auth-header">
        <div class="header-content">
            <a href="homepage.php" class="header-left">
                <div class="header-logo">
                    <img src="../../images/logo2.png" alt="LaiRaiShop">
                </div>
                <span class="page-title">Đăng nhập</span>
            </a>
            <a href="#" class="help-link">Bạn cần giúp đỡ?</a>
        </div>
    </div>

    <div class="auth-body">
        <div class="auth-container">

            <div class="auth-branding">
                <img src="../../../images/big-logo.png" alt="Branding">
                <h3>Nền tảng thương mại điện tử<br>yêu thích ở Đông Nam Á</h3>
            </div>

            <div class="auth-form-box">
                <div class="form-title">Đăng nhập</div>

                <?php if ($error): ?>
                    <div style="color: red; font-size: 13px; margin-bottom: 15px; background: #ffebeb; padding: 8px; border: 1px solid #ffcccc; border-radius: 2px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <div class="input-group">
                        <input type="text" name="username" class="form-control" placeholder="Email/Số điện thoại/Tên đăng nhập" required autocomplete="new-password">
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Mật khẩu" required autocomplete="new-password">
                        <span class="toggle-password" onclick="togglePassword('loginPassword', this)">
                            <i class="fas fa-eye-slash"></i>
                        </span>
                    </div>

                    <button type="submit" class="btn-auth">ĐĂNG NHẬP</button>
                </form>

                <div class="auth-utils">
                    <a href="#">Quên mật khẩu</a>
                </div>

                <div class="auth-divider">
                    <span></span>
                    <p>HOẶC</p><span></span>
                </div>

                <div class="social-login">
                    <a href="#" class="btn-social"><i class="fab fa-facebook" style="color: #3b5998;"></i> Facebook</a>
                    <a href="#" class="btn-social"><i class="fab fa-google" style="color: #db4437;"></i> Google</a>
                </div>

                <div class="auth-footer-text">
                    Bạn mới biết đến LaiRaiShop? <a href="../SignupPage/signup.php">Đăng ký</a>
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

    <script src="js/login.js?v=4"></script>
</body>

</html>