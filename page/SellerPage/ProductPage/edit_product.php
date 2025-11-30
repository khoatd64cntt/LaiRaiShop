<?php 
// FILE: page/SellerPage/ProductPage/edit_product.php

// 1. KẾT NỐI SESSION
$session_path = __DIR__ . '/../types/seller_session.php';
if (file_exists($session_path)) require_once $session_path;
else die("Lỗi: Không tìm thấy file session.");

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name']; // Lấy tên shop cho sidebar
$pid = $_GET['id'];

// Lấy thông tin sản phẩm (Chỉ lấy nếu đúng là của shop này)
$query = $conn->prepare("SELECT * FROM products WHERE pid = ? AND sid = ?");
$query->bind_param("ii", $pid, $sid);
$query->execute();
$product = $query->get_result()->fetch_assoc();

if (!$product) { die("Sản phẩm không tồn tại hoặc bạn không có quyền sửa."); }

// Lấy danh mục
$cats = $conn->query("SELECT * FROM categories");

// XỬ LÝ CẬP NHẬT
if (isset($_POST['submit_edit'])) {
    $name = $_POST['name'];
    $cid = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];
    
    // Logic ảnh: Giữ ảnh cũ mặc định
    $db_image_path = $product['main_image']; 

    if (!empty($_FILES["image"]["name"])) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/LaiRaiShop/images/products/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $new_filename = time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_filename)) {
            $db_image_path = "/images/products/" . $new_filename;
        }
    }

    // Update SQL
    $stmt = $conn->prepare("UPDATE products SET cid=?, name=?, price=?, stock=?, description=?, main_image=? WHERE pid=? AND sid=?");
    $stmt->bind_param("isdissii", $cid, $name, $price, $stock, $desc, $db_image_path, $pid, $sid);
    
    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Lỗi cập nhật');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        /* CSS Đồng bộ với add_product.php */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        a { text-decoration: none; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #e1e1e1; position: fixed; height: 100%; top: 0; left: 0; z-index: 100; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
        .sidebar-header i { font-size: 24px; color: #088178; }
        .sidebar-header h2 { font-size: 20px; color: #088178; font-weight: 700; }
        
        .user-profile { padding: 20px; text-align: center; background: #f9f9f9; border-bottom: 1px solid #eee; }
        .user-profile img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #088178; }
        .user-profile h4 { font-size: 16px; margin-bottom: 5px; }
        
        .sidebar-menu { list-style: none; padding: 10px 0; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: #555; font-weight: 500; transition: 0.3s; font-size: 15px; border-left: 4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #e8f6ea; color: #088178; border-left-color: #088178; }
        .sidebar-menu li a i { margin-right: 15px; width: 20px; text-align: center; font-size: 16px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        
        /* Form Style */
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 700px; margin: 0 auto; }
        h2 { color: #f39c12; text-align: center; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .form-row { display: flex; gap: 20px; }
        .form-col { flex: 1; }
        
        .btn-save { width: 100%; padding: 12px; background: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 10px; }
        .btn-save:hover { background: #e67e22; }
        
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; font-size: 14px; }
        .back-link:hover { color: #f39c12; text-decoration: underline; }
        
        .current-img { border: 1px solid #ddd; padding: 5px; border-radius: 5px; margin-bottom: 10px; max-width: 100px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><i class="fas fa-shopping-bag"></i> <h2>Kênh Người Bán</h2></div>
        
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($shop_name) ?>&background=088178&color=fff" alt="Shop Logo">
            <h4><?= htmlspecialchars($shop_name) ?></h4>
            <p>ID Shop: #<?= $sid ?></p>
        </div>

        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>
            <li><a href="../orders.php"><i class="fas fa-file-invoice-dollar"></i> Đơn hàng</a></li>
            <li style="border-top: 1px solid #eee; margin-top: 20px;">
                <a href="/LaiRaiShop/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
            </li>
            <li>
                <a href="../HomePage/LoginPage/logout.php" onclick="return confirm('Bạn muốn đăng xuất?');" style="color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h2>Sửa Sản Phẩm #<?= $pid ?></h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Danh mục:</label>
                    <select name="category" required>
                        <?php while($c = $cats->fetch_assoc()): ?>
                            <option value="<?= $c['cid'] ?>" <?= ($c['cid'] == $product['cid']) ? 'selected' : '' ?>>
                                <?= $c['name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>Giá bán (VNĐ):</label>
                            <input type="number" name="price" value="<?= $product['price'] ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>Số lượng kho:</label>
                            <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết:</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ảnh hiện tại:</label><br>
                    <?php 
                        // --- LOGIC XỬ LÝ ẢNH (GIỐNG products.php) ---
                        $imgSrc = $product['main_image'];
                        if (empty($imgSrc)) {
                            $displayImg = 'https://via.placeholder.com/100?text=No+Img';
                        } elseif (strpos($imgSrc, 'http') !== false) { // Link online (có chứa http)
                            // Nếu có dấu / ở đầu thì xóa đi
                            $cleanPath = ltrim($imgSrc, '/');
                            if (strpos($cleanPath, 'http') === 0) {
                                $displayImg = $cleanPath;
                            } else {
                                // Trường hợp lạ, cứ để nguyên
                                $displayImg = $imgSrc;
                            }
                        } else {
                            // Link local -> thêm /LaiRaiShop
                            if (strpos($imgSrc, '/') !== 0) $imgSrc = '/' . $imgSrc;
                            $displayImg = '/LaiRaiShop' . $imgSrc;
                        }
                    ?>
                    <img src="<?= $displayImg ?>" class="current-img" onerror="this.src='https://via.placeholder.com/100?text=Error'">
                    
                    <label style="margin-top: 10px;">Chọn ảnh mới (Nếu muốn thay đổi):</label>
                    <input type="file" name="image" accept="image/*" style="border: none; padding-left: 0;">
                </div>

                <button type="submit" name="submit_edit" class="btn-save">Cập nhật thay đổi</button>
                <a href="products.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
            </form>
        </div>
    </div>

</body>
</html>