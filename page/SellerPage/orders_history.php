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

// --- [MỚI] XỬ LÝ XÓA ĐƠN HÀNG ---
if (isset($_POST['delete_oid'])) {
    $oid_del = $_POST['delete_oid'];
    
    // Kiểm tra xem đơn hàng này có thuộc shop không trước khi xóa (Bảo mật)
    $check = $conn->query("SELECT 1 FROM order_items oi JOIN products p ON oi.pid = p.pid WHERE oi.oid = $oid_del AND p.sid = $sid");
    
    if ($check->num_rows > 0) {
        // Thực hiện xóa đơn hàng
        // Lưu ý: Việc này sẽ xóa đơn hàng khỏi database.
        // Nếu muốn giữ dữ liệu cho Admin nhưng ẩn với Seller, cần thêm cột 'deleted_by_seller' trong DB.
        // Ở đây tôi làm xóa hẵn theo yêu cầu của bạn.
        $stmt = $conn->prepare("DELETE FROM orders WHERE oid = ?");
        $stmt->bind_param("i", $oid_del);
        
        if ($stmt->execute()) {
            echo "<script>alert('Đã xóa lịch sử đơn hàng!'); window.location.href='orders_history.php';</script>";
        } else {
            echo "<script>alert('Lỗi xóa: " . $conn->error . "');</script>";
        }
    }
}

