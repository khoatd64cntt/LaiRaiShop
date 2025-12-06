<?php
// File: page/HomePage/AccountPage/register_seller.php
session_start();

// 1. KẾT NỐI DATABASE
// Điều chỉnh đường dẫn require tùy theo vị trí thực tế của file này
require_once '../../../config.php';
require_once ROOT_PATH . '/db/db.php';

// 2. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['aid'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='" . BASE_URL . "/page/HomePage/LoginPage/login.php';</script>";
    exit();
}

$aid = $_SESSION['aid'];

// 3. KIỂM TRA TRẠNG THÁI TRONG DB
$sql_check = "SELECT * FROM shops WHERE aid = $aid";
$result = $conn->query($sql_check);
$shop = $result->fetch_assoc();

// --- LOGIC ĐIỀU HƯỚNG QUAN TRỌNG ---

// Trường hợp A: Đã là Seller chính thức (Active)
if ($shop && $shop['status'] == 'active') {
    // Cập nhật session quyền Seller nếu chưa có để vào được Dashboard
    $_SESSION['role'] = 'seller';
    $_SESSION['shop_id'] = $shop['sid'];

    // Chuyển hướng vào trang quản lý
    header("Location: " . BASE_URL . "/page/SellerPage/dashboard.php");
    exit();
}

// Trường hợp B: Đang chờ duyệt (Pending) -> KHÔNG LÀM GÌ CẢ, ĐỂ NÓ CHẠY XUỐNG DƯỚI HIỆN HTML THÔNG BÁO
// (Code HTML bên dưới sẽ xử lý hiển thị giao diện chờ)

// 4. XỬ LÝ KHI NGƯỜI DÙNG BẤM NÚT ĐĂNG KÝ
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_register'])) {
    $shop_name = trim($_POST['shop_name']);
    $desc = trim($_POST['description']);

    // Kiểm tra tên shop trùng
    $check_name = $conn->query("SELECT sid FROM shops WHERE shop_name = '$shop_name'");
    if ($check_name->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Tên Shop này đã có người dùng!</div>";
    } else {
        // Insert với trạng thái PENDING
        $stmt = $conn->prepare("INSERT INTO shops (aid, shop_name, description, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iss", $aid, $shop_name, $desc);

        if ($stmt->execute()) {
            // Refresh lại trang để lọt vào trường hợp B (Hiện thông báo chờ)
            header("Location: register_seller.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký Kênh Người Bán</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: sans-serif;
        }

        .main-card {
            width: 100%;
            max-width: 550px;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .card-header-custom {
            padding: 30px;
            text-align: center;
            color: white;
        }

        /* Màu sắc cho từng trạng thái */
        .header-pending {
            background: linear-gradient(45deg, #f6d365, #fda085);
        }

        /* Màu cam vàng */
        .header-new {
            background: #135E4B;
        }

        /* Màu xanh */
        .header-banned {
            background: #e74a3b;
        }

        /* Màu đỏ */
    </style>
</head>

<body>

    <div class="main-card">

        <?php if ($shop && $shop['status'] == 'pending'): ?>
            <div class="card-header-custom header-pending">
                <i class="fas fa-clock fa-4x mb-3"></i>
                <h2>ĐANG CHỜ DUYỆT</h2>
                <p class="mb-0">Hồ sơ của bạn đã được gửi thành công!</p>
            </div>
            <div class="p-4 text-center">
                <h5 class="mb-3">Shop: <strong><?= htmlspecialchars($shop['shop_name']) ?></strong></h5>
                <div class="alert alert-warning text-left" style="font-size: 14px;">
                    <i class="fas fa-info-circle mr-1"></i> <strong>Lưu ý:</strong><br>
                    Admin đang xem xét hồ sơ của bạn. Quá trình này có thể mất từ vài giờ đến 24h.<br>
                    Sau khi được duyệt, bạn sẽ có thể truy cập vào trang quản lý bán hàng.
                </div>
                <a href="<?= BASE_URL ?>/page/HomePage/homepage.php" class="btn btn-outline-dark btn-block mt-4">
                    <i class="fas fa-arrow-left"></i> Quay về trang chủ
                </a>
            </div>

        <?php elseif ($shop && $shop['status'] == 'banned'): ?>
            <div class="card-header-custom header-banned">
                <i class="fas fa-ban fa-4x mb-3"></i>
                <h2>BỊ TỪ CHỐI</h2>
            </div>
            <div class="p-4 text-center">
                <p class="text-danger">Tài khoản bán hàng của bạn đã bị khóa hoặc đơn đăng ký không được chấp thuận.</p>
                <a href="<?= BASE_URL ?>/page/HomePage/homepage.php" class="btn btn-secondary btn-block">Về trang chủ</a>
            </div>

        <?php else: ?>
            <div class="card-header-custom header-new">
                <i class="fas fa-store fa-4x mb-3"></i>
                <h3>Đăng Ký Bán Hàng</h3>
                <p class="mb-0">Khởi tạo gian hàng của bạn cùng LaiRaiShop</p>
            </div>
            <div class="p-4">
                <?= $message ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Tên Shop <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-tag"></i></span></div>
                            <input type="text" name="shop_name" class="form-control" placeholder="VD: Shop Thời Trang An An..." required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Mô tả ngắn</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Giới thiệu shop..."></textarea>
                    </div>
                    <button type="submit" name="submit_register" class="btn btn-success btn-block py-2 font-weight-bold" style="background: #135E4B;">
                        Gửi Đơn Đăng Ký
                    </button>
                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/page/HomePage/homepage.php" class="text-muted">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>