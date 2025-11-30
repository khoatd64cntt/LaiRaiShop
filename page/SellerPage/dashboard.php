<?php
// FILE: page/SellerPage/dashboard.php

// 1. KẾT NỐI SESSION VÀ DB
$session_path = __DIR__ . '/types/seller_session.php';
$config_path  = __DIR__ . '/../../config.php'; // Đường dẫn tới config để có BASE_URL

if (file_exists($session_path)) {
    require_once $session_path;
} else {
    $session_path_alt = __DIR__ . '/../types/seller_session.php';
    if (file_exists($session_path_alt)) {
        require_once $session_path_alt;
    } else {
        die("Lỗi: Không tìm thấy file session.");
    }
}

if (file_exists($config_path)) require_once $config_path;

if (!isset($conn)) {
    $db_path = __DIR__ . '/../../db/db.php';
    if (file_exists($db_path)) require_once $db_path;
}

$sid = $_SESSION['shop_id'] ?? 0;
$shop_name = $_SESSION['shop_name'] ?? 'Shop';
$msg = "";

// --- XỬ LÝ CẬP NHẬT THÔNG TIN SHOP ---
if (isset($_POST['update_shop_info'])) {
    $new_name = $_POST['shop_name'];
    $new_desc = $_POST['shop_desc'];

    $stmt = $conn->prepare("UPDATE shops SET shop_name = ?, description = ? WHERE sid = ?");
    $stmt->bind_param("ssi", $new_name, $new_desc, $sid);

    if ($stmt->execute()) {
        $_SESSION['shop_name'] = $new_name;
        $shop_name = $new_name;
        $msg = "<script>alert('Cập nhật thông tin Shop thành công!');</script>";
    } else {
        $msg = "<script>alert('Lỗi cập nhật: " . $conn->error . "');</script>";
    }
}

// --- LẤY THÔNG TIN CHI TIẾT SHOP ---
$stmt_info = $conn->prepare("SELECT * FROM shops WHERE sid = ?");
$stmt_info->bind_param("i", $sid);
$stmt_info->execute();
$current_shop = $stmt_info->get_result()->fetch_assoc();

// --- TÍNH TOÁN THỐNG KÊ ---
// 1. Doanh thu
$sql_rev = "SELECT SUM(oi.quantity * oi.price) as total_revenue 
            FROM order_items oi
            JOIN products p ON oi.pid = p.pid
            JOIN orders o ON oi.oid = o.oid
            WHERE p.sid = $sid AND (o.status = 'completed' OR o.status = 'shipped')";
$res_rev = $conn->query($sql_rev);
$revenue = $res_rev->fetch_assoc()['total_revenue'] ?? 0;

// 2. Tổng đơn
$sql_orders = "SELECT COUNT(DISTINCT oi.oid) as total_orders 
               FROM order_items oi
               JOIN products p ON oi.pid = p.pid
               WHERE p.sid = $sid";
$res_orders = $conn->query($sql_orders);
$total_orders = $res_orders->fetch_assoc()['total_orders'] ?? 0;

// 3. Tổng sản phẩm
$sql_prods = "SELECT COUNT(*) as total_products FROM products WHERE sid = $sid";
$res_prods = $conn->query($sql_prods);
$total_products = $res_prods->fetch_assoc()['total_products'] ?? 0;

// 4. Đơn chờ xử lý
$sql_pending = "SELECT COUNT(DISTINCT oi.oid) as pending 
                FROM order_items oi
                JOIN products p ON oi.pid = p.pid
                JOIN orders o ON oi.oid = o.oid
                WHERE p.sid = $sid AND o.status = 'pending'";
$res_pending = $conn->query($sql_pending);
$pending_orders = $res_pending->fetch_assoc()['pending'] ?? 0;

// 5. Đơn hàng mới nhất
$sql_recent = "SELECT o.oid, acc.username, o.order_date, o.status,
               SUM(oi.quantity * oi.price) as shop_total 
               FROM orders o
               JOIN order_items oi ON o.oid = oi.oid
               JOIN products p ON oi.pid = p.pid
               JOIN acc ON o.aid = acc.aid
               WHERE p.sid = $sid
               GROUP BY o.oid, acc.username, o.order_date, o.status
               ORDER BY o.order_date DESC LIMIT 5";
