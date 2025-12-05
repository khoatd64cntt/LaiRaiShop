<?php
// FILE: page/AdminPage/Layout/header.php
require_once __DIR__ . '/../../../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['aid']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/page/HomePage/LoginPage/login.php");
    exit();
}

$url_admin = BASE_URL . '/page/AdminPage/';
$url_logout = BASE_URL . '/page/HomePage/LoginPage/logout.php';

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        return number_format($amount, 0, ',', '.') . '₫';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị Admin | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>
    <style>
        body {
            background: #f8f9fa;
            font-family: sans-serif;
            overflow-x: hidden;
        }

        /* --- [1] CỐ ĐỊNH SIDEBAR (FIXED POSITION) --- */
        #sidebar-wrapper {
            position: fixed;
            /* Giúp sidebar đứng yên, không cuộn theo trang */
            top: 0;
            left: 0;
            bottom: 0;
            width: 220px;
            /* Độ rộng cố định */
            z-index: 1000;
            background: #135E4B;
            color: #fff;
            overflow-y: auto;
            /* Cho phép cuộn riêng trong menu nếu quá dài */
            transition: all 0.3s ease;
            /* Hiệu ứng trượt mượt mà */
            /* Ẩn thanh cuộn của sidebar cho đẹp */
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #sidebar-wrapper::-webkit-scrollbar {
            display: none;
        }

        /* --- [2] ĐẨY NỘI DUNG CHÍNH QUA PHẢI --- */
        #page-content-wrapper {
            width: 100%;
            margin-left: 220px;
            /* Phải khớp với width của sidebar */
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* --- [3] TRẠNG THÁI KHI ĐÓNG MENU (TOGGLED) --- */
        #wrapper.toggled #sidebar-wrapper {
            margin-left: -220px;
            /* Ẩn sidebar sang trái */
        }

        #wrapper.toggled #page-content-wrapper {
            margin-left: 0;
            /* Nội dung tràn ra toàn màn hình */
        }

        /* Style cho các link trong menu */
        .sidebar-heading {
            padding: 1.5rem 1rem;
            font-size: 1.2rem;
            background: #0e4638;
            text-align: center;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
            /* Giữ tiêu đề menu luôn ở trên cùng */
        }

        .list-group-item {
            background: #135E4B;
            color: #d1d3e2;
            border: none;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .list-group-item:hover {
            background: #1aa17f;
            color: #fff;
            text-decoration: none;
            padding-left: 25px;
            /* Hiệu ứng hover đẩy nhẹ */
            transition: 0.2s;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
    </style>

    <script>
        // Kiểm tra ngay trong thẻ head xem user đã đóng menu chưa
        if (localStorage.getItem("sidebar_state") === "toggled") {
            // Nếu đã đóng, thêm class toggled vào html hoặc body ngay lập tức để CSS ẩn nó đi trước khi vẽ
            document.write('<style>#sidebar-wrapper { margin-left: -220px !important; } #page-content-wrapper { margin-left: 0 !important; }</style>');
        }
    </script>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading"><i class="fas fa-wine-bottle mr-2"></i>LaiRai Admin</div>
            <div class="list-group list-group-flush">
                <a href="<?php echo $url_admin; ?>dashboard.php" class="list-group-item"><i class="fas fa-tachometer-alt mr-2"></i> Tổng quan</a>
                <a href="<?php echo $url_admin; ?>CategoriesPage/categories_list.php" class="list-group-item"><i class="fas fa-list mr-2"></i> Danh mục</a>
                <a href="<?php echo $url_admin; ?>ProductPage/products_list.php" class="list-group-item"><i class="fas fa-box mr-2"></i> Sản phẩm</a>
                <a href="<?php echo $url_admin; ?>OrderPage/orders_list.php" class="list-group-item"><i class="fas fa-file-invoice-dollar mr-2"></i> Đơn hàng</a>
                <a href="<?php echo $url_admin; ?>UserPage/users_list.php" class="list-group-item"><i class="fas fa-users mr-2"></i> Người dùng</a>
                <a href="<?php echo $url_admin; ?>ReportsRevenuePage/reports_revenue.php" class="list-group-item"><i class="fas fa-chart-line mr-2"></i> Báo cáo</a>

                <div class="dropdown-divider border-secondary my-3"></div>
                <a href="<?php echo $url_logout; ?>" class="list-group-item text-warning font-weight-bold" onclick="return confirm('Đăng xuất ngay?');">
                    <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 shadow-sm sticky-top">
                <button class="btn btn-success btn-sm" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle font-weight-bold text-dark" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle mr-1"></i> <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#">Hồ sơ</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="<?php echo $url_logout; ?>">Đăng xuất</a>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="container-fluid p-4">