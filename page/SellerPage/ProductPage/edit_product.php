<?php 
$session_path = $_SERVER['DOCUMENT_ROOT'] . '/LaiRaiShop/page/SellerPage/types/seller_session.php';
if (file_exists($session_path)) require_once $session_path;

$sid = $_SESSION['shop_id'];
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
    
    // Logic ảnh: Nếu có upload ảnh mới thì cập nhật, không thì giữ nguyên
    $db_image_path = $product['main_image']; // Mặc định giữ ảnh cũ

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
        /* CSS dùng chung form */
        body { background-color: #f4f6f9; font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; }
        .form-container { background: white; padding: 30px; border-radius: 8px; width: 600px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #f39c12; text-align: center; margin-bottom: 20px; }
        input, select, textarea { width: 100%; padding: 10px; margin: 8px 0 20px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; background: #f39c12; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #e67e22; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Sửa Sản Phẩm #<?= $pid ?></h2>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Tên sản phẩm:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label>Danh mục:</label>
        <select name="category" required>
            <?php while($c = $cats->fetch_assoc()): ?>
                <option value="<?= $c['cid'] ?>" <?= ($c['cid'] == $product['cid']) ? 'selected' : '' ?>>
                    <?= $c['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <div style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>Giá bán:</label>
                <input type="number" name="price" value="<?= $product['price'] ?>" required>
            </div>
            <div style="flex: 1;">
                <label>Kho:</label>
                <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
            </div>
        </div>

        <label>Mô tả:</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>

        <label>Ảnh hiện tại:</label><br>
        <img src="/LaiRaiShop<?= $product['main_image'] ?>" width="100" style="margin-bottom: 10px; border-radius: 5px;"><br>
        <label>Chọn ảnh mới (Nếu muốn thay đổi):</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" name="submit_edit">Cập nhật thay đổi</button>
        <a href="products.php" class="back-link">Quay lại</a>
    </form>
</div>

</body>
</html>