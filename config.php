<?php
// FILE: config.php

// 1. TỰ ĐỘNG LẤY ĐƯỜNG DẪN HỆ THỐNG (ROOT_PATH)
// __DIR__ luôn trả về đúng thư mục chứa file config.php này
define('ROOT_PATH', str_replace('\\', '/', __DIR__));

// 2. TỰ ĐỘNG TÍNH TOÁN BASE_URL (URL TRÊN TRÌNH DUYỆT)
// Logic: So sánh đường dẫn file hiện tại với Document Root của server để tìm ra thư mục con
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Lấy đường dẫn gốc của server (VD: C:/xampp/htdocs)
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

// Tìm phần đường dẫn thư mục dự án (VD: /CK_MNM/LaiRaiShop)
// Bằng cách loại bỏ phần 'C:/xampp/htdocs' ra khỏi 'C:/xampp/htdocs/CK_MNM/LaiRaiShop'
$folder_path = str_replace($doc_root, '', ROOT_PATH);

// Định nghĩa BASE_URL chuẩn
define('BASE_URL', $protocol . $host . $folder_path);

// 3. KẾT NỐI CƠ SỞ DỮ LIỆU
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lairaidata";

// Bật báo lỗi chi tiết cho MySQLi để dễ debug
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Hiển thị lỗi đẹp hơn thay vì chết trang
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// 4. KHỞI ĐỘNG SESSION TỰ ĐỘNG (QUAN TRỌNG)
// Giúp bạn không cần gọi session_start() ở từng file con nếu đã include config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. HÀM HỖ TRỢ XỬ LÝ ẢNH THÔNG MINH (Khuyên dùng)
// Giúp tự động sửa lỗi ảnh bị dư dấu / hoặc thiếu BASE_URL
if (!function_exists('getImage')) {
    function getImage($path) {
        if (empty($path)) return BASE_URL . '/images/icon.png'; // Ảnh mặc định nếu rỗng
        
        // Nếu là ảnh online (http...), giữ nguyên
        if (strpos($path, 'http') === 0) return $path;
        
        // Xóa dấu / ở đầu để tránh bị lỗi 2 dấu //
        $clean_path = ltrim($path, '/');
        
        // Ghép với BASE_URL chuẩn
        return BASE_URL . '/' . $clean_path;
    }
}
?>