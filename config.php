<?php
// File: LaiRaiShop/config.php

// -------------------------------------------------------------
// PHẦN 1: CẤU HÌNH ĐƯỜNG DẪN HỆ THỐNG (SYSTEM PATH)
// Dùng cho PHP để gọi các file khác (include, require)
// -------------------------------------------------------------

// Lấy đường dẫn thư mục hiện tại và chuẩn hóa dấu gạch chéo (để chạy tốt trên cả Windows/Linux)
define('ROOT_PATH', str_replace('\\', '/', __DIR__));


// -------------------------------------------------------------
// PHẦN 2: CẤU HÌNH ĐƯỜNG DẪN URL TRÌNH DUYỆT (WEB URL)
// Dùng cho HTML (thẻ a, img, link css, js)
// Tự động tính toán dựa trên thư mục hiện tại so với thư mục gốc của server
// -------------------------------------------------------------

// 1. Kiểm tra giao thức (http hay https)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";

// 2. Lấy tên miền (localhost hoặc tenmien.com)
$host = $_SERVER['HTTP_HOST'];

// 3. Tính toán đường dẫn thư mục từ gốc server (htdocs)
// Logic: Lấy đường dẫn file config này trừ đi đường dẫn gốc của server
$root_sys = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']); // C:/xampp/htdocs
$current_dir = ROOT_PATH;                                     // C:/xampp/htdocs/CK_MNM/LaiRaiShop

// Kết quả phép trừ sẽ ra: /CK_MNM/LaiRaiShop (hoặc bất kỳ tên gì bạn đổi sau này)
$folder_path = str_replace($root_sys, '', $current_dir);

// 4. Tạo hằng số BASE_URL hoàn chỉnh
// Ví dụ kết quả: http://localhost/CK_MNM/LaiRaiShop
define('BASE_URL', $protocol . $host . $folder_path);

?>