<?php
// FILE: page/SellerPage/ProductPage/products.php

// 1. KẾT NỐI SESSION VÀ CONFIG
// Dùng __DIR__ để định vị file chính xác
$session_path = __DIR__ . '/../types/seller_session.php';
$config_path  = __DIR__ . '/../../../config.php'; // Thêm config để dùng BASE_URL và getImage

if (file_exists($session_path)) {
    require_once $session_path;
} else {
    // Fallback tìm lùi (phòng hờ)
    $session_path_alt = __DIR__ . '/../../types/seller_session.php';
    if (file_exists($session_path_alt)) require_once $session_path_alt;
    else die("Lỗi: Không tìm thấy file session.");
}

if (file_exists($config_path)) {
    require_once $config_path;
}

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];

// --- XỬ LÝ CẬP NHẬT THÔNG TIN SHOP ---
if (isset($_POST['update_shop_info'])) {
    $new_name = $_POST['shop_name'];
    $new_desc = $_POST['shop_desc'];

    $stmt = $conn->prepare("UPDATE shops SET shop_name = ?, description = ? WHERE sid = ?");
    $stmt->bind_param("ssi", $new_name, $new_desc, $sid);

    if ($stmt->execute()) {
        $_SESSION['shop_name'] = $new_name;
        $shop_name = $new_name;
        echo "<script>alert('Cập nhật thông tin Shop thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi cập nhật: " . $conn->error . "');</script>";
    }
}

// --- LẤY THÔNG TIN CHI TIẾT SHOP ---
$sql_shop_info = "SELECT * FROM shops WHERE sid = $sid";
$res_info = $conn->query($sql_shop_info);
$current_shop = $res_info->fetch_assoc();

// 2. LẤY DANH SÁCH SẢN PHẨM
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
        /* --- CSS STYLE --- */
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
            color: #135E4B;
        }

        .sidebar-header h2 {
            font-size: 20px;
            color: #135E4B;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .btn-add {
            background: #135E4B;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-add:hover {
            background: #0f4a3b;
            color: white;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        th {
            background: #f8f9fa;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #eee;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            margin-right: 5px;
            transition: 0.2s;
        }

        .btn-edit {
            background: #f39c12;
        }

        .btn-delete {
            background: #e74c3c;
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

        .st-approved {
            background: #d4edda;
            color: #155724;
        }

        .st-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .st-hidden {
            background: #e2e3e5;
            color: #383d41;
        }

        /* Modal */
        .btn-edit-shop {
            margin-top: 10px;
            font-size: 12px;
            color: #135E4B;
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
            width: 500px;
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
            box-sizing: border-box;
        }

        .btn-save {
            width: 100%;
            padding: 10px;
            background: #135E4B;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
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
            <button onclick="openShopModal()" class="btn-edit-shop"><i class="fas fa-pen"></i> Sửa thông tin</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>

            <li><a href="../orders.php"><i class="fas fa-clipboard-list"></i> Đơn cần xử lý</a></li>
            <li><a href="../orders_history.php"><i class="fas fa-history"></i> Lịch sử đơn hàng</a></li>

            <li style="border-top: 1px solid #eee; margin-top: 20px;">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/LaiRaiShop'; ?>/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
            </li>
            <li><a href="../../HomePage/LoginPage/logout.php" onclick="return confirm('Bạn muốn đăng xuất?');" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
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
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            // --- XỬ LÝ HIỂN THỊ ẢNH (FIX LỖI LOAD TRANG & ẢNH TRẮNG) ---
                            $imgSrc = $row['main_image'];

                            // Sử dụng hàm getImage từ config (nếu có)
                            if (function_exists('getImage')) {
                                $displayImg = getImage($imgSrc);
                            } else {
                                // Fallback
                                if (empty($imgSrc)) {
                                    $displayImg = 'https://via.placeholder.com/50?text=No+Img';
                                } elseif (strpos($imgSrc, 'http') === 0) {
                                    $displayImg = $imgSrc;
                                } else {
                                    $base = defined('BASE_URL') ? BASE_URL : '/LaiRaiShop';
                                    $displayImg = $base . '/' . ltrim($imgSrc, '/');
                                }
                            }
                            ?>
                            <tr>
                                <td>#<?= $row['pid'] ?></td>
                                <td>
                                    <img src="<?= $displayImg ?>" class="product-img"
                                        style="background-color: #f0f0f0; border: 1px solid #ddd;"
                                        loading="lazy"
                                        onerror="this.src='https://via.placeholder.com/50?text=Error'">
                                </td>
                                <td style="font-weight: 500;">
                                    <?= htmlspecialchars($row['name']) ?>
                                </td>
                                <td style="color: #555; font-size: 14px;">
                                    <?= $row['cat_name'] ?? 'Chưa phân loại' ?>
                                </td>
                                <td style="color: #088178; font-weight: bold;"><?= number_format($row['price'], 0, ',', '.') ?>đ</td>
                                <td><?= $row['stock'] ?></td>

                                <td>
                                    <?php
                                    $st = $row['status'];
                                    $st_class = match ($st) {
                                        'pending' => 'st-pending',
                                        'approved' => 'st-approved',
                                        'rejected' => 'st-rejected',
                                        default => 'st-hidden'
                                    };
                                    ?>
                                    <span class="badge <?= $st_class ?>"><?= ucfirst($st) ?></span>
                                </td>

                                <td>
                                    <a href="edit_product.php?id=<?= $row['pid'] ?>" class="btn-action btn-edit"><i class="fas fa-pen"></i></a>
                                    <a href="delete_product.php?id=<?= $row['pid'] ?>" class="btn-action btn-delete" onclick="return confirm('Xóa sản phẩm này?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px;">Chưa có sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="shopModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeShopModal()">&times;</span>
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
        function openShopModal() {
            document.getElementById("shopModal").style.display = "flex";
        }

        function closeShopModal() {
            document.getElementById("shopModal").style.display = "none";
        }
        window.onclick = function(event) {
            var m1 = document.getElementById("shopModal");
            if (event.target == m1) m1.style.display = "none";
        }
    </script>

</body>

</html>