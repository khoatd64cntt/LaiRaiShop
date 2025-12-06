<?php
// FILE: page/SellerPage/CreateSellerPage/create_shop.php

// 1. CẤU HÌNH HIỂN THỊ LỖI
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. [QUAN TRỌNG] ĐỊNH NGHĨA ROOT_PATH VÀ BASE_URL ĐỂ TRÁNH LỖI LINE 58
// Tính toán đường dẫn gốc dựa trên vị trí file hiện tại
if (!defined('ROOT_PATH')) {
    // Đi lùi 3 cấp thư mục: CreateSellerPage -> SellerPage -> page -> LaiRaiShop (Root)
    define('ROOT_PATH', realpath(__DIR__ . '/../../../'));
}

if (!defined('BASE_URL')) {
    // Định nghĩa đường dẫn URL cơ sở (bạn có thể sửa '/LaiRaiShop' nếu tên thư mục khác)
    define('BASE_URL', '/LaiRaiShop'); 
}

// 3. GỌI FILE SESSION
// Kiểm tra file tồn tại trước khi require để tránh lỗi fatal
$session_path = __DIR__ . '/../types/seller_session.php';
if (file_exists($session_path)) {
    require_once $session_path;
} else {
    // Fallback nếu không tìm thấy file session theo đường dẫn tương đối
    die("Lỗi: Không tìm thấy file session tại " . $session_path);
}

// --- LOGIC XỬ LÝ ĐĂNG KÝ SHOP ---
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shop_name = trim($_POST['shop_name']);
    $description = trim($_POST['description']);

    if (!empty($shop_name)) {
        // Kiểm tra kết nối database từ session có tồn tại không
        if (!isset($conn)) {
            $error = "Lỗi kết nối cơ sở dữ liệu (biến \$conn không tồn tại).";
        } else {
            // Kiểm tra tên shop
            $stmt_check = $conn->prepare("SELECT sid FROM shops WHERE shop_name = ?");
            $stmt_check->bind_param("s", $shop_name);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $error = "Tên Shop này đã được sử dụng, vui lòng chọn tên khác!";
            } else {
                // Kiểm tra biến $aid từ session
                if (!isset($aid)) {
                    $aid = $_SESSION['aid'] ?? 0; // Lấy từ session nếu biến $aid chưa set
                }

                if ($aid > 0) {
                    // Thêm shop mới
                    $stmt = $conn->prepare("INSERT INTO shops (aid, shop_name, description) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $aid, $shop_name, $description);

                    if ($stmt->execute()) {
                        $new_shop_id = $conn->insert_id;
                        
                        // Cập nhật quyền seller cho user
                        $conn->query("UPDATE acc SET role = 'seller' WHERE aid = $aid");

                        // Cập nhật session
                        $_SESSION['shop_id'] = $new_shop_id;
                        $_SESSION['shop_name'] = $shop_name;
                        
                        // Chuyển hướng
                        echo "<script>alert('Đăng ký thành công!'); window.location.href='../dashboard.php';</script>";
                        exit();
                    } else {
                        $error = "Lỗi hệ thống: " . $stmt->error;
                    }
                } else {
                    $error = "Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.";
                }
            }
        }
    } else {
        $error = "Tên Shop không được để trống!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Kênh Người Bán | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <?php 
    if (file_exists(ROOT_PATH . '/includes/head_meta.php')) {
        include ROOT_PATH . '/includes/head_meta.php'; 
    }
    ?>
    
    <style>
        body {
            background-color: #f5f5f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .register-box {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .btn-primary {
            background-color: #135E4B;
            border-color: #135E4B;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: #0f4a3b;
            border-color: #0f4a3b;
            opacity: 0.95;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo img {
            max-width: 250px;
            height: auto;
            object-fit: contain;
        }

        .form-control:focus {
            border-color: #135E4B;
            box-shadow: 0 0 0 0.2rem rgba(19, 94, 75, 0.25);
        }

        .text-secondary:hover {
            color: #135E4B !important;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="register-box">
        <div class="brand-logo">
            <img src="<?php echo BASE_URL; ?>/images/seller.png" alt="LaiRaiShop Seller Logo" 
                 onerror="this.src='https://via.placeholder.com/250x80?text=LaiRaiShop'">
        </div>
        <h4 class="text-center mb-4">Đăng ký mở Shop</h4>
        <p class="text-center text-muted mb-4">Bắt đầu hành trình kinh doanh của bạn ngay hôm nay!</p>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Tên Shop <span class="text-danger">*</span></label>
                <input type="text" name="shop_name" class="form-control" placeholder="Nhập tên cửa hàng của bạn..." required>
            </div>

            <div class="form-group">
                <label>Mô tả Shop</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Giới thiệu đôi chút về shop (VD: Chuyên đồ điện tử chính hãng...)"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
                Đăng Ký Ngay
            </button>

            <div class="text-center mt-3">
                <a href="<?php echo BASE_URL; ?>/page/HomePage/homepage.php" class="text-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại trang chủ
                </a>
            </div>
        </form>
    </div>

</body>
</html>