// 3. LẤY DANH SÁCH (CHỈ ĐƠN ĐÃ HOÀN THÀNH HOẶC HỦY)
$sql = "SELECT o.oid, o.order_date, o.status, a.username, a.phone, SUM(oi.quantity * oi.price) as shop_total 
        FROM orders o
        JOIN order_items oi ON o.oid = oi.oid
        JOIN products p ON oi.pid = p.pid
        JOIN acc a ON o.aid = a.aid
        WHERE p.sid = $sid AND (o.status = 'completed' OR o.status = 'cancelled')
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
    <?php include ROOT_PATH . '/includes/head_meta.php'; ?>
    <style>
        /* --- CSS STYLE ĐỒNG BỘ DASHBOARD --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        a { text-decoration: none; }

        /* SIDEBAR */
        .sidebar { width: 260px; background-color: #fff; border-right: 1px solid #e1e1e1; display: flex; flex-direction: column; position: fixed; height: 100%; top: 0; left: 0; z-index: 100; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
        .sidebar-header i { font-size: 24px; color: #135E4B; }
        .sidebar-header h2 { font-size: 20px; color: #135E4B; font-weight: 700; }

        .user-profile { padding: 20px; text-align: center; background: #f9f9f9; border-bottom: 1px solid #eee; }
        .user-profile img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #088178; }
        .user-profile h4 { font-size: 16px; margin-bottom: 5px; }
        .user-profile p { font-size: 12px; color: #777; }

        .sidebar-menu { list-style: none; padding: 10px 0; flex: 1; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: #555; font-weight: 500; transition: 0.3s; font-size: 15px; border-left: 4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #e8f6ea; color: #088178; border-left-color: #088178; }
        .sidebar-menu li a i { margin-right: 15px; width: 20px; text-align: center; font-size: 16px; }

        /* MAIN CONTENT */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { font-size: 24px; color: #333; margin-bottom: 5px; }
        .page-header p { color: #777; font-size: 14px; }

        /* ORDER CARD */
        .order-card { background: white; border-radius: 10px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03); margin-bottom: 20px; border: 1px solid #f0f0f0; overflow: hidden; transition: 0.3s; }
        .order-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        .order-header { background: #f8f9fa; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; }
        .order-id { font-weight: 700; color: #333; }
        .order-date { font-size: 13px; color: #777; margin-left: 10px; }

        .order-body { padding: 20px; }
        .customer-info { margin-bottom: 15px; font-size: 14px; color: #555; border-bottom: 1px solid #f4f4f4; padding-bottom: 10px; }
        
        .item-list { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .item-list td { padding: 10px 0; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        .item-img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; margin-right: 15px; }
        .item-name { font-weight: 500; color: #333; }

        .order-footer { background: #fff; padding: 15px 20px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .total-price { font-size: 18px; color: #d35400; font-weight: 700; }

        /* BADGES */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .st-completed { background: #d4edda; color: #155724; }
        .st-cancelled { background: #f8d7da; color: #721c24; }

        /* BUTTONS */
        .action-group { display: flex; gap: 8px; align-items: center; }
        
        .btn-view { padding: 6px 15px; background: #f39c12; color: white; border-radius: 4px; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-view:hover { background: #e67e22; color: white; }

        /* Nút Xóa Mới */
        .btn-delete-order { padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 4px; font-size: 13px; cursor: pointer; transition: 0.2s; }
        .btn-delete-order:hover { background: #c0392b; }
        
        .btn-edit-shop { margin-top: 10px; font-size: 12px; color: #135E4B; cursor: pointer; text-decoration: underline; border: none; background: none; }
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
            <button class="btn-edit-shop"><i class="fas fa-pen"></i> Sửa thông tin</button>
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
            <h2>Lịch Sử Đơn Hàng</h2>
            <p>Danh sách các đơn hàng đã giao thành công và hoàn tất hoặc bị hủy.</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <strong style="font-size: 15px;">Đơn hàng #<?= $order['oid'] ?></strong>
                            <span style="color:#777; font-size:13px; margin-left:10px;"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                        </div>
                        
                        <div class="action-group">
                            <span class="badge st-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>

                            <a href="order_detail.php?oid=<?= $order['oid'] ?>&ref=history" class="btn-view" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>

                            <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa lịch sử đơn hàng này?');" style="display:inline;">
                                <input type="hidden" name="delete_oid" value="<?= $order['oid'] ?>">
                                <button type="submit" class="btn-delete-order" title="Xóa lịch sử">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="customer-info">
                            <i class="fas fa-user"></i> Khách: <b><?= htmlspecialchars($order['username']) ?></b> | SĐT: <?= htmlspecialchars($order['phone']) ?>
                        </div>

                        <table class="item-list">
                            <?php
                            $oid = $order['oid'];
                            $items = $conn->query("SELECT oi.*, p.name, p.main_image FROM order_items oi JOIN products p ON oi.pid = p.pid WHERE oi.oid = $oid AND p.sid = $sid");
                            while ($item = $items->fetch_assoc()):
                                $imgSrc = $item['main_image'];
                                if (function_exists('getImage')) $displayImg = getImage($imgSrc);
                                else {
                                    if (empty($imgSrc)) $displayImg = 'https://via.placeholder.com/50';
                                    elseif (strpos($imgSrc, 'http') === 0) $displayImg = $imgSrc;
                                    else $displayImg = BASE_URL . '/' . ltrim($imgSrc, '/');
                                }
                            ?>
                                <tr>
                                    <td width="60"><img src="<?= $displayImg ?>" class="item-img"></td>
                                    <td>
                                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <small style="color: #888;">Giá: <?= number_format($item['price']) ?>đ</small>
                                    </td>
                                    <td align="right" style="font-weight:bold;">x<?= $item['quantity'] ?></td>
                                    <td align="right" width="120"><b><?= number_format($item['price'] * $item['quantity']) ?>đ</b></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>

                    <div class="order-footer">
                        <div style="font-style:italic; color:#777; font-size: 13px;">
                            <?php if($order['status']=='completed') echo "Giao dịch thành công."; else echo "Đơn hàng đã hủy."; ?>
                        </div>
                        <div>Tổng thu: <span class="total-price"><?= number_format($order['shop_total']) ?> đ</span></div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; margin-top: 50px; color: #888;">
                <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px; opacity: 0.5;"></i>
                <p>Chưa có đơn hàng nào trong lịch sử.</p>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>