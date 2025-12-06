<?php
// FILE: page/HomePage/purchase.php
session_start();
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['aid'])) {
    header("Location: LoginPage/login.php");
    exit();
}
$aid = $_SESSION['aid'];

// Lấy thông tin User để hiện ở Sidebar
$user_sql = "SELECT * FROM acc WHERE aid = $aid";
$user = $conn->query($user_sql)->fetch_assoc();
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&background=random&size=128";

// --- XỬ LÝ LỌC TRẠNG THÁI ---
$status_list = [
    'all'       => 'Tất cả',
    'pending'   => 'Chờ xác nhận',
    'paid'      => 'Đã thanh toán',
    'shipped'   => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];

$current_status = isset($_GET['status']) && array_key_exists($_GET['status'], $status_list) ? $_GET['status'] : 'all';

// --- XỬ LÝ PHÂN TRANG & QUERY ---
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$where_clause = "WHERE aid = $aid";
if ($current_status !== 'all') {
    $where_clause .= " AND status = '$current_status'";
}

$sql_count = "SELECT COUNT(*) as total FROM orders $where_clause";
$row_count = $conn->query($sql_count)->fetch_assoc();
$total_orders = $row_count['total'];
$total_pages = ceil($total_orders / $limit);

$sql_orders = "SELECT * FROM orders $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result_orders = $conn->query($sql_orders);

