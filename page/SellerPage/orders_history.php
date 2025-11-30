<?php
// FILE: page/SellerPage/orders_history.php

// 1. KẾT NỐI
$session_path = __DIR__ . '/types/seller_session.php';
$config_path  = __DIR__ . '/../../config.php';

if (file_exists($session_path)) require_once $session_path;
else die("Lỗi session.");

if (file_exists($config_path)) require_once $config_path;

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];

// 3. LẤY DANH SÁCH (CHỈ ĐƠN ĐÃ HOÀN THÀNH)
// status = 'completed'
$sql = "SELECT o.oid, o.order_date, o.status, a.username, a.phone, SUM(oi.quantity * oi.price) as shop_total 
        FROM orders o
        JOIN order_items oi ON o.oid = oi.oid
        JOIN products p ON oi.pid = p.pid
        JOIN acc a ON o.aid = a.aid
        WHERE p.sid = $sid AND o.status = 'completed' 
        GROUP BY o.oid, o.order_date, o.status, a.username, a.phone
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        /* Copy CSS y hệt trang orders.php để đồng bộ */
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
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: #e8f6ea;
            color: #088178;
            border-left: 4px solid #088178;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            margin-bottom: 20px;
            border: 1px solid #f0f0f0;
        }

        .order-header {
            background: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .order-body {
            padding: 20px;
        }

        .order-footer {
            background: #fff;
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .st-completed {
            background: #d4edda;
            color: #155724;
        }

        .item-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .item-list td {
            padding: 10px 0;
            border-bottom: 1px solid #f9f9f9;
        }

        .item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
            margin-right: 15px;
        }

        .btn-view {
            padding: 6px 15px;
            background: #9b59b6;
            color: white;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-view:hover {
            background: #8e44ad;
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
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="ProductPage/products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="ProductPage/add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>

            <li><a href="orders.php"><i class="fas fa-clipboard-list"></i> Đơn cần xử lý</a></li>

            <li><a href="orders_history.php" class="active"><i class="fas fa-history"></i> Lịch sử đơn hàng</a></li>

            <li style="border-top: 1px solid #eee; margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
            </li>
            <li><a href="../HomePage/LoginPage/logout.php" onclick="return confirm('Đăng xuất?');" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2 style="color: #27ae60;">Lịch Sử Đơn Hàng (Đã Xong)</h2>
            <p>Danh sách các đơn hàng đã giao thành công và hoàn tất.</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <strong>Đơn hàng #<?= $order['oid'] ?></strong>
                            <span style="color:#777; font-size:13px; margin-left:10px;"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                        </div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <span class="badge st-completed">Completed</span>

                            <a href="order_detail.php?oid=<?= $order['oid'] ?>" class="btn-view" style="color:white; text-decoration:none;">
                                <i class="fas fa-eye"></i> Xem Chi Tiết
                            </a>
                        </div>
                    </div>

                    <div class="order-body">
                        <p style="margin-bottom: 10px;"><i class="fas fa-user"></i> Khách: <b><?= htmlspecialchars($order['username']) ?></b> | SĐT: <?= htmlspecialchars($order['phone']) ?></p>

                        <table class="item-list">
                            <?php
                            $oid = $order['oid'];
                            $items = $conn->query("SELECT oi.*, p.name, p.main_image FROM order_items oi JOIN products p ON oi.pid = p.pid WHERE oi.oid = $oid AND p.sid = $sid");
                            while ($item = $items->fetch_assoc()):
                                $imgSrc = $item['main_image'];
                                if (function_exists('getImage')) $displayImg = getImage($imgSrc);
                                else $displayImg = (strpos($imgSrc, 'http') === 0) ? $imgSrc : BASE_URL . '/' . ltrim($imgSrc, '/');
                            ?>
                                <tr>
                                    <td width="60"><img src="<?= $displayImg ?>" class="item-img"></td>
                                    <td><b><?= htmlspecialchars($item['name']) ?></b><br><small>Giá: <?= number_format($item['price']) ?>đ</small></td>
                                    <td align="right">x<?= $item['quantity'] ?></td>
                                    <td align="right"><b><?= number_format($item['price'] * $item['quantity']) ?>đ</b></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>

                    <div class="order-footer">
                        <div style="font-style:italic; color:#777;">Đơn hàng đã hoàn thành.</div>
                        <div>Tổng thu: <b style="color:#d35400; font-size:18px;"><?= number_format($order['shop_total']) ?> đ</b></div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; margin-top: 50px; color: #888;">
                <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px;"></i>
                <p>Chưa có đơn hàng nào đã hoàn thành.</p>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>