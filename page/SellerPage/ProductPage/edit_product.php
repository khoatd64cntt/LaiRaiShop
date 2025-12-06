<?php
// FILE: page/SellerPage/ProductPage/edit_product.php

// --- 1. BẬT HIỂN THỊ LỖI (Để debug nếu có sự cố) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 2. KẾT NỐI SESSION (Dùng đường dẫn tuyệt đối) ---
// Thay đổi đường dẫn này nếu thư mục dự án của bạn không phải là 'LaiRaiShop'
$session_path = $_SERVER['DOCUMENT_ROOT'] . '/LaiRaiShop/page/SellerPage/types/seller_session.php';

if (file_exists($session_path)) {
    require_once $session_path;
} else {
    // Fallback: Tìm file ở thư mục cha (phòng trường hợp cấu trúc thư mục khác)
    $alt_path = __DIR__ . '/../types/seller_session.php';
    if(file_exists($alt_path)) require_once $alt_path;
    else die("Lỗi: Không tìm thấy file 'seller_session.php'. <br>Đường dẫn tìm kiếm: " . $session_path);
}

// Kiểm tra biến kết nối CSDL từ session
if (!isset($conn)) {
    die("Lỗi: Không có kết nối Cơ sở dữ liệu ($conn). Kiểm tra lại file seller_session.php");
}

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- 3. LẤY THÔNG TIN SẢN PHẨM ---
// Chỉ lấy nếu đúng là sản phẩm của shop này (sid khớp)
$query = $conn->prepare("SELECT * FROM products WHERE pid = ? AND sid = ?");
$query->bind_param("ii", $pid, $sid);
$query->execute();
$product = $query->get_result()->fetch_assoc();

if (!$product) {
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>Lỗi: Sản phẩm không tồn tại hoặc không thuộc quyền quản lý của bạn.</h3> <a href='products.php' style='display:block; text-align:center;'>Quay lại</a>");
}

// Lấy danh mục
$cats = $conn->query("SELECT * FROM categories");

// --- 4. XỬ LÝ CẬP NHẬT ---
if (isset($_POST['submit_edit'])) {
    $name = $_POST['name'];
    $cid = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];
    
    // Logic ảnh: Mặc định giữ nguyên ảnh cũ
    $db_image_path = $product['main_image']; 

    // Nếu có upload ảnh mới
    if (!empty($_FILES["image"]["name"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/LaiRaiShop/images/products/";
        
        // Tạo thư mục nếu chưa có
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $new_filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Cập nhật đường dẫn mới
            $db_image_path = "/images/products/" . $new_filename;
        } else {
            echo "<script>alert('Lỗi: Không thể tải ảnh lên thư mục!');</script>";
        }
    }

    // Update SQL
    $stmt = $conn->prepare("UPDATE products SET cid=?, name=?, price=?, stock=?, description=?, main_image=? WHERE pid=? AND sid=?");
    $stmt->bind_param("isdissii", $cid, $name, $price, $stock, $desc, $db_image_path, $pid, $sid);
    
    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Lỗi cập nhật SQL: " . $stmt->error . "');</script>";
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
        /* CSS Đồng bộ */
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
        
        .sidebar-menu { list-style: none; padding: 10px 0; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: #555; font-weight: 500; transition: 0.3s; font-size: 15px; border-left: 4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #e8f6ea; color: #088178; border-left-color: #088178; }
        .sidebar-menu li a i { margin-right: 15px; width: 20px; text-align: center; font-size: 16px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        
        /* Form Style */
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 700px; margin: 0 auto; }
        h2 { color: #135E4B; text-align: center; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .form-row { display: flex; gap: 20px; }
        .form-col { flex: 1; }
        
        .btn-save { width: 100%; padding: 12px; background: #135E4B; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 10px; }
        .btn-save:hover { background: #0f4a3b; }
        
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; font-size: 14px; }
        .back-link:hover { color: #135E4B; text-decoration: underline; }
        
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
            <li><a href="../orders.php"><i class="fas fa-file-invoice-dollar"></i> Đơn cần xử lý</a></li>
            <li><a href="../orders_history.php"><i class="fas fa-history"></i> Lịch sử đơn hàng</a></li>
            <li style="border-top: 1px solid #eee; margin-top: 20px;">
                <a href="/LaiRaiShop/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
            </li>
            <li>
                <a href="../../HomePage/LoginPage/logout.php" onclick="return confirm('Bạn muốn đăng xuất?');" style="color: #e74c3c;">
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
                        // --- XỬ LÝ ẢNH THÔNG MINH ---
                        $imgSrc = $product['main_image'];
                        $displayImg = 'https://via.placeholder.com/100?text=No+Img';

                        if (!empty($imgSrc)) {
                            // Link Online
                            if (strpos($imgSrc, 'http') === 0) {
                                $displayImg = $imgSrc;
                            } 
                            // Link Local
                            elseif (strpos($imgSrc, '/') === 0 || strpos($imgSrc, 'images/') !== false) {
                                $base = '/LaiRaiShop'; // Đường dẫn gốc mặc định
                                $displayImg = $base . '/' . ltrim($imgSrc, '/');
                            }
                            // BLOB (Base64) - Nếu bạn dùng cách lưu Blob trước đó
                            else {
                                $base64 = base64_encode($imgSrc);
                                $displayImg = 'data:image/jpeg;base64,' . $base64;
                            }
                        }
                    ?>
                    <img src="<?= $displayImg ?>" class="current-img" onerror="this.src='https://via.placeholder.com/100?text=Error'">
                    
                    <label style="margin-top: 10px; display:block; font-weight:normal;">Chọn ảnh mới (Nếu muốn thay đổi):</label>
                    <input type="file" name="image" accept="image/*" style="border: none; padding-left: 0;">
                </div>

                <button type="submit" name="submit_edit" class="btn-save">Cập nhật thay đổi</button>
                <a href="products.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
            </form>
        </div>
    </div>

</body>
</html>