<?php
session_start(); // Khởi động session để biết đang hủy session nào

// Xóa tất cả các biến trong session
session_unset();

// Hủy toàn bộ session
session_destroy();

// Chuyển hướng về Trang chủ (Dùng đường dẫn tuyệt đối từ gốc dự án)
header("Location: /LaiRaiShop/page/HomePage/homepage.php");
exit;
?>