<?php
// --- 1. CẤU HÌNH ĐƯỜNG DẪN GỐC (QUAN TRỌNG) ---
// Do dự án nằm trong thư mục CKMNM/LaiRaiShop nên phải khai báo đầy đủ
$project_folder = '/CKMNM/LaiRaiShop';

// Các đường dẫn dùng chung
$url_admin = $project_folder . '/page/AdminPage/'; // Dẫn đến thư mục AdminPage
$url_login = $project_folder . '/page/HomePage/LoginPage/'; // Dẫn đến thư mục Login

// --- 2. KẾT NỐI DB ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dùng __DIR__ để lùi ra đúng file db.php dù header được gọi ở đâu
// header.php nằm ở: CKMNM/LaiRaiShop/page/AdminPage/Layout/
// Cần lùi 3 cấp: Layout -> AdminPage -> page -> LaiRaiShop -> db
$db_path = __DIR__ . '/../../../db/db.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    die("Lỗi: Không tìm thấy file kết nối database tại: " . $db_path);
}

// --- 3. KIỂM TRA QUYỀN ADMIN ---
if (!isset($_SESSION['aid']) || $_SESSION['role'] !== 'admin') {
    // Nếu chưa đăng nhập, đá về trang login
    header("Location: " . $url_login . "login.php");
    exit();
}

// --- 4. HÀM HỖ TRỢ ---
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
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
    <style>
        body { background: #f8f9fa; font-family: sans-serif; overflow-x: hidden; }
        #wrapper { display: flex; }
        #sidebar-wrapper { min-height: 100vh; width: 250px; margin-left: -250px; transition: margin .25s ease-out; background: #135E4B; color: #fff; }
        #sidebar-wrapper .sidebar-heading { padding: 1.2rem; font-size: 1.2rem; background: #0e4638; text-align: center; font-weight: bold; }
        #sidebar-wrapper .list-group { width: 250px; }
        #sidebar-wrapper .list-group-item { background: #135E4B; color: #e9ecef; border: none; padding: 15px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        #sidebar-wrapper .list-group-item:hover { background: #1aa17f; color: #fff; text-decoration: none; padding-left: 25px; transition: 0.2s; }
        #wrapper.toggled #sidebar-wrapper { margin-left: 0; }
        #page-content-wrapper { width: 100%; }
        .card { border: none; border-radius: 8px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <div id="sidebar-wrapper">
        <div class="sidebar-heading"><i class="fas fa-wine-bottle mr-2"></i>LaiRai Admin</div>
        <div class="list-group list-group-flush">
            
            <a href="<?php echo $url_admin; ?>dashboard.php" class="list-group-item">
                <i class="fas fa-tachometer-alt mr-2"></i> Tổng quan
            </a>

            <a href="<?php echo $url_admin; ?>CategoriesPage/categories_list.php" class="list-group-item">
                <i class="fas fa-list mr-2"></i> Danh mục
            </a>

            <a href="<?php echo $url_admin; ?>ProductPage/products_list.php" class="list-group-item">
                <i class="fas fa-box mr-2"></i> Sản phẩm
            </a>

            <a href="<?php echo $url_admin; ?>OrderPage/orders_list.php" class="list-group-item">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Đơn hàng
            </a>

            <a href="<?php echo $url_admin; ?>UserPage/users_list.php" class="list-group-item">
                <i class="fas fa-users mr-2"></i> Người dùng
            </a>

            <a href="<?php echo $url_admin; ?>ReportsRevenuePage/reports_revenue.php" class="list-group-item">
                <i class="fas fa-chart-line mr-2"></i> Báo cáo
            </a>

            <div class="dropdown-divider border-secondary"></div>

            <a href="<?php echo $url_login; ?>login.php?logout=true" class="list-group-item text-warning font-weight-bold">
                <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
            </a>
        </div>
    </div>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
            <button class="btn btn-outline-success btn-sm" id="menu-toggle"><i class="fas fa-bars"></i> Menu</button>
            <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
                <li class="nav-item">
                    <span class="nav-link font-weight-bold text-dark">
                        <i class="fas fa-user-circle mr-1"></i> Xin chào, <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Admin'; ?>
                    </span>
                </li>
            </ul>
        </nav>

        <div class="container-fluid p-4">