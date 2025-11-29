<?php
session_start(); // 1. Khởi động session

// 2. KẾT NỐI CONFIG (Để lấy đường dẫn chuẩn BASE_URL)
require_once __DIR__ . '/../../../config.php';

// 3. XÓA SẠCH SESSION
session_unset(); // Xóa biến
session_destroy(); // Hủy phiên

// 4. HỦY COOKIE (Để đảm bảo sạch sẽ hoàn toàn)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. CHUYỂN HƯỚNG VỀ TRANG CHỦ (HOMEPAGE)
// Thay vì về login.php, ta cho về homepage.php
header("Location: " . BASE_URL . "/page/HomePage/homepage.php");
exit;
?>