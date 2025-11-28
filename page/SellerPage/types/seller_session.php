<?php
// seller/seller_session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ('../../db/db.php'); // Kết nối CSDL

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /LaiRaiShop/page/HomePage/LoginPage/login.php");
    exit();
}

$aid = $_SESSION['user_id'];

// 2. Kiểm tra xem user đã có Shop chưa
// Lưu ý: Không kiểm tra shop nếu đang ở trang tạo shop để tránh lặp vô tận
$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page != 'create_shop.php') {
    $sql_check_shop = "SELECT * FROM shops WHERE aid = $aid";
    $res_check = $conn->query($sql_check_shop);

    if ($res_check->num_rows == 0) {
        // Chưa có shop -> Đá sang trang tạo shop
        header("Location: create_shop.php");
        exit();
    } else {
        // Đã có shop -> Lưu thông tin shop vào session để dùng tiện hơn
        $shop_data = $res_check->fetch_assoc();
        $_SESSION['shop_id'] = $shop_data['sid'];
        $_SESSION['shop_name'] = $shop_data['shop_name'];
    }
}
?>