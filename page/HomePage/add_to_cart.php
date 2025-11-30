<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once ROOT_PATH . '/db/db.php';



// Đặt header JSON để trả về dữ liệu cho AJAX
header('Content-Type: application/json; charset=utf-8');



// 1. KIỂM TRA PHƯƠNG THỨC GỬI
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'msg' => 'Lỗi: Phải dùng phương thức POST.']);
    exit;
}

// 2. NHẬN DỮ LIỆU TỪ AJAX
$pid = isset($_POST['pid']) ? (int)$_POST['pid'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// 3. KIỂM TRA ID SẢN PHẨM
if ($pid <= 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Lỗi: Không tìm thấy ID sản phẩm.']);
    exit;
}

// 4. TRUY VẤN THÔNG TIN SẢN PHẨM TỪ DB
$sql = "SELECT * FROM products WHERE pid = $pid";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Cấu trúc sản phẩm lưu trong session
    $item = [
        'pid' => $product['pid'],
        'name' => $product['name'],
        'image' => $product['main_image'], // Đảm bảo cột này đúng tên trong DB của bạn
        'price' => $product['price'],
        'qty' => $quantity
    ];

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Nếu sản phẩm đã có -> cộng dồn số lượng
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['qty'] += $quantity;
    } else {
        // Nếu chưa có -> thêm mới
        $_SESSION['cart'][$pid] = $item;
    }
    
    // Tính tổng số lượng item đang có trong giỏ
    $total_items = 0;
    foreach($_SESSION['cart'] as $cart_item){
        $total_items += $cart_item['qty'];
    }

    // Trả về kết quả thành công
    echo json_encode([
        'status' => 'success',
        'total_items' => $total_items,
        'data' => $item,
        'msg' => 'Đã thêm vào giỏ hàng'
    ]);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Lỗi: Sản phẩm không tồn tại trong CSDL.']);
}
?>