<?php 
// 1. KẾT NỐI SESSION
$session_path = $_SERVER['DOCUMENT_ROOT'] . '/LaiRaiShop/page/SellerPage/types/seller_session.php';
if (file_exists($session_path)) require_once $session_path;
else die("Lỗi: Không tìm thấy file session.");

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];

// 2. LẤY DANH SÁCH SẢN PHẨM (Kèm tên danh mục)
$sql = "SELECT p.*, c.name as cat_name 
        FROM products p 
        LEFT JOIN categories c ON p.cid = c.cid 
        WHERE p.sid = $sid 
        ORDER BY p.pid DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        /* --- COPY CSS TỪ DASHBOARD ĐỂ ĐỒNG BỘ --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        a { text-decoration: none; }
        
        /* Sidebar Styles */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #e1e1e1; position: fixed; height: 100%; top: 0; left: 0; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
        .sidebar-header h2 { font-size: 20px; color: #088178; font-weight: 700; }
        .user-profile { padding: 20px; text-align: center; background: #f9f9f9; border-bottom: 1px solid #eee; }
        .sidebar-menu { list-style: none; padding: 10px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 12px 25px; color: #555; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e8f6ea; color: #088178; border-left: 4px solid #088178; }
        .sidebar-menu i { margin-right: 15px; width: 20px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Nút thêm mới */
        .btn-add { background: #088178; color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold; }
        .btn-add:hover { background: #066e67; }

        /* Table Styles */
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background: #f8f9fa; color: #555; text-transform: uppercase; font-size: 13px; }
        
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #eee; }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 11px; font-weight: bold; }
        .st-active { background: #d4edda; color: #155724; }
        .st-pending { background: #fff3cd; color: #856404; }

        .btn-action { padding: 6px 10px; border-radius: 4px; color: white; font-size: 12px; margin-right: 5px; }
        .btn-edit { background: #f39c12; }
        .btn-delete { background: #e74c3c; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><i class="fas fa-shopping-bag"></i> <h2>Kênh Người Bán</h2></div>
        <div class="user-profile">
            <h4><?= htmlspecialchars($shop_name) ?></h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>
            <li><a href="../orders.php"><i class="fas fa-file-invoice-dollar"></i> Đơn hàng</a></li>
            <li style="border-top: 1px solid #eee; margin-top: 20px;"><a href="/LaiRaiShop/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Danh sách sản phẩm</h2>
            <a href="add_product.php" class="btn-add"><i class="fas fa-plus"></i> Thêm sản phẩm</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá bán</th>
                        <th>Kho</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['pid'] ?></td>
                            <td>
                                <img src="<?= !empty($row['main_image']) ? '/LaiRaiShop' . $row['main_image'] : 'https://via.placeholder.com/50' ?>" class="product-img">
                            </td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($row['name']) ?></td>
                            <td style="color: #666; font-size: 14px;"><?= $row['cat_name'] ?? 'Chưa phân loại' ?></td>
                            <td style="color: #088178; font-weight: bold;"><?= number_format($row['price'], 0, ',', '.') ?>đ</td>
                            <td><?= $row['stock'] ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $row['pid'] ?>" class="btn-action btn-edit"><i class="fas fa-pen"></i></a>
                                <a href="delete_product.php?id=<?= $row['pid'] ?>" class="btn-action btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; padding: 30px;">Chưa có sản phẩm nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>