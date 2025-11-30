<?php
// FILE: config.php

// --- PHẦN 1: CẤU HÌNH ĐƯỜNG DẪN HỆ THỐNG ---
define('ROOT_PATH', str_replace('\\', '/', __DIR__));

// --- PHẦN 2: CẤU HÌNH URL TRÌNH DUYỆT (TỰ ĐỘNG) ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$root_sys = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$current_dir = ROOT_PATH;
$folder_path = str_replace($root_sys, '', $current_dir);
define('BASE_URL', $protocol . $host . $folder_path);

// --- PHẦN 3: KẾT NỐI CƠ SỞ DỮ LIỆU (MỚI THÊM) ---
// Thêm vào đây để các trang Admin chỉ cần gọi config.php là có luôn kết nối
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lairaidata";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Lỗi kết nối DB: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
