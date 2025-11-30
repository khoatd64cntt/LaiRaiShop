<?php
// FILE: page/SellerPage/ProductPage/add_product.php

// 1. KẾT NỐI SESSION
$session_path = __DIR__ . '/../types/seller_session.php';
if (file_exists($session_path)) require_once $session_path;
else die("Lỗi: Không tìm thấy file session.");

$sid = $_SESSION['shop_id'];
$msg = "";

// XỬ LÝ KHI BẤM NÚT LƯU
if (isset($_POST['submit_add'])) {
    $name = $_POST['name'];
    $cid = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];

    // --- XỬ LÝ UPLOAD ẢNH ---
    // Định nghĩa thư mục lưu ảnh vật lý: D:/XAMPP/htdocs/LaiRaiShop/images/products/
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/LaiRaiShop/images/products/";

    // Tạo thư mục nếu chưa có
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = basename($_FILES["image"]["name"]);
    // Đổi tên file để tránh trùng lặp: time_tên_ảnh
    $new_filename = time() . "_" . $filename;
    $target_file = $target_dir . $new_filename;

    // Đường dẫn để lưu vào SQL (Dạng tương đối): /images/products/ten_anh.jpg
    $db_image_path = "/images/products/" . $new_filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {

        // INSERT VÀO SQL
        $stmt = $conn->prepare("INSERT INTO products (sid, cid, name, price, stock, description, main_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iisdiss", $sid, $cid, $name, $price, $stock, $desc, $db_image_path);

        if ($stmt->execute()) {
            echo "<script>alert('Thêm thành công! Sản phẩm đang chờ duyệt.'); window.location.href='products.php';</script>";
            exit();
        } else {
            $msg = "Lỗi SQL: " . $stmt->error;
        }
    } else {
        $msg = "Lỗi upload ảnh. Vui lòng kiểm tra lại file.";
    }
}

// Lấy danh mục
$cats = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            width: 600px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #135E4B;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-save {
            width: 100%;
            background: #135E4B;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn-save:hover {
            background: #0f4a3b;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            color: #135E4B;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>Thêm Sản Phẩm Mới</h2>
        <?php if ($msg) echo "<p style='color:red; text-align:center;'>$msg</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên sản phẩm:</label>
                <input type="text" name="name" required placeholder="Ví dụ: Áo thun nam...">
            </div>

            <div class="form-group">
                <label>Danh mục:</label>
                <select name="category" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php while ($c = $cats->fetch_assoc()): ?>
                        <option value="<?= $c['cid'] ?>"><?= $c['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Giá bán (VNĐ):</label>
                    <input type="number" name="price" required placeholder="500000">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Số lượng kho:</label>
                    <input type="number" name="stock" required placeholder="100">
                </div>
            </div>

            <div class="form-group">
                <label>Mô tả chi tiết:</label>
                <textarea name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label>Hình ảnh đại diện:</label>
                <input type="file" name="image" required accept="image/*" style="border: none; padding-left: 0;">
            </div>

            <button type="submit" name="submit_add" class="btn-save">Lưu Sản Phẩm</button>
            <a href="products.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        </form>
    </div>

</body>

</html>