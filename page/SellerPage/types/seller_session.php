<?php
// page/SellerPage/types/seller_session.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- SỬA LỖI Ở ĐÂY (QUAN TRỌNG) ---
// Dùng __DIR__ để đi từ thư mục 'types' ra 3 cấp để về gốc, sau đó vào thư mục 'db'
// Cách này chạy đúng bất kể bạn đặt tên thư mục dự án là 'LaiRaiShop' hay 'CK_MNM'
$db_path = __DIR__ . '/../../../db/db.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    // Dự phòng trường hợp file bị di chuyển
    die("Lỗi: Không tìm thấy file kết nối database tại: " . $db_path);
}
// -----------------------------------


// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['aid'])) {
    // Nếu chưa đăng nhập, đá về trang Login
    // Lưu ý: Nếu web chạy trên máy bạn bị lỗi 404 khi chuyển trang, hãy thêm '/CK_MNM' vào trước '/LaiRaiShop'
    header("Location: /LaiRaiShop/page/HomePage/LoginPage/login.php");
    exit();
}

// Lấy ID từ session
$aid = $_SESSION['aid'];

// 2. Kiểm tra xem user đã có Shop chưa
$current_page = basename($_SERVER['PHP_SELF']);

// Chỉ kiểm tra nếu KHÔNG PHẢI là trang tạo shop (để tránh vòng lặp vô tận)
if ($current_page != 'create_shop.php') {
    // Kiểm tra biến $conn có tồn tại không trước khi dùng
    if (isset($conn)) {
        $sql_check_shop = "SELECT * FROM shops WHERE aid = $aid"; 
        $res_check = $conn->query($sql_check_shop);
    
        if ($res_check->num_rows == 0) {
            // Chưa có shop -> Đá sang trang tạo shop
            header("Location: /LaiRaiShop/page/SellerPage/CreateSellerPage/create_shop.php");
            exit();
        } else {
            // Đã có shop -> Lưu thông tin shop vào session
            $shop_data = $res_check->fetch_assoc();
            $_SESSION['shop_id'] = $shop_data['sid']; 
            $_SESSION['shop_name'] = $shop_data['shop_name'];
        }
    }
}
?>