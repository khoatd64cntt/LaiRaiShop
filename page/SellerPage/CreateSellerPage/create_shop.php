<?php
// FILE: page/SellerPage/CreateSellerPage/create_shop.php

// 1. CẤU HÌNH & KẾT NỐI
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Xác định đường dẫn thực tế để include file (Tránh lỗi đường dẫn)
$root_path = realpath(__DIR__ . '/../../../');
require_once $root_path . '/config.php';
require_once $root_path . '/db/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// 2. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['aid'])) {
    // Dùng đường dẫn tương đối để tránh lỗi 404
    header("Location: ../../../page/HomePage/LoginPage/login.php");
    exit();
}

$aid = $_SESSION['aid'];

// 3. KIỂM TRA TRẠNG THÁI HIỆN TẠI CỦA SHOP
$sql_check = "SELECT * FROM shops WHERE aid = $aid";
$result = $conn->query($sql_check);
$shop = $result->fetch_assoc();

// --- [TRƯỜNG HỢP A]: SHOP ĐÃ ĐƯỢC DUYỆT (ACTIVE) ---
// Chỉ khi Admin đã duyệt thì mới cho vào Dashboard
if ($shop && $shop['status'] == 'active') {
    // Cập nhật Session
    if ($_SESSION['role'] !== 'seller') {
        $_SESSION['role'] = 'seller';
        $_SESSION['shop_id'] = $shop['sid'];
        $_SESSION['shop_name'] = $shop['shop_name'];
        // Cập nhật DB cho chắc chắn
        $conn->query("UPDATE acc SET role='seller' WHERE aid=$aid");
    }
    // Chuyển hướng vào Dashboard (Lùi 1 cấp ra SellerPage)
    header("Location: ../dashboard.php");
    exit();
}

// --- [TRƯỜNG HỢP B]: XỬ LÝ FORM ĐĂNG KÝ ---
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_register'])) {
    $shop_name = trim($_POST['shop_name']);
    $description = trim($_POST['description']);

    if (empty($shop_name)) {
        $error = "Vui lòng nhập tên cửa hàng!";
    } else {
        // Kiểm tra tên shop trùng
        $check_name = $conn->prepare("SELECT sid FROM shops WHERE shop_name = ?");
        $check_name->bind_param("s", $shop_name);
        $check_name->execute();

        if ($check_name->get_result()->num_rows > 0) {
            $error = "Tên Shop này đã có người sử dụng!";
        } else {
            // [QUAN TRỌNG]: Insert trạng thái 'pending'
            $stmt = $conn->prepare("INSERT INTO shops (aid, shop_name, description, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iss", $aid, $shop_name, $description);

            if ($stmt->execute()) {
                // Hiện thông báo JS và reload trang để hiện giao diện chờ
                echo "<script>
                    alert('Đăng ký thành công! Vui lòng chờ Admin xét duyệt.');
                    window.location.href = 'create_shop.php';
                </script>";
                exit();
            } else {
                $error = "Lỗi hệ thống: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kênh Người Bán | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #e9ecef;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .main-card {
            width: 100%;
            max-width: 550px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header-custom {
            padding: 35px 20px;
            text-align: center;
            color: white;
        }

        /* Màu nền theo trạng thái */
        .bg-pending {
            background: linear-gradient(to right, #f2994a, #f2c94c);
        }

        /* Màu vàng chờ đợi */
        .bg-new {
            background: #135E4B;
        }

        /* Màu xanh đăng ký mới */
        .bg-banned {
            background: #e74a3b;
        }

        /* Màu đỏ bị khóa */

        .status-icon {
            font-size: 50px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .btn-custom {
            background: #135E4B;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background: #0e4638;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <div class="main-card">

        <?php if ($shop && $shop['status'] == 'pending'): ?>
            <div class="card-header-custom bg-pending">
                <i class="fas fa-clock status-icon"></i>
                <h2>ĐANG CHỜ DUYỆT</h2>
                <p class="mb-0">Hồ sơ của bạn đã được gửi thành công!</p>
            </div>
            <div class="p-4 text-center">
                <h5 class="text-dark font-weight-bold mb-3"><?= htmlspecialchars($shop['shop_name']) ?></h5>

                <div class="alert alert-light border text-left" style="font-size: 14px; background: #fffdf5;">
                    <i class="fas fa-info-circle text-warning mr-1"></i>
                    <strong>Thông báo:</strong><br>
                    Admin đang xem xét hồ sơ đăng ký của bạn. Quá trình này có thể mất vài giờ.<br>
                    Vui lòng quay lại sau khi được duyệt.
                </div>

                <a href="../../../page/HomePage/homepage.php" class="btn btn-outline-dark btn-block mt-4 rounded-pill">
                    <i class="fas fa-arrow-left mr-2"></i> Trở về Trang Chủ
                </a>
            </div>

        <?php elseif ($shop && $shop['status'] == 'banned'): ?>
            <div class="card-header-custom bg-banned">
                <i class="fas fa-ban status-icon"></i>
                <h2>TÀI KHOẢN BỊ KHÓA</h2>
            </div>
            <div class="p-4 text-center">
                <p class="text-danger">Đơn đăng ký của bạn đã bị từ chối hoặc Shop bị khóa.</p>
                <a href="../../../page/HomePage/homepage.php" class="btn btn-secondary btn-block">Về Trang Chủ</a>
            </div>

        <?php else: ?>
            <div class="card-header-custom bg-new">
                <i class="fas fa-store status-icon"></i>
                <h3>Đăng Ký Mở Shop</h3>
                <p class="mb-0">Bắt đầu kinh doanh cùng LaiRaiShop</p>
            </div>

            <div class="p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="font-weight-bold">Tên Cửa Hàng <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text bg-light"><i class="fas fa-tag"></i></span></div>
                            <input type="text" name="shop_name" class="form-control" placeholder="Ví dụ: Shop Thời Trang An An..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Mô tả ngắn</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Giới thiệu đôi chút về gian hàng..."></textarea>
                    </div>

                    <button type="submit" name="submit_register" class="btn btn-custom btn-block mt-4">
                        Gửi Đơn Đăng Ký
                    </button>

                    <div class="text-center mt-3">
                        <a href="../../../page/HomePage/homepage.php" class="text-muted small">
                            <i class="fas fa-arrow-left"></i> Hủy bỏ, quay về trang chủ
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>