<?php 
// FILE: page/SellerPage/orders.php

// 1. KẾT NỐI SESSION (ĐÃ SỬA PATH)
// Dùng __DIR__ để gọi file nằm trong thư mục 'types' cùng cấp
$session_path = __DIR__ . '/types/seller_session.php';

if (file_exists($session_path)) {
    require_once $session_path;
} else {
    die("Lỗi: Không tìm thấy file session tại: " . $session_path);
}

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];

// 2. XỬ LÝ CẬP NHẬT TRẠNG THÁI (Giữ nguyên)
if(isset($_POST['update_status'])) {
    $order_id = $_POST['oid'];
    $new_status = $_POST['status'];
    
    // Chỉ cho phép cập nhật nếu đơn hàng đó thực sự có chứa sản phẩm của shop mình
    $check = $conn->query("SELECT 1 FROM order_items oi JOIN products p ON oi.pid = p.pid WHERE oi.oid = $order_id AND p.sid = $sid");
    
    if($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE oid = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if($stmt->execute()) {
            echo "<script>alert('Cập nhật trạng thái thành công!'); window.location.href='orders.php';</script>";
        } else {
            echo "<script>alert('Lỗi cập nhật');</script>";
        }
    }
}

// 3. LẤY DANH SÁCH ĐƠN HÀNG (Giữ nguyên)
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
        WHERE p.sid = $sid
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
    <style>
        /* --- COPY CSS STYLE DASHBOARD --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        a { text-decoration: none; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #e1e1e1; position: fixed; height: 100%; top: 0; left: 0; z-index: 10; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
        .sidebar-header h2 { font-size: 20px; color: #088178; font-weight: 700; }
        .user-profile { padding: 20px; text-align: center; background: #f9f9f9; border-bottom: 1px solid #eee; }
        .sidebar-menu { list-style: none; padding: 10px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 12px 25px; color: #555; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e8f6ea; color: #088178; border-left: 4px solid #088178; }
        .sidebar-menu i { margin-right: 15px; width: 20px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #088178; border-bottom: 2px solid #eee; padding-bottom: 10px; display: inline-block; }

        /* Order Card Style */
        .order-card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #eee; overflow: hidden; }
        .order-header { background: #f8f9fa; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; }
        .order-id { font-weight: bold; color: #333; }
        .order-date { font-size: 13px; color: #777; margin-left: 10px; }
        
        .order-body { padding: 20px; }
        .customer-info { margin-bottom: 15px; font-size: 14px; color: #555; border-bottom: 1px solid #f4f4f4; padding-bottom: 10px; }
        .customer-info i { width: 20px; color: #088178; }

        .item-list { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .item-list td { padding: 10px 0; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        .item-img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; margin-right: 15px; }
        .item-name { font-weight: 500; }
        
        .order-footer { background: #fff; padding: 15px 20px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .total-price { font-size: 18px; color: #d35400; font-weight: bold; }
        
        /* Status Badge */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .st-pending { background: #fff3cd; color: #856404; }
        .st-completed { background: #d4edda; color: #155724; }
        .st-shipped { background: #cce5ff; color: #004085; }
        .st-cancelled { background: #f8d7da; color: #721c24; }
        .st-paid { background: #d1ecf1; color: #0c5460; }

        /* Form Update Status */
        .status-form { display: flex; gap: 10px; align-items: center; }
        .status-select { padding: 5px; border-radius: 4px; border: 1px solid #ddd; font-size: 13px; }
        .btn-update { padding: 5px 10px; background: #088178; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-update:hover { background: #066e67; }

    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><i class="fas fa-shopping-bag"></i> <h2>Kênh Người Bán</h2></div>
        <div class="user-profile">
            <h4><?= htmlspecialchars($shop_name) ?></h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            
            <li><a href="ProductPage/products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="ProductPage/add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>
            
            <li><a href="orders.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Đơn hàng</a></li>
            
            <li style="border-top: 1px solid #eee; margin-top: 20px;">
                <a href="../../index.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Quản lý Đơn hàng</h2>
        </div>

        <?php if($result->num_rows > 0): ?>
            <?php while($order = $result->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <span class="order-id">Đơn hàng #<?= $order['oid'] ?></span>
                            <span class="order-date"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                        </div>
                        <span class="badge st-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                    </div>
                    
                    <div class="order-body">
                        <div class="customer-info">
                            <i class="fas fa-user"></i> Khách hàng: <b><?= htmlspecialchars($order['username']) ?></b> &nbsp;|&nbsp; 
                            <i class="fas fa-phone"></i> SĐT: <?= htmlspecialchars($order['phone']) ?>
                        </div>

                        <table class="item-list">
                            <?php 
                                // Lấy chi tiết sản phẩm TRONG ĐƠN HÀNG NÀY mà thuộc về SHOP NÀY
                                $oid = $order['oid'];
                                $sql_items = "SELECT oi.*, p.name, p.main_image 
                                              FROM order_items oi 
                                              JOIN products p ON oi.pid = p.pid 
                                              WHERE oi.oid = $oid AND p.sid = $sid";
                                $items = $conn->query($sql_items);
                                while($item = $items->fetch_assoc()):
                            ?>
                            <tr>
                                <td style="width: 70px;">
                                    <img src="<?= !empty($item['main_image']) ? '/LaiRaiShop'.$item['main_image'] : 'https://via.placeholder.com/50' ?>" class="item-img">
                                </td>
                                <td>
                                    <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <small style="color: #888;">Đơn giá: <?= number_format($item['price']) ?>đ</small>
                                </td>
                                <td style="text-align: right; font-weight: bold;">x<?= $item['quantity'] ?></td>
                                <td style="text-align: right; width: 120px;">
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
                                    <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>Pending (Chờ duyệt)</option>
                                    <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>Shipped (Đang giao)</option>
                                    <option value="completed" <?= $order['status']=='completed'?'selected':'' ?>>Completed (Hoàn thành)</option>
                                    <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>Cancelled (Hủy)</option>
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
                <p>Shop chưa có đơn hàng nào.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>