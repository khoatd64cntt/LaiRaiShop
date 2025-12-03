<?php
// FILE: page/SellerPage/order_detail.php

// 1. KẾT NỐI HỆ THỐNG
$session_path = __DIR__ . '/types/seller_session.php';
$config_path  = __DIR__ . '/../../config.php';

if (file_exists($session_path)) require_once $session_path;
else die("Lỗi: Không tìm thấy file session.");

if (file_exists($config_path)) require_once $config_path;

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];

// 2. LẤY ID ĐƠN HÀNG TỪ URL
if (!isset($_GET['oid']) || empty($_GET['oid'])) {
    echo "<script>alert('Không tìm thấy mã đơn hàng!'); window.location.href='orders.php';</script>";
    exit;
}
$oid = intval($_GET['oid']);

// --- [LOGIC MỚI] XỬ LÝ NÚT QUAY LẠI ---
$back_link = 'orders.php'; // Mặc định về trang đơn cần xử lý
$back_text = 'Quay lại danh sách';

if (isset($_GET['ref']) && $_GET['ref'] == 'history') {
    $back_link = 'orders_history.php'; // Nếu từ lịch sử -> Về lịch sử
    $back_text = 'Quay lại lịch sử';
}
// --------------------------------------

// 3. TRUY VẤN THÔNG TIN ĐƠN HÀNG
$sql_order = "SELECT o.*, acc.username, acc.phone as user_phone, acc.email
              FROM orders o
              JOIN acc ON o.aid = acc.aid
              JOIN order_items oi ON o.oid = oi.oid
              JOIN products p ON oi.pid = p.pid
              WHERE o.oid = $oid AND p.sid = $sid
              GROUP BY o.oid";

$result_order = $conn->query($sql_order);

if ($result_order->num_rows == 0) {
    echo "<script>alert('Đơn hàng không tồn tại hoặc không thuộc shop của bạn!'); window.location.href='orders.php';</script>";
    exit;
}
$order = $result_order->fetch_assoc();

// 4. TRUY VẤN CHI TIẾT SẢN PHẨM
$sql_items = "SELECT oi.*, p.name, p.main_image 
              FROM order_items oi 
              JOIN products p ON oi.pid = p.pid 
              WHERE oi.oid = $oid AND p.sid = $sid";
$result_items = $conn->query($sql_items);

$shop_total = 0;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $oid ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>
    <style>
        /* CSS dùng chung */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f4f6f9;
            color: #333;
        }

        a {
            text-decoration: none;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #e1e1e1;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 24px;
            color: #088178;
        }

        .sidebar-header h2 {
            font-size: 20px;
            color: #088178;
            font-weight: 700;
        }

        .user-profile {
            padding: 20px;
            text-align: center;
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
        }

        .user-profile img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid #088178;
        }

        .user-profile h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 10px 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: #555;
            font-weight: 500;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: #e8f6ea;
            color: #088178;
            border-left-color: #088178;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        /* Detail Card Style */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h2 {
            font-size: 22px;
            color: #333;
        }

        /* Nút Quay lại */
        .btn-back {
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .detail-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #555;
            margin: 0;
        }

        .card-body {
            padding: 25px;
        }

        .info-row {
            display: flex;
            margin-bottom: 15px;
        }

        .info-label {
            width: 150px;
            color: #888;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value {
            flex: 1;
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            color: #444;
            font-size: 14px;
            vertical-align: middle;
        }

        .item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
            margin-right: 15px;
        }

        .total-row {
            display: flex;
            justify-content: flex-end;
            padding-top: 15px;
            font-size: 16px;
        }

        .total-label {
            margin-right: 20px;
            font-weight: 600;
            color: #555;
        }

        .total-amount {
            color: #d35400;
            font-weight: 700;
            font-size: 20px;
        }

        /* Badge Status */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }

        .st-completed {
            background: #d4edda;
            color: #155724;
        }

        .st-pending {
            background: #fff3cd;
            color: #856404;
        }

        .st-shipped {
            background: #cce5ff;
            color: #004085;
        }

        .st-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .st-paid {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-header"><i class="fas fa-shopping-bag"></i>
            <h2>Kênh Người Bán</h2>
        </div>
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($shop_name) ?>&background=088178&color=fff" alt="Shop Logo">
            <h4><?= htmlspecialchars($shop_name) ?></h4>
            <p>ID Shop: #<?= $sid ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="ProductPage/products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="ProductPage/add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>

            <li><a href="orders.php" class="<?= (!isset($_GET['ref']) || $_GET['ref'] != 'history') ? 'active' : '' ?>"><i class="fas fa-file-invoice-dollar"></i> Đơn cần xử lý</a></li>
            <li><a href="orders_history.php" class="<?= (isset($_GET['ref']) && $_GET['ref'] == 'history') ? 'active' : '' ?>"><i class="fas fa-history"></i> Lịch sử đơn hàng</a></li>

            <li style="border-top: 1px solid #eee; margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
            </li>
            <li>
                <a href="../HomePage/LoginPage/logout.php" onclick="return confirm('Bạn muốn đăng xuất?');" style="color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Chi tiết đơn hàng #<?= $oid ?></h2>
            <a href="<?= $back_link ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> <?= $back_text ?>
            </a>
        </div>

        <div class="detail-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Thông tin chung</h3>
                <span class="badge st-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Ngày đặt hàng:</div>
                    <div class="info-value"><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Khách hàng:</div>
                    <div class="info-value"><?= htmlspecialchars($order['username']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Số điện thoại:</div>
                    <div class="info-value"><?= htmlspecialchars($order['phone']) ?> (SĐT Nhận hàng) / <?= htmlspecialchars($order['user_phone']) ?> (SĐT Tài khoản)</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Địa chỉ nhận:</div>
                    <div class="info-value"><?= htmlspecialchars($order['address']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Ghi chú:</div>
                    <div class="info-value text-muted"><?= !empty($order['note']) ? htmlspecialchars($order['note']) : 'Không có' ?></div>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="card-header">
                <h3><i class="fas fa-box-open"></i> Sản phẩm trong đơn</h3>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $result_items->fetch_assoc()):
                            $item_total = $item['price'] * $item['quantity'];
                            $shop_total += $item_total;

                            $imgSrc = $item['main_image'];
                            if (function_exists('getImage')) {
                                $displayImg = getImage($imgSrc);
                            } else {
                                if (strpos($imgSrc, 'http') === 0) $displayImg = $imgSrc;
                                else $displayImg = BASE_URL . '/' . ltrim($imgSrc, '/');
                            }
                        ?>
                            <tr>
                                <td style="display: flex; align-items: center;">
                                    <img src="<?= $displayImg ?>" class="item-img" onerror="this.src='https://via.placeholder.com/50'">
                                    <span style="font-weight: 500;"><?= htmlspecialchars($item['name']) ?></span>
                                </td>
                                <td style="text-align: right; color: #666;"><?= number_format($item['price']) ?>đ</td>
                                <td style="text-align: center; font-weight: bold;">x<?= $item['quantity'] ?></td>
                                <td style="text-align: right; font-weight: 600; color: #333;"><?= number_format($item_total) ?>đ</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="total-row">
                    <span class="total-label">Tổng doanh thu đơn này:</span>
                    <span class="total-amount"><?= number_format($shop_total) ?>đ</span>
                </div>
            </div>
        </div>

    </div>

</body>

</html>