$recent_orders = $conn->query($sql_recent);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Kênh Người Bán - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
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

        .sidebar {
            width: 260px;
            background-color: #fff;
            border-right: 1px solid #e1e1e1;
            display: flex;
            flex-direction: column;
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

        .user-profile p {
            font-size: 12px;
            color: #777;
        }

        .sidebar-menu {
            list-style: none;
            padding: 10px 0;
            flex: 1;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: #555;
            font-weight: 500;
            transition: 0.3s;
            font-size: 15px;
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

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #f0f0f0;
        }

        .card-info h3 {
            font-size: 26px;
            font-weight: 700;
            color: #222;
            margin-bottom: 5px;
        }

        .card-info p {
            color: #888;
            font-size: 14px;
            font-weight: 500;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .c-revenue .card-icon {
            background: #e8f6ea;
            color: #088178;
        }

        .c-orders .card-icon {
            background: #e3f2fd;
            color: #0984e3;
        }

        .c-products .card-icon {
            background: #fff3e0;
            color: #e67e22;
        }

        .c-pending .card-icon {
            background: #fdeaea;
            color: #e74c3c;
        }

        .table-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f0f0f0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            color: #444;
            font-size: 14px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .st-pending {
            background: #fff3cd;
            color: #856404;
        }

        .st-completed {
            background: #d4edda;
            color: #155724;
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

        .btn-action {
            background: #088178;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            transition: 0.2s;
        }

        .btn-action:hover {
            background: #066e67;
        }

        /* Modal & Form */
        .btn-edit-shop {
            margin-top: 10px;
            font-size: 12px;
            color: #088178;
            cursor: pointer;
            text-decoration: underline;
            border: none;
            background: none;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            width: 400px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-save {
            width: 100%;
            padding: 10px;
            background: #088178;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?= $msg ?>

    <div class="sidebar">
        <div class="sidebar-header"><i class="fas fa-shopping-bag"></i>
            <h2>Kênh Người Bán</h2>
        </div>
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($shop_name) ?>&background=088178&color=fff" alt="Shop Logo">
            <h4><?= htmlspecialchars($shop_name) ?></h4>
            <p>ID Shop: #<?= $sid ?></p>
            <button onclick="openModal()" class="btn-edit-shop"><i class="fas fa-pen"></i> Sửa thông tin</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="ProductPage/products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="ProductPage/add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>
            <li><a href="orders.php"><i class="fas fa-file-invoice-dollar"></i> Đơn hàng</a></li>

            <li style="margin-top: 20px; border-top: 1px solid #eee;">
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
            <h2>Tổng quan kinh doanh</h2>
            <p>Chào mừng trở lại! Số liệu này được lấy trực tiếp từ dữ liệu bán hàng của bạn.</p>
        </div>

        <div class="stats-grid">
            <div class="card c-revenue">
                <div class="card-info">
                    <h3><?= number_format($revenue, 0, ',', '.') ?>đ</h3>
                    <p>Doanh thu (Đã xong/Ship)</p>
                </div>
                <div class="card-icon"><i class="fas fa-wallet"></i></div>
            </div>
            <div class="card c-orders">
                <div class="card-info">
                    <h3><?= $total_orders ?></h3>
                    <p>Tổng đơn hàng</p>
                </div>
                <div class="card-icon"><i class="fas fa-shopping-bag"></i></div>
            </div>
            <div class="card c-products">
                <div class="card-info">
                    <h3><?= $total_products ?></h3>
                    <p>Sản phẩm tồn kho</p>
                </div>
                <div class="card-icon"><i class="fas fa-box-open"></i></div>
            </div>
            <div class="card c-pending">
                <div class="card-info">
                    <h3><?= $pending_orders ?></h3>
                    <p>Đơn chờ xử lý</p>
                </div>
                <div class="card-icon"><i class="fas fa-bell"></i></div>
            </div>
        </div>

        <div class="table-section">
            <div class="section-header">
                <h3>Đơn hàng mới nhất</h3>
                <a href="orders.php" style="color: #088178; font-weight: bold; font-size: 14px;">Xem tất cả &rarr;</a>
            </div>
            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Khách hàng</th>
                            <th>Doanh thu đơn này</th>
                            <th>Trạng thái</th>
                            <th style="text-align: right;">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td><b>#<?= $row['oid'] ?></b></td>
                                <td><?= date('d/m/Y', strtotime($row['order_date'])) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td style="font-weight: bold; color: #088178;"><?= number_format($row['shop_total'], 0, ',', '.') ?>đ</td>
                                <td><span class="badge st-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>

                                <td style="text-align: right;">
                                    <a href="order_detail.php?oid=<?= $row['oid'] ?>" class="btn-action">Xem</a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: #888;">
                    <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>Chưa có đơn hàng nào.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="shopModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3 style="color: #088178; text-align: center; margin-bottom: 20px;">Sửa Thông Tin Shop</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Tên Shop:</label>
                    <input type="text" name="shop_name" value="<?= htmlspecialchars($current_shop['shop_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Mô tả / Giới thiệu:</label>
                    <textarea name="shop_desc" rows="4"><?= htmlspecialchars($current_shop['description'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="update_shop_info" class="btn-save">Lưu Thay Đổi</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById("shopModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("shopModal").style.display = "none";
        }
        window.onclick = function(event) {
            var modal = document.getElementById("shopModal");
            if (event.target == modal) modal.style.display = "none";
        }
    </script>

</body>

</html>