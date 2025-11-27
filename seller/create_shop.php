<?php
// Bật hiển thị lỗi để dễ debug nếu có sự cố
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gọi file session (file này đã bao gồm kết nối db và check login)
require_once 'seller_session.php';

$message = "";
$error = "";
///rsxgfhcvbj
// Xử lý khi người dùng nhấn nút Đăng Ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shop_name = trim($_POST['shop_name']); // trim để cắt khoảng trắng thừa
    $description = trim($_POST['description']);

    if (!empty($shop_name)) {
        // Kiểm tra xem tên shop đã tồn tại chưa (Optional)
        $check = $conn->query("SELECT * FROM shops WHERE shop_name = '$shop_name'");
        if($check->num_rows > 0){
             $error = "Tên Shop này đã được sử dụng, vui lòng chọn tên khác!";
        } else {
             // Thêm shop mới
            $stmt = $conn->prepare("INSERT INTO shops (aid, shop_name, description) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $aid, $shop_name, $description);
            
            if ($stmt->execute()) {
                // Cập nhật quyền seller cho user
                $conn->query("UPDATE acc SET role = 'seller' WHERE aid = $aid");
                
                // Cập nhật lại session để nhận diện ngay là seller
                $_SESSION['shop_id'] = $conn->insert_id;
                $_SESSION['shop_name'] = $shop_name;
                
                // Chuyển hướng về dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Lỗi hệ thống: " . $conn->error;
            }
        }
    } else {
        $error = "Tên Shop không được để trống!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Kênh Người Bán | LaiRaiShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f5f5f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .register-box { 
            width: 100%; 
            max-width: 500px; 
            background: white; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .btn-primary { background-color: #ee4d2d; border-color: #ee4d2d; }
        .btn-primary:hover { background-color: #d73211; }
        .brand-logo { text-align: center; margin-bottom: 20px; color: #ee4d2d; font-weight: bold; font-size: 24px;}
    </style>
</head>
<body>

    <div class="register-box">
        <div class="brand-logo"><i class="fas fa-shopping-bag"></i> LaiRaiShop Seller</div>
        <h4 class="text-center mb-4">Đăng ký mở Shop</h4>
        <p class="text-center text-muted mb-4">Bắt đầu hành trình kinh doanh của bạn ngay hôm nay!</p>
        
        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Tên Shop <span class="text-danger">*</span></label>
                <input type="text" name="shop_name" class="form-control" placeholder="Nhập tên cửa hàng của bạn..." required>
            </div>
            
            <div class="form-group">
                <label>Mô tả Shop</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Giới thiệu đôi chút về shop (VD: Chuyên đồ điện tử chính hãng...)"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
                Đăng Ký Ngay
            </button>
            
            <div class="text-center mt-3">
                <a href="../homepage.php" class="text-secondary"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>
            </div>
        </form>
    </div>

</body>
</html>