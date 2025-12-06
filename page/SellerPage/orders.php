<?php
// FILE: page/SellerPage/orders.php

// 1. KẾT NỐI SESSION VÀ CONFIG
$session_path = __DIR__ . '/types/seller_session.php';
$config_path  = __DIR__ . '/../../config.php';

if (file_exists($session_path)) {
    require_once $session_path;
} else {
    // Fallback
    $session_path_alt = __DIR__ . '/../types/seller_session.php';
    if (file_exists($session_path_alt)) require_once $session_path_alt;
    else die("Lỗi: Không tìm thấy file session.");
}

if (file_exists($config_path)) {
    require_once $config_path;
}

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];
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

// 2. XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG (CÓ LOGIC KHO & HOA HỒNG)
if (isset($_POST['update_status'])) {
    $order_id = $_POST['oid'];
    $new_status = $_POST['status'];

    $check = $conn->query("SELECT 1 FROM order_items oi JOIN products p ON oi.pid = p.pid WHERE oi.oid = $order_id AND p.sid = $sid");

    if ($check->num_rows > 0) {
        $old_status_query = $conn->query("SELECT status FROM orders WHERE oid = $order_id");
        $old_status = $old_status_query->fetch_assoc()['status'];

        // --- [LOGIC MỚI] KIỂM TRA KHO TRƯỚC KHI DUYỆT ---
        $can_update = true;
        $error_msg = "";

        // Chỉ cần kiểm tra kho nếu KHÔNG PHẢI là Hủy đơn (Vì hủy đơn là trả hàng về, không lo hết hàng)
        if ($new_status != 'cancelled') {
            $items = $conn->query("SELECT oi.pid, oi.quantity, p.stock, p.name 
                                   FROM order_items oi 
                                   JOIN products p ON oi.pid = p.pid 
                                   WHERE oi.oid = $order_id");

            while ($item = $items->fetch_assoc()) {
                // Nếu kho hiện tại bị âm (do bán lố trước đó), chặn duyệt tiếp
                if ($item['stock'] < 0) {
                    $can_update = false;
                    $error_msg = "Sản phẩm '" . $item['name'] . "' đang bị âm kho (" . $item['stock'] . "). Vui lòng nhập thêm hàng hoặc hủy đơn!";
                    break;
                }
            }
        }

        if ($can_update) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE oid = ?");
            $stmt->bind_param("si", $new_status, $order_id);

            if ($stmt->execute()) {
                // Xử lý hoàn kho nếu Hủy đơn
                if ($new_status == 'cancelled' && $old_status != 'cancelled') {
                    $items_cancel = $conn->query("SELECT pid, quantity FROM order_items WHERE oid = $order_id");
                    while ($item = $items_cancel->fetch_assoc()) {
                        $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE pid = {$item['pid']}");
                    }
                }

                // Xử lý trừ kho lại nếu Khôi phục đơn hủy
                if ($old_status == 'cancelled' && $new_status != 'cancelled') {
                    $items_restore = $conn->query("SELECT pid, quantity FROM order_items WHERE oid = $order_id");
                    while ($item = $items_restore->fetch_assoc()) {
                        $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE pid = {$item['pid']}");
                    }
                }

                // TRƯỜNG HỢP 2: HỦY ĐƠN ĐÃ DUYỆT (Shipped/Completed -> Cancelled)
                // Hành động: HOÀN KHO (CỘNG LẠI)
                elseif ($is_old_approved && !$is_new_approved) {
                    foreach ($items_data as $item) {
                        $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE pid = {$item['pid']}");
                    }
                }
                
                // TRƯỜNG HỢP 3: Pending -> Cancelled
                // Hành động: KHÔNG LÀM GÌ (Vì chưa trừ kho nên không cần cộng lại)

                echo "<script>alert('Cập nhật trạng thái thành công!'); window.location.href='orders.php';</script>";
            } else {
                echo "<script>alert('Lỗi cập nhật: " . $conn->error . "');</script>";
            }
        } else {
            // Báo lỗi kho
            echo "<script>alert('$error_msg'); window.location.href='orders.php';</script>";
        }
    }
}

