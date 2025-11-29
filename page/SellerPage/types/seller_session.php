<?php
// seller/seller_session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối CSDL (Đường dẫn tuyệt đối - Đã chuẩn)
require_once($_SERVER['DOCUMENT_ROOT'] . '/LaiRaiShop/db/db.php'); 

// 1. Kiểm tra đăng nhập
// SỬA LỖI: Đổi 'user_id' thành 'aid' (vì bên login lưu là aid)
if (!isset($_SESSION['aid'])) {
    // Nếu chưa đăng nhập, đá về trang Login
    header("Location: /LaiRaiShop/page/HomePage/LoginPage/login.php");
    exit();
}

// Lấy ID từ session
$aid = $_SESSION['aid'];

// 2. Kiểm tra xem user đã có Shop chưa
$current_page = basename($_SERVER['PHP_SELF']);

// Chỉ kiểm tra nếu KHÔNG PHẢI là trang tạo shop (để tránh vòng lặp vô tận)
if ($current_page != 'create_shop.php') {
    $sql_check_shop = "SELECT * FROM shops WHERE aid = $aid"; // shop liên kết với aid
    $res_check = $conn->query($sql_check_shop);

    if ($res_check->num_rows == 0) {
        // Chưa có shop -> Đá sang trang tạo shop
        // Lưu ý: Dùng đường dẫn tuyệt đối để tránh lỗi file nằm ở thư mục con khác nhau
        header("Location: /LaiRaiShop/page/SellerPage/CreateSellerPage/create_shop.php");
        exit();
    } else {
        // Đã có shop -> Lưu thông tin shop vào session
        $shop_data = $res_check->fetch_assoc();
        $_SESSION['shop_id'] = $shop_data['sid']; // Giả sử cột ID shop là sid
        $_SESSION['shop_name'] = $shop_data['shop_name'];
    }
}
?>