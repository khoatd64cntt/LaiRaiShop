<?php 
// FILE: page/SellerPage/ProductPage/view_product.php

// 1. KẾT NỐI SESSION
$session_path = __DIR__ . '/../types/seller_session.php';
$config_path  = __DIR__ . '/../../../config.php'; // Đường dẫn tới file config gốc

// Load Session
if (file_exists($session_path)) {
    require_once $session_path;
} else {
    // Fallback tìm lùi
    $session_path_alt = $_SERVER['DOCUMENT_ROOT'] . '/LaiRaiShop/page/SellerPage/types/seller_session.php';
    if(file_exists($session_path_alt)) require_once $session_path_alt;
    else die("Error: Session file not found.");
}

// Load Config
if (file_exists($config_path)) require_once $config_path;
if (!defined('BASE_URL')) define('BASE_URL', '/LaiRaiShop');

$sid = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'];
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. LẤY THÔNG TIN SẢN PHẨM CHI TIẾT
$sql = "SELECT p.*, c.name as cat_name 
        FROM products p 
        LEFT JOIN categories c ON p.cid = c.cid 
        WHERE p.pid = ? AND p.sid = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $pid, $sid);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Sản phẩm không tồn tại hoặc không thuộc quyền quản lý của bạn.");
}

// Xử lý hiển thị ảnh (Logic thông minh như products.php)
$imgSrc = $product['main_image'];
$displayImg = 'https://via.placeholder.com/300?text=No+Img';

if (!empty($imgSrc)) {
    if (strpos($imgSrc, 'http') === 0) {
        $displayImg = $imgSrc;
    } elseif (strpos($imgSrc, '/') === 0 || strpos($imgSrc, 'images/') !== false) {
        $displayImg = BASE_URL . '/' . ltrim($imgSrc, '/');
    } else {
        $base64 = base64_encode($imgSrc);
        $displayImg = 'data:image/jpeg;base64,' . $base64;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm: <?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        /* --- CSS STYLE ĐỒNG BỘ 100% VỚI DASHBOARD --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: #f4f6f9; color: #333; }
        a { text-decoration: none; }
        
        /* SIDEBAR (Copy chuẩn từ Dashboard) */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #e1e1e1; position: fixed; height: 100%; top: 0; left: 0; z-index: 100; display: flex; flex-direction: column; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
        .sidebar-header i { font-size: 24px; color: #135E4B; }
        .sidebar-header h2 { font-size: 20px; color: #135E4B; font-weight: 700; }
        
        .user-profile { padding: 20px; text-align: center; background: #f9f9f9; border-bottom: 1px solid #eee; }
        .user-profile img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #088178; }
        .user-profile h4 { font-size: 16px; margin-bottom: 5px; }
        .user-profile p { font-size: 12px; color: #777; }
        
        .sidebar-menu { list-style: none; padding: 10px 0; flex: 1; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 25px; color: #555; font-weight: 500; transition: 0.3s; font-size: 15px; border-left: 4px solid transparent; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #e8f6ea; color: #135E4B; border-left-color: #135E4B; }
        .sidebar-menu li a i { margin-right: 15px; width: 20px; text-align: center; font-size: 16px; }

        /* MAIN CONTENT */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { font-size: 24px; color: #333; margin-bottom: 5px; }

        /* DETAIL CARD STYLE */
        .detail-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; overflow: hidden; display: flex; flex-wrap: wrap; }
        .detail-img-col { width: 40%; padding: 30px; background: #fcfcfc; display: flex; align-items: center; justify-content: center; border-right: 1px solid #eee; }
        .detail-img { max-width: 100%; max-height: 400px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .detail-info-col { width: 60%; padding: 40px; }

        .info-group { margin-bottom: 25px; }
        .info-label { font-size: 13px; color: #888; text-transform: uppercase; font-weight: 700; margin-bottom: 8px; letter-spacing: 0.5px; }
        .info-value { font-size: 16px; color: #333; font-weight: 500; line-height: 1.6; }
        
        .product-name { font-size: 26px; font-weight: 700; color: #135E4B; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .price-tag { font-size: 28px; color: #d35400; font-weight: 700; }
        
        /* Status Badge */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; display: inline-block; }
        .st-pending { background: #fff3cd; color: #856404; }
        .st-approved { background: #d4edda; color: #155724; }
        .st-rejected { background: #f8d7da; color: #721c24; }
        .st-hidden { background: #e2e3e5; color: #383d41; }

        /* Buttons */
        .btn-group { margin-top: 40px; display: flex; gap: 15px; }
        .btn-edit { background: #f39c12; color: white; padding: 10px 25px; border-radius: 5px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-back { background: #e2e6ea; color: #555; padding: 10px 25px; border-radius: 5px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-edit:hover { background: #e67e22; transform: translateY(-2px); }
        .btn-back:hover { background: #dbe2e8; color: #333; }
        
        /* Modal Sidebar Button (Disabled here but kept for UI consistency) */
        .btn-edit-shop { margin-top: 10px; font-size: 12px; color: #135E4B; cursor: pointer; text-decoration: underline; border: none; background: none; opacity: 0.7; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><i class="fas fa-shopping-bag"></i> <h2>Kênh Người Bán</h2></div>
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($shop_name) ?>&background=088178&color=fff" alt="Shop Logo">
            <h4><?= htmlspecialchars($shop_name) ?></h4>
            <p>ID Shop: #<?= $sid ?></p>
            <button class="btn-edit-shop">Sửa thông tin</button>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php"><i class="fas fa-th-large"></i> Tổng quan</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Thêm mới</a></li>
            <li><a href="../orders.php"><i class="fas fa-file-invoice-dollar"></i> Đơn cần xử lý</a></li>
            <li><a href="../orders_history.php"><i class="fas fa-history"></i> Lịch sử đơn hàng</a></li>
            <li style="border-top: 1px solid #eee; margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>/page/HomePage/homepage.php"><i class="fas fa-home"></i> Xem Shop (Client)</a>
            </li>
            <li>
                <a href="../HomePage/LoginPage/logout.php" style="color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>Chi tiết sản phẩm</h2>
            <a href="products.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>

        <div class="detail-card">
            <div class="detail-img-col">
                <img src="<?= $displayImg ?>" class="detail-img" onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
            </div>

            <div class="detail-info-col">
                <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                
                <div class="info-group">
                    <div class="info-label">Trạng thái</div>
                    <div class="info-value">
                        <?php 
                            $st = $product['status'];
                            $st_class = 'st-hidden';
                            if($st == 'pending') $st_class = 'st-pending';
                            elseif($st == 'approved') $st_class = 'st-approved';
                            elseif($st == 'rejected') $st_class = 'st-rejected';
                        ?>
                        <span class="badge <?= $st_class ?>"><?= ucfirst($st) ?></span>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Danh mục</div>
                    <div class="info-value"><?= $product['cat_name'] ?? 'Chưa phân loại' ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">Giá bán</div>
                    <div class="info-value price-tag"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                </div>

                <div class="info-group">
                    <div class="info-label">Kho hàng</div>
                    <div class="info-value">
                        <?= $product['stock'] ?> sản phẩm
                        <?php if($product['stock'] <= 5): ?>
                            <span style="color: #e74c3c; font-size: 13px; font-weight: normal;">(Sắp hết hàng)</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Mô tả</div>
                    <div class="info-value" style="white-space: pre-line; color: #555;">
                        <?= !empty($product['description']) ? htmlspecialchars($product['description']) : "Chưa có mô tả." ?>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="edit_product.php?id=<?= $pid ?>" class="btn-edit"><i class="fas fa-pen"></i> Chỉnh Sửa</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>