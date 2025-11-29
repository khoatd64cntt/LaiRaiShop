<?php 
// FILE: page/SellerPage/ProductPage/add_product.php

// 1. KẾT NỐI SESSION (SỬA PATH)
$session_path = __DIR__ . '/../types/seller_session.php';
if (file_exists($session_path)) require_once $session_path;
else die("Lỗi: Không tìm thấy file session.");

$sid = $_SESSION['shop_id'];
$msg = "";

// Lấy danh mục để hiển thị ra dropdown
$cats = $conn->query("SELECT * FROM categories");

// XỬ LÝ KHI BẤM NÚT LƯU
if (isset($_POST['submit_add'])) {
    $name = $_POST['name'];
    $cid = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];
    
    // 2. XỬ LÝ UPLOAD ẢNH (SỬA ĐƯỜNG DẪN)
    // Dùng __DIR__ để đi ra thư mục gốc images/products
    $target_dir = __DIR__ . "/../../../images/products/"; 
    
    // Tạo thư mục nếu chưa có
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $filename = basename($_FILES["image"]["name"]);
    // Đổi tên file để tránh trùng: time_tên_ảnh
    $new_filename = time() . "_" . $filename; 
    $target_file = $target_dir . $new_filename;
    
    // Đường dẫn lưu vào DB (Dạng tương đối để web load được)
    $db_image_path = "/images/products/" . $new_filename; 

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // SQL Insert (Dùng Prepared Statement cho an toàn)
        $stmt = $conn->prepare("INSERT INTO products (sid, cid, name, price, stock, description, main_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iisdiss", $sid, $cid, $name, $price, $stock, $desc, $db_image_path);
        
        if ($stmt->execute()) {
            echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href='products.php';</script>";
        } else {
            $msg = "Lỗi SQL: " . $stmt->error;
        }
    } else {
        $msg = "Lỗi upload ảnh. Vui lòng kiểm tra lại.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; }
        .form-container { background: white; padding: 30px; border-radius: 8px; width: 600px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #088178; text-align: center; margin-bottom: 20px; }
        input, select, textarea { width: 100%; padding: 10px; margin: 8px 0 20px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; background: #088178; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #066e67; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Thêm Sản Phẩm Mới</h2>
    <?php if($msg) echo "<p style='color:red; text-align:center;'>$msg</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Tên sản phẩm:</label>
        <input type="text" name="name" required placeholder="Ví dụ: Áo thun nam...">

        <label>Danh mục:</label>
        <select name="category" required>
            <option value="">-- Chọn danh mục --</option>
            <?php while($c = $cats->fetch_assoc()): ?>
                <option value="<?= $c['cid'] ?>"><?= $c['name'] ?></option>
            <?php endwhile; ?>
        </select>

        <div style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>Giá bán (VNĐ):</label>
                <input type="number" name="price" required placeholder="500000">
            </div>
            <div style="flex: 1;">
                <label>Số lượng kho:</label>
                <input type="number" name="stock" required placeholder="100">
            </div>
        </div>

        <label>Mô tả chi tiết:</label>
        <textarea name="description" rows="4"></textarea>

        <label>Hình ảnh đại diện:</label>
        <input type="file" name="image" required accept="image/*">

        <button type="submit" name="submit_add">Lưu Sản Phẩm</button>
        <a href="products.php" class="back-link">Quay lại danh sách</a>
    </form>
</div>

</body>
</html>