function getStatusBadge($status)
{
    switch ($status) {
        case 'pending':
            return 'badge-warning';
        case 'paid':
            return 'badge-info';
        case 'shipped':
            return 'badge-primary';
        case 'completed':
            return 'badge-success';
        case 'cancelled':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đơn Mua | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/homepage.css?v=4">
    <link rel="icon" href="../../images/icon.png" />

    <style>
        body {
            background-color: #f5f5f5;
            font-size: 14px;
        }

        /* Sidebar */
        .profile-sidebar {
            width: 100%;
            padding: 10px 0;
        }

        .user-brief {
            display: flex;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #efefef;
            margin-bottom: 15px;
        }

        .user-brief img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1px solid #e1e1e1;
            margin-right: 15px;
        }

        .user-brief div {
            font-weight: 600;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-brief a {
            font-weight: 400;
            color: #888;
            font-size: 12px;
            text-decoration: none;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            text-decoration: none;
            color: #333;
            display: block;
            padding: 5px 0;
            transition: color 0.2s;
        }

        .sidebar-menu a:hover {
            color: #ee4d2d;
        }

        .sidebar-menu li.active>a {
            color: #ee4d2d;
            font-weight: 600;
        }

        .sidebar-menu i {
            width: 25px;
            text-align: center;
            color: #555;
            margin-right: 10px;
        }

        /* Tabs trạng thái */
        .purchase-tabs {
            background: #fff;
            display: flex;
            border-bottom: 1px solid #e1e1e1;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .05);
            border-radius: 2px;
            overflow-x: auto;
        }

        .purchase-tabs a {
            padding: 15px 20px;
            cursor: pointer;
            flex: 1;
            text-align: center;
            font-size: 16px;
            color: #555;
            white-space: nowrap;
            text-decoration: none;
            border-bottom: 2px solid transparent;
        }

        .purchase-tabs a:hover {
            color: #ee4d2d;
        }

        .purchase-tabs a.active {
            color: #ee4d2d;
            border-bottom: 2px solid #ee4d2d;
            font-weight: 500;
        }

        /* Order Card */
        .order-card {
            background: #fff;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .05);
            border-radius: 2px;
        }

        .order-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }

        .order-body {
            padding: 0 20px;
        }

        .order-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 1px solid #e1e1e1;
            margin-right: 15px;
        }

        .order-footer {
            padding: 20px;
            background: #fffcf5;
            border-top: 1px dotted #e1e1e1;
            text-align: right;
        }

        .total-money {
            color: #ee4d2d;
            font-size: 20px;
            font-weight: bold;
        }

        .pagination {
            justify-content: center;
            margin-top: 30px;
        }

        .page-link {
            color: #555;
        }

        .page-item.active .page-link {
            background-color: #ee4d2d;
            border-color: #ee4d2d;
            color: white;
        }

        /* CSS Mới cho Empty State */
        .empty-state-box {
            background: #fff;
            text-align: center;
            padding: 50px 20px;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .05);
            border-radius: 2px;
            min-height: 400px;
            /* Chiều cao tối thiểu */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .empty-icon {
            font-size: 80px;
            color: #e0e0e0;
            /* Màu xám nhạt */
            margin-bottom: 20px;
        }
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
                        <li><a href="profile.php"><i class="fas fa-user text-primary"></i> Tài Khoản Của Tôi</a></li>
                        <li class="active"><a href="purchase.php"><i class="fas fa-file-alt text-primary"></i> Đơn Mua</a></li>
                        <li><a href="#"><i class="fas fa-bell text-danger"></i> Thông Báo</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-md-9">

                <div class="purchase-tabs">
                    <?php foreach ($status_list as $key => $label): ?>
                        <a href="purchase.php?status=<?= $key ?>"
                            class="<?= ($current_status == $key) ? 'active' : '' ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($result_orders->num_rows > 0): ?>
                    <?php while ($order = $result_orders->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <strong><i class="fas fa-store"></i> LaiRai Shop</strong>
                                    <span class="text-muted mx-2">|</span>
                                    <span>Mã đơn: #<?= $order['oid'] ?></span>
                                    <span class="text-muted ml-2 text-small" style="font-size: 12px;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                </div>
                                <span class="badge <?= getStatusBadge($order['status']) ?>" style="font-size: 13px; font-weight: normal; padding: 6px 10px;">
                                    <?= strtoupper($status_list[$order['status']] ?? $order['status']) ?>
                                </span>
                            </div>

                            <div class="order-body">
                                <?php
                                $oid = $order['oid'];
                                $sql_items = "SELECT oi.*, p.name, p.main_image 
                                              FROM order_items oi 
                                              JOIN products p ON oi.pid = p.pid 
                                              WHERE oi.oid = $oid";
                                $res_items = $conn->query($sql_items);
                                ?>

                                <?php while ($item = $res_items->fetch_assoc()): ?>
                                    <?php
                                    $imgSrc = $item['main_image'];
                                    if (strpos($imgSrc, 'http') !== 0) $imgSrc = "../../" . ltrim($imgSrc, '/');
                                    ?>
                                    <a href="detail.php?id=<?= $item['pid'] ?>" class="text-decoration-none text-dark">
                                        <div class="order-item">
                                            <img src="<?= $imgSrc ?>" class="item-img" onerror="this.src='https://placehold.co/80x80'">
                                            <div style="flex: 1;">
                                                <div style="font-weight: 500; font-size: 15px; margin-bottom: 5px;"><?= htmlspecialchars($item['name']) ?></div>
                                                <div class="text-muted">x<?= $item['quantity'] ?></div>
                                            </div>
                                            <div class="text-right">
                                                <div style="color: #ee4d2d;"><?= number_format($item['price'], 0, ',', '.') ?>₫</div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>

                            <div class="order-footer">
                                <div class="mb-3">
                                    <i class="fas fa-file-invoice-dollar text-danger"></i> Thành tiền:
                                    <span class="total-money"><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</span>
                                </div>
                                <!-- <div class="action-buttons">
                                    <?php if ($order['status'] == 'completed'): ?>
                                        <button class="btn btn-danger">Mua Lại</button>
                                        <button class="btn btn-outline-secondary">Đánh Giá</button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-secondary">Liên Hệ Người Bán</button>
                                </div> -->
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?status=<?= $current_status ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?status=<?= $current_status ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?status=<?= $current_status ?>&page=<?= $page + 1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="empty-state-box">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h5 class="text-muted">Chưa có đơn hàng nào</h5>
                        <p class="text-muted mb-4" style="font-size: 14px;">Bạn chưa có đơn hàng nào trong mục này.</p>
                        <a href="homepage.php" class="btn btn-danger px-4">Mua sắm ngay</a>
                    </div>
                <?php endif; ?>

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