// 3. LẤY DANH SÁCH ĐƠN HÀNG
$sql = "SELECT 
            o.oid, 
            o.order_date, 
            o.status, 
            a.username, 
            a.phone,
            SUM(oi.quantity * oi.price) as shop_total 
        FROM orders o
        JOIN order_items oi ON o.oid = oi.oid
        JOIN products p ON oi.pid = p.pid
        JOIN acc a ON o.aid = a.aid
        WHERE p.sid = $sid AND o.status != 'completed' AND o.status != 'cancelled'
        GROUP BY o.oid, o.order_date, o.status, a.username, a.phone
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>
    <style>
        /* CSS dùng chung */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        a { text-decoration: none; }

        /* Sidebar */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #e1e1e1; position: fixed; height: 100%; top: 0; left: 0; z-index: 100; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
        .sidebar-header i { font-size: 24px; color: #135E4B; }
        .sidebar-header h2 { font-size: 20px; color: #135E4B; font-weight: 700; }
        
        .user-profile { padding: 20px; text-align: center; background: #f9f9f9; border-bottom: 1px solid #eee; }
        .user-profile img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #088178; }
        .user-profile h4 { font-size: 16px; margin-bottom: 5px; }
        .user-profile p { font-size: 12px; color: #777; }

        .sidebar-menu { list-style: none; padding: 10px 0; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: #555; font-weight: 500; transition: 0.3s; font-size: 15px; border-left: 4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #e8f6ea; color: #088178; border-left-color: #088178; }
        .sidebar-menu li a i { margin-right: 15px; width: 20px; text-align: center; font-size: 16px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { font-size: 24px; color: #333; margin-bottom: 5px; }
        .page-header p { color: #777; font-size: 14px; }

        /* Order Card */
        .order-card { background: white; border-radius: 10px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03); margin-bottom: 20px; border: 1px solid #f0f0f0; overflow: hidden; transition: 0.3s; }
        .order-card:hover { box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); }
        .order-header { background: #f8f9fa; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; }
        .order-id { font-weight: 700; color: #333; }
        .order-date { font-size: 13px; color: #777; margin-left: 10px; }
        
        .order-body { padding: 20px; }
        .customer-info { margin-bottom: 15px; font-size: 14px; color: #555; border-bottom: 1px solid #f4f4f4; padding-bottom: 10px; }
        .customer-info i { width: 20px; color: #088178; }

        .item-list { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .item-list td { padding: 10px 0; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        .item-img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; margin-right: 15px; }
        .item-name { font-weight: 500; }

        .order-footer { background: #fff; padding: 15px 20px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .total-price { font-size: 18px; color: #d35400; font-weight: 700; }

        /* Badges */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .st-pending { background: #fff3cd; color: #856404; }
        .st-completed { background: #d4edda; color: #155724; }
        .st-shipped { background: #cce5ff; color: #004085; }
        .st-cancelled { background: #f8d7da; color: #721c24; }
        .st-paid { background: #d1ecf1; color: #0c5460; }

        /* Form & Buttons */
        .status-form { display: flex; gap: 10px; align-items: center; }
        .status-select { padding: 5px; border-radius: 4px; border: 1px solid #ddd; font-size: 13px; outline: none; }
        .btn-update { padding: 6px 15px; background: #088178; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-update:hover { background: #066e67; }

        /* Modal Styles */
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: #aaa;
        }

        .close-btn:hover {
            color: #333;
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

        .btn-save:hover {
            background: #066e67;
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
            <button onclick="openModal()" class="btn-edit-shop"><i class="fas fa-pen"></i> Sửa thông tin</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="ProductPage/products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="ProductPage/add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>
            <li><a href="orders.php" class="active"><i class="fas fa-clipboard-list"></i> Đơn cần xử lý</a></li>
            <li><a href="orders_history.php"><i class="fas fa-history"></i> Lịch sử đơn hàng</a></li>

            <li style="margin-top: 20px; border-top: 1px solid #eee;">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/LaiRaiShop'; ?>/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
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
            <h2>Quản lý Đơn hàng</h2>
            <p>Danh sách các đơn hàng chưa hoàn thành (Pending, Shipped, Cancelled...).</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <span class="order-id">Đơn hàng #<?= $order['oid'] ?></span>
                            <span class="order-date"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                        </div>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <span class="badge st-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>

                            <a href="order_detail.php?oid=<?= $order['oid'] ?>&ref=active" class="btn-view" style="color: white; text-decoration: none; background: #9b59b6; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="customer-info">
                            <i class="fas fa-user"></i> Khách hàng: <b><?= htmlspecialchars($order['username']) ?></b> &nbsp;|&nbsp;
                            <i class="fas fa-phone"></i> SĐT: <?= htmlspecialchars($order['phone']) ?>
                        </div>

                        <table class="item-list">
                            <?php
                            $oid = $order['oid'];
                            $sql_items = "SELECT oi.*, p.name, p.main_image 
                                              FROM order_items oi 
                                              JOIN products p ON oi.pid = p.pid 
                                              WHERE oi.oid = $oid AND p.sid = $sid";
                            $items = $conn->query($sql_items);
                            while ($item = $items->fetch_assoc()):
                            ?>
                                <tr>
                                    <td style="width: 70px;">
                                        <?php
                                        // Logic hiển thị ảnh (giống products.php)
                                        $imgSrc = $item['main_image'];
                                        if (function_exists('getImage')) {
                                            $displayImg = getImage($imgSrc);
                                        } else {
                                            if (empty($imgSrc)) $displayImg = 'https://via.placeholder.com/50?text=No+Img';
                                            elseif (strpos($imgSrc, 'http') === 0) $displayImg = $imgSrc;
                                            else $displayImg = BASE_URL . '/' . ltrim($imgSrc, '/');
                                        }
                                        ?>
                                        <img src="<?= $displayImg ?>" class="item-img" loading="lazy" onerror="this.src='https://via.placeholder.com/50?text=Err'">
                                    </td>
                                    <td>
                                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <small style="color: #888;">Đơn giá: <?= number_format($item['price']) ?>đ</small>
                                    </td>
                                    <td style="text-align: right; font-weight: bold;">x<?= $item['quantity'] ?></td>
                                    <td style="text-align: right; width: 120px; font-weight: 500;">
                                        <?= number_format($item['price'] * $item['quantity']) ?>đ
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>

                    <div class="order-footer">
                        <div class="status-form">
                            <form method="POST">
                                <input type="hidden" name="oid" value="<?= $order['oid'] ?>">
                                <select name="status" class="status-select">
                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending (Chờ duyệt)</option>
                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped (Đang giao)</option>
                                    <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed (Hoàn thành)</option>
                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled (Hủy)</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-update">Cập nhật</button>
                            </form>
                        </div>
                        <div>
                            Tổng thu của Shop: <span class="total-price"><?= number_format($order['shop_total']) ?> đ</span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; color: #888; margin-top: 50px;">
                <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px;"></i>
                <p>Không tìm thấy đơn hàng nào cần xử lý.</p>
            </div>
        <?php endif; ?>

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
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>

</html>