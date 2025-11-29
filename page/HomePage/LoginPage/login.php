<?php
session_start();

// 1. KẾT NỐI CONFIG & DB
require_once __DIR__ . '/../../../config.php';
require_once ROOT_PATH . '/db/db.php';

// --- XỬ LÝ ĐĂNG XUẤT ---
// Nếu link có ?logout=true thì xóa sạch session và reload trang
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header("Location: " . BASE_URL . "/page/HomePage/LoginPage/login.php");
    exit();
}

// Kiểm tra xem có đang ở "Cổng Mật" không
$is_secret_gate = (isset($_GET['gate']) && $_GET['gate'] === 'admin_entry');
$error = "";

// --- ĐIỀU HƯỚNG NẾU ĐÃ ĐĂNG NHẬP ---
// Nếu đã có session rồi thì không cho ở trang login nữa, đẩy đi ngay
if (isset($_SESSION['aid'])) {
    if ($_SESSION['role'] === "admin") {
        header("Location: " . BASE_URL . "/page/AdminPage/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === "seller") {
        header("Location: " . BASE_URL . "/page/SellerPage/dashboard.php");
        exit;
    } else {
        header("Location: " . BASE_URL . "/page/HomePage/homepage.php");
        exit;
    }
}

// --- XỬ LÝ FORM ĐĂNG NHẬP ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $username_esc = $conn->real_escape_string($username);
    $sql = "SELECT * FROM acc WHERE username = '$username_esc'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
            
            // =================================================================
            // LOGIC CHIA LUỒNG (QUAN TRỌNG NHẤT)
            // =================================================================
            
            if ($is_secret_gate) {
                // --- TRƯỜNG HỢP 1: ĐANG Ở CỔNG ADMIN ---
                
                if ($user['role'] === 'admin') {
                    // Đúng là Admin -> Mời vào
                    $_SESSION['aid'] = $user['aid'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['fullname'] = !empty(trim($user['afname']." ".$user['alname'])) ? trim($user['afname']." ".$user['alname']) : $user['username'];
                    
                    header("Location: " . BASE_URL . "/page/AdminPage/dashboard.php");
                    exit;
                } else {
                    // Là User hoặc Seller nhưng đi nhầm cổng Admin
                    // -> BÁO LỖI TẠI CHỖ (Không chuyển hướng, nên không bị lỗi bên SellerPage)
                    $error = "CẢNH BÁO: Tài khoản này không có quyền truy cập trang Quản trị!";
                }

            } else {
                // --- TRƯỜNG HỢP 2: ĐANG Ở CỔNG THƯỜNG ---
                
                if ($user['role'] === 'admin') {
                    // Admin đi cổng thường -> Giả vờ sai pass
                    $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
                } else {
                    // User/Seller đi cổng thường -> Mời vào
                    $_SESSION['aid'] = $user['aid'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['fullname'] = !empty(trim($user['afname']." ".$user['alname'])) ? trim($user['afname']." ".$user['alname']) : $user['username'];

                    if ($user['role'] === "seller") {
                        header("Location: " . BASE_URL . "/page/SellerPage/dashboard.php");
                    } else {
                        header("Location: " . BASE_URL . "/page/HomePage/homepage.php");
                    }
                    exit;
                }
            }
            // =================================================================

        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        // --- CHECK ADMIN CỨNG (DEV) ---
        if ($username === "admin" && $password === "123456") {
            if ($is_secret_gate) {
                $_SESSION['aid'] = 0;
                $_SESSION['role'] = "admin";
                $_SESSION['fullname'] = "Administrator";
                header("Location: " . BASE_URL . "/page/AdminPage/dashboard.php");
                exit;
            }
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/page/HomePage/style/login_style.css?v=2">
    <link rel="icon" href="<?php echo BASE_URL; ?>/images/icon.png" />
</head>
<body>

    <div class="auth-header">
        <div class="header-content">
            <a href="<?php echo BASE_URL; ?>/page/HomePage/homepage.php" class="header-left">
                <div class="header-logo">
                    <img src="<?php echo BASE_URL; ?>/images/logo2.png" alt="LaiRaiShop">
                </div>
                <span class="page-title">Đăng Nhập</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/page/HomePage/HelpPage/help_center.php" class="help-link">Bạn cần giúp đỡ?</a>
        </div>
    </div>

    <div class="auth-body">
        <div class="auth-container">
            <div class="auth-branding">
                <img src="<?php echo BASE_URL; ?>/images/big-logo.png" alt="Branding">
                <h3>Nền tảng thương mại điện tử<br>yêu thích ở Đông Nam Á</h3>
            </div>

            <div class="auth-form-box">
                <div class="form-title">
                    Đăng nhập 
                    <?php if ($is_secret_gate) echo '<span class="text-danger font-weight-bold ml-2" style="font-size: 0.6em;">(ADMIN AREA)</span>'; ?>
                </div>

                <?php if (isset($_SESSION['security_alert'])): ?>
                    <div class="alert alert-danger text-center" style="font-size: 13px;">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['security_alert']; ?>
                        <?php unset($_SESSION['security_alert']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="color: red; font-size: 13px; margin-bottom: 15px; background: #ffebeb; padding: 8px; border: 1px solid #ffcccc; border-radius: 2px;">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <div class="input-group">
                        <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required autocomplete="new-password">
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Mật khẩu" required autocomplete="new-password">
                        <span class="toggle-password" onclick="togglePassword('loginPassword', this)">
                            <i class="fas fa-eye-slash"></i>
                        </span>
                    </div>
                    <button type="submit" class="btn-auth">ĐĂNG NHẬP</button>
                </form>

                <div class="auth-footer-text">
                    Bạn mới biết đến LaiRaiShop? <a href="<?php echo BASE_URL; ?>/page/HomePage/SignupPage/signup.php">Đăng ký</a>
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
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>LAIRAISHOP VIỆT NAM</h3>
                    <ul><li><a href="<?php echo BASE_URL; ?>/page/HomePage/ContentPage/about.php">Về LaiRaiShop</a></li></ul>
                </div>
                <div class="footer-column">
                    <h3>THANH TOÁN</h3>
                    <div class="payment-icons">
                        <img src="https://down-vn.img.susercontent.com/file/d4bbea4570b93bfd5fc652ca82a262a8" alt="Visa">
                    </div>
                </div>
                <div class="footer-column">
                    <h3>THEO DÕI CHÚNG TÔI TRÊN</h3>
                    <ul class="social-links">
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="copyright">
                    © 2025 LaiRaiShop. Tất cả các quyền được bảo lưu
                    <a href="login.php?gate=admin_entry" style="color: inherit; text-decoration: none; cursor: default;">.</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/js/login.js?v=4"></script>
</body>